<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pregnancy;
use App\Models\Checkup;
use App\Models\HealthRecord;
use App\Models\BhwMonthlyReport;
use App\Models\BhwAssignment;
use App\Models\Purok;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class BhwPresidentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isBhwPresident()) {
                abort(403, 'Access denied. BHW President only.');
            }
            return $next($request);
        });
    }

    // Dashboard
    public function dashboard()
    {
        $barangay = auth()->user()->barangay;
        // Live counts on every load (previously cached for 5 minutes, which
        // left dashboard cards stale right after new records appeared).
        // Jurisdiction matching uses the shared normalize-and-overlap rule so
        // legacy spellings ('Burgos' vs 'Barangay Burgos Padlan, San Carlos
        // City, Pangasinan') resolve to the same area. A strict equality
        // check matched nothing and left every card at 0.
        $area = fn ($query, $column = 'barangay') => \App\Services\BhwPresidentAssignmentService::applyJurisdictionFilter($query, $barangay, $column);
        $stats = [
                'totalPatients' => tap(User::where('role', 'user'), $area)->count(),
                'totalBhws' => tap(User::where('role', 'bhw'), $area)->count(),
                'activePregnancies' => Pregnancy::active()->whereHas('woman', function ($q) use ($area) { $area($q); })->count(),
                'highRiskPregnancies' => Pregnancy::active()->highRisk()->whereHas('woman', function ($q) use ($area) { $area($q); })->count(),
                'scheduledCheckups' => Checkup::scheduled()->whereHas('woman', function ($q) use ($area) { $area($q); })->count(),
                'missedCheckups' => Checkup::missed()->whereHas('woman', function ($q) use ($area) { $area($q); })->count(),
                'completedCheckups' => Checkup::where('status', 'Completed')->whereHas('woman', function ($q) use ($area) { $area($q); })->count(),
                'totalHealthRecords' => HealthRecord::whereHas('woman', function ($q) use ($area) { $area($q); })->count(),
                'monthlyReports' => BhwMonthlyReport::whereHas('bhw', function ($q) use ($area) { $area($q); })->count(),
        ];

        // Recent activity
        $recentCheckups = Checkup::with(['woman', 'scheduledByBhw'])
            ->whereHas('woman', function ($q) use ($area) { $area($q); })
            ->latest('scheduled_date')
            ->limit(10)
            ->get();

        $recentHealthRecords = HealthRecord::with(['woman', 'recordedBy'])
            ->whereHas('woman', function ($q) use ($area) { $area($q); })
            ->latest()
            ->limit(10)
            ->get();

        // High-risk pregnancies needing attention
        $highRiskPregnancies = Pregnancy::active()
            ->highRisk()
            ->whereHas('woman', function ($q) use ($area) { $area($q); })
            ->with(['woman', 'checkups' => function ($query) {
                $query->latest('scheduled_date');
            }])
            ->latest()
            ->limit(5)
            ->get();

        $highRiskPregnanciesCount = $highRiskPregnancies->count();

        return view('bhw-president.dashboard', array_merge($stats, compact(
            'recentCheckups',
            'recentHealthRecords',
            'highRiskPregnancies',
            'highRiskPregnanciesCount'
        )));
    }

    // BHW Management
    public function bhws()
    {
        $search = request('search');
        $status = request('status');

        $query = User::where('role', 'bhw')->with(['purok', 'activeBhwAssignment.purok']);
        \App\Services\BhwPresidentAssignmentService::applyJurisdictionFilter($query, auth()->user()->barangay);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('middle_initial', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('barangay', 'like', '%' . $search . '%');
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $bhws = $query->latest()->paginate(15);

        // Get performance stats for each BHW
        foreach ($bhws as $bhw) {
            $bhw->health_records_count = HealthRecord::where('recorded_by_id', $bhw->id)->count();
            $bhw->checkups_scheduled = Checkup::where('scheduled_by_id', $bhw->id)->count();
            $bhw->monthly_reports_count = BhwMonthlyReport::where('bhw_id', $bhw->id)->count();
        }

        return view('bhw-president.bhws', compact('bhws'));
    }

    // Create BHW Form
    public function createBhw()
    {
        return view('bhw-president.create-bhw');
    }

    // Store BHW
    public function storeBhw(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:2',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'purok' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'phone' => 'required|string|max:20',
            'certification_number' => 'required|string|max:255',
            'certification_date' => 'required|date',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $validated;
        unset($data['purok']);
        $data['password'] = Hash::make($validated['password']);
        $data['role'] = 'bhw';
        $data['status'] = 'approved';
        $data['contact_number'] = $validated['phone'];
        $data['phone'] = $validated['phone'];
        $data['address'] = $request->address ?: implode(', ', array_filter([
            $request->filled('purok') ? $request->purok : null,
            auth()->user()->barangay,
            'San Carlos City, Pangasinan',
        ]));

        // New BHWs belong to the president's own barangay; typed Sitio / Street / Purok
        // entries are added to the registry automatically.
        $data['barangay'] = auth()->user()->barangay;
        $data['purok_id'] = \App\Models\Purok::resolveIdFromText($request->purok, auth()->user()->barangay);

        if ($request->hasFile('profile_image')) {
            $imageName = time() . '.' . $request->profile_image->extension();
            $request->profile_image->move(public_path('images/uploads/profile'), $imageName);
            $data['profile_image'] = 'uploads/profile/' . $imageName;
        }

        $bhw = User::create($data);
        \App\Models\ActivityLog::log('create', "BHW President created BHW {$bhw->name} in ".auth()->user()->barangay);

        Cache::forget('bhw_president_dashboard_stats_'.auth()->id());

        return redirect()->route('bhw-president.bhws.index')
            ->with('success', 'BHW created successfully.');
    }

    // BHW Details
    public function bhwDetails($id)
    {
        $bhw = User::where('role', 'bhw')->with(['purok', 'activeBhwAssignment.purok'])->findOrFail($id);
        $puroks = Purok::orderBy('barangay')->orderBy('name')->get();

        $healthRecords = HealthRecord::where('recorded_by_id', $bhw->id)
            ->with('woman')
            ->latest()
            ->paginate(20);

        $checkups = Checkup::where('scheduled_by_id', $bhw->id)
            ->with('woman')
            ->latest()
            ->paginate(20);

        $monthlyReports = BhwMonthlyReport::where('bhw_id', $bhw->id)
            ->latest()
            ->paginate(10);

        return view('bhw-president.bhw-details', compact(
            'bhw',
            'puroks',
            'healthRecords',
            'checkups',
            'monthlyReports'
        ));
    }

    public function healthRecords()
    {
        $president = auth()->user();

        $healthRecords = HealthRecord::with(['woman', 'walkInPatient', 'recordedBy'])
            ->whereNotNull('recorded_by_id')
            ->where(function ($query) use ($president) {
                $query->whereHas('woman', function ($q) use ($president) {
                    \App\Services\BhwPresidentAssignmentService::applyJurisdictionFilter($q, $president->barangay);
                })->orWhereHas('walkInPatient', function ($q) use ($president) {
                    \App\Services\BhwPresidentAssignmentService::applyJurisdictionFilter($q, $president->barangay);
                });
            })
            ->latest()
            ->paginate(15);

        return view('bhw-president.health-records.index', compact('healthRecords'));
    }

    public function passHealthRecordToMidwife(Request $request, $id)
    {
        $healthRecord = HealthRecord::with('recordedBy')->findOrFail($id);

        $healthRecord->update([
            'bhw_president_id' => auth()->id(),
            'bhw_president_approved_at' => now(),
            'workflow_status' => 'submitted_to_midwife',
            'submitted_to_midwife_at' => now(),
            'workflow_notes' => $request->input('workflow_notes'),
        ]);

        $midwife = User::where('role', 'midwife')->where('status', 'approved')->first();
        if ($midwife) {
            \App\Models\Notification::createNotification(
                $midwife->id,
                'A health record for ' . $healthRecord->patient_name . ' was reviewed by the BHW President and is ready for midwife review.',
                'Health Record Ready for Review',
                'info',
                route('midwife.health-records.show', $healthRecord->id)
            );
        }
        \App\Models\ActivityLog::log('update', "BHW President passed health record #{$healthRecord->id} ({$healthRecord->patient_name}) to midwife");

        return redirect()->route('bhw-president.health-records.index')
            ->with('success', 'Health record passed to the midwife.');
    }

    public function assignPurok(Request $request, $id)
    {
        $bhw = User::where('role', 'bhw')->findOrFail($id);

        $validated = $request->validate([
            'purok_id' => 'required|exists:puroks,id',
        ]);

        $purok = Purok::findOrFail($validated['purok_id']);

        if (!empty($bhw->barangay) && !empty($purok->barangay) && strcasecmp($bhw->barangay, $purok->barangay) !== 0) {
            return back()->withErrors([
                'purok_id' => 'The selected purok does not belong to this BHW\'s barangay.',
            ])->withInput();
        }

        $bhw->update([
            'purok_id' => $purok->id,
        ]);

        BhwAssignment::where('bhw_id', $bhw->id)->update(['is_active' => false]);
        BhwAssignment::updateOrCreate(
            [
                'bhw_id' => $bhw->id,
                'purok_id' => $purok->id,
            ],
            [
                'assigned_by_id' => auth()->id(),
                'assigned_at' => now(),
                'is_active' => true,
                'notes' => 'Synced from BHW details assignment.',
            ]
        );

        \App\Models\Notification::createNotification(
            $bhw->id,
            'Your assigned purok is now ' . $purok->name . '.',
            'Purok Assignment Updated',
            'info',
            route('bhw.dashboard')
        );
        \App\Models\ActivityLog::log('update', "BHW President assigned {$bhw->name} to {$purok->name}");

        return redirect()->route('bhw-president.bhws.details', $bhw->id)
            ->with('success', $bhw->name . ' is now assigned to ' . $purok->name . '.');
    }

    // Archive BHW (status flag + soft delete — retained for audit with a reason)
    public function archiveBhw(\Illuminate\Http\Request $request, $id)
    {
        $bhw = User::where('role', 'bhw')->findOrFail($id);
        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'BHW archived by BHW President via console';
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveUser($bhw, $reason, auth()->user());
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            return back()->withErrors(['handover' => $e->getMessage()])->withInput();
        }

        Cache::forget('bhw_president_dashboard_stats');

        return redirect()->route('bhw-president.bhws.index')
            ->with('success', 'BHW ' . $bhw->name . ' has been archived. Records are retained for audit.');
    }

    // Mark BHW as Inactive
    public function markInactive($id)
    {
        $bhw = User::where('role', 'bhw')->findOrFail($id);

        if ($block = app(\App\Services\WorkflowService::class)->guardOffboarding($bhw)) {
            return $block;
        }
        $bhw->update(['status' => 'inactive']);

        Cache::forget('bhw_president_dashboard_stats');

        return redirect()->route('bhw-president.bhws.index')
            ->with('success', 'BHW ' . $bhw->name . ' has been marked as inactive.');
    }

    // Activate BHW
    public function activateBhw($id)
    {
        $bhw = User::where('role', 'bhw')->findOrFail($id);
        $bhw->update(['status' => 'approved']);

        Cache::forget('bhw_president_dashboard_stats');

        return redirect()->route('bhw-president.bhws.index')
            ->with('success', 'BHW ' . $bhw->name . ' has been activated.');
    }

    // Archive BHW (soft-delete only — the account stays in archives for audit)
    public function deleteBhw(\Illuminate\Http\Request $request, $id)
    {
        $bhw = User::where('role', 'bhw')->findOrFail($id);

        if (($bhw->status ?? 'approved') === 'approved') {
            return redirect()->route('bhw-president.bhws.details', $bhw->id)
                ->withErrors(['delete' => 'Active BHW accounts cannot be archived directly. Mark the BHW inactive first.']);
        }

        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'BHW archived by BHW President via console';
        }

        // Delete profile image if exists
        if ($bhw->profile_image) {
            $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($publicDisk->exists($bhw->profile_image)) {
                $publicDisk->delete($bhw->profile_image);
            }
        }

        $bhw->update(['archived_reason' => $reason, 'archived_at' => now(), 'archived_by' => auth()->id()]);
        if (! $bhw->trashed()) {
            $bhw->delete();
        }
        \App\Models\ActivityLog::logProtected('archive', "BHW President archived BHW {$bhw->name}. Reason: {$reason}", $bhw);

        Cache::forget('bhw_president_dashboard_stats');

        return redirect()->route('bhw-president.bhws.index')
            ->with('success', 'BHW ' . $bhw->name . ' has been archived. Records are retained for audit.');
    }

    // Analytics Dashboard
    public function analytics()
    {
        // Maternal health indicators
        $maternalStats = [
            'totalPregnancies' => Pregnancy::count(),
            'activePregnancies' => Pregnancy::active()->count(),
            'completedPregnancies' => Pregnancy::completed()->count(),
            'highRiskCount' => Pregnancy::active()->highRisk()->count(),
            'highRiskPercentage' => Pregnancy::active()->count() > 0 
                ? round((Pregnancy::active()->highRisk()->count() / Pregnancy::active()->count()) * 100, 2)
                : 0,
        ];

        // Service utilization
        $serviceStats = [
            'checkupsCompleted' => Checkup::where('status', 'Completed')->count(),
            'checkupsMissed' => Checkup::missed()->count(),
            'healthRecordsTotal' => HealthRecord::count(),
            'averageCheckupsPerPatient' => User::where('role', 'user')->count() > 0
                ? round(Checkup::where('status', 'Completed')->count() / User::where('role', 'user')->count(), 2)
                : 0,
        ];

        // Monthly trends (last 6 months)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyTrends[] = [
                'month' => $date->format('M Y'),
                'pregnancies' => Pregnancy::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'checkups' => Checkup::whereMonth('scheduled_date', $date->month)
                    ->whereYear('scheduled_date', $date->year)
                    ->count(),
                'healthRecords' => HealthRecord::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
            ];
        }

        // Risk distribution across active pregnancies (for the risk chart).
        // Keys are matched case-insensitively for the same reason as above.
        $riskCounts = Pregnancy::active()
            ->selectRaw('LOWER(COALESCE(risk_level, ?)) as level, COUNT(*) as total', ['Low'])
            ->groupBy('level')
            ->pluck('total', 'level')
            ->all();
        $riskDistribution = [
            'labels' => ['Low', 'Medium', 'High', 'Critical'],
            'values' => [
                (int) ($riskCounts['low'] ?? 0),
                (int) ($riskCounts['medium'] ?? 0),
                (int) ($riskCounts['high'] ?? 0),
                (int) ($riskCounts['critical'] ?? 0),
            ],
        ];

        // Checkup outcomes (for the outcomes chart).
        // Keys are matched case-insensitively: stored values vary ('scheduled' vs 'Scheduled').
        $statusCounts = Checkup::selectRaw('LOWER(status) as status_key, COUNT(*) as total')
            ->groupBy('status_key')
            ->pluck('total', 'status_key')
            ->all();
        $checkupOutcomes = [
            'labels' => ['Scheduled', 'Completed', 'Missed', 'Cancelled'],
            'values' => [
                (int) ($statusCounts['scheduled'] ?? 0),
                (int) ($statusCounts['completed'] ?? 0),
                (int) ($statusCounts['missed'] ?? 0),
                (int) ($statusCounts['cancelled'] ?? 0),
            ],
        ];

        // BHW workload: records filed per BHW, top 8 (for the workload chart)
        $workload = HealthRecord::selectRaw('recorded_by_id, COUNT(*) as total')
            ->whereNotNull('recorded_by_id')
            ->whereHas('recordedBy', fn ($q) => $q->where('role', 'bhw'))
            ->groupBy('recorded_by_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('recordedBy:id,first_name,last_name')
            ->get();
        $bhwWorkload = [
            'labels' => $workload->map(fn ($row) => trim(($row->recordedBy?->first_name ?? '') . ' ' . mb_substr((string) $row->recordedBy?->last_name, 0, 1)) ?: ('BHW #' . $row->recorded_by_id))->all(),
            'values' => $workload->map(fn ($row) => (int) $row->total)->all(),
        ];

        return view('bhw-president.analytics', compact(
            'maternalStats',
            'serviceStats',
            'monthlyTrends',
            'riskDistribution',
            'checkupOutcomes',
            'bhwWorkload'
        ));
    }

    // Coverage Report
    public function coverage()
    {
        $purokId = request('purok');
        $month = request('month', Carbon::now()->month);
        $year = request('year', Carbon::now()->year);

        $query = User::where('role', 'user')->with(['purok', 'checkups', 'healthRecords', 'pregnancies']);

        if ($purokId) {
            $query->where('purok_id', $purokId);
        }

        $registeredPatients = $query->get()->map(function ($patient) {
            $patient->coverage_type = 'registered';
            return $patient;
        });

        $walkInQuery = \App\Models\WalkInPatient::with(['purok', 'checkupReferrals', 'pregnancies'])
            ->whereNull('converted_to_user_id');

        if ($purokId) {
            $walkInQuery->where('purok_id', $purokId);
        }

        $walkInPatients = $walkInQuery->get()->map(function ($patient) {
            $patient->coverage_type = 'walk_in';
            return $patient;
        });

        $combinedPatients = $registeredPatients->concat($walkInPatients)->sortBy(function ($patient) {
            return $patient->coverage_type === 'walk_in'
                ? $patient->full_name
                : $patient->name;
        })->values();

        $page = request('page', 1);
        $perPage = 20;
        $patients = new \Illuminate\Pagination\LengthAwarePaginator(
            $combinedPatients->forPage($page, $perPage),
            $combinedPatients->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Get unique puroks for filter
        $purokIds = User::where('role', 'user')->select('purok_id')->distinct()->pluck('purok_id')
            ->merge(\App\Models\WalkInPatient::select('purok_id')->distinct()->pluck('purok_id'))
            ->filter()
            ->unique();
        $puroks = \App\Models\Purok::whereIn('id', $purokIds)
            ->orderBy('name')
            ->get();

        // Coverage statistics
        $patientCollection = $combinedPatients;
        $coverageStats = [
            'totalPatients' => $combinedPatients->count(),
            'walkInPatients' => $walkInPatients->count(),
            'patientsWithCheckups' => $patientCollection->filter(function($patient) {
                if (($patient->coverage_type ?? 'registered') === 'walk_in') {
                    return $patient->checkupReferrals && $patient->checkupReferrals->count() > 0;
                }
                return $patient->checkups && $patient->checkups->count() > 0;
            })->count(),
            'patientsWithHealthRecords' => $patientCollection->filter(function($patient) {
                if (($patient->coverage_type ?? 'registered') === 'walk_in') {
                    return \App\Models\HealthRecord::where('walk_in_patient_id', $patient->id)->exists();
                }
                return $patient->healthRecords && $patient->healthRecords->count() > 0;
            })->count(),
            'patientsWithPregnancies' => $patientCollection->filter(function($patient) {
                return $patient->pregnancies && $patient->pregnancies->count() > 0;
            })->count(),
            'coveragePercentage' => $combinedPatients->count() > 0
                ? round(($patientCollection->filter(function($patient) {
                    if (($patient->coverage_type ?? 'registered') === 'walk_in') {
                        return \App\Models\HealthRecord::where('walk_in_patient_id', $patient->id)->exists();
                    }
                    return $patient->healthRecords && $patient->healthRecords->count() > 0;
                })->count() / $combinedPatients->count()) * 100, 2)
                : 0,
        ];

        return view('bhw-president.coverage', compact(
            'patients',
            'puroks',
            'coverageStats',
            'purokId',
            'month',
            'year'
        ));
    }

    // High-Risk Report
    public function highRisk()
    {
        $highRiskPregnancies = Pregnancy::active()
            ->highRisk()
            ->with(['woman', 'checkups' => function($q) {
                $q->latest()->limit(5);
            }])
            ->latest()
            ->paginate(20);

        return view('bhw-president.high-risk', compact('highRiskPregnancies'));
    }

    // Pregnancies Review Queue (missing method referenced by bhw-president.pregnancies.index)
    public function pregnancies(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter', 'pending');

        $query = Pregnancy::active()
            ->with(['woman', 'walkInPatient', 'healthRecords']);

        if ($filter === 'pending') {
            $query->where('workflow_status', 'submitted_to_bhw_president');
        } elseif ($filter === 'high-risk') {
            $query->highRisk();
        }

        if ($search) {
            $query->where(function ($outer) use ($search) {
                $outer->whereHas('woman', fn ($q) => $q->where(function ($w) use ($search) {
                    $w->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                }))->orWhereHas('walkInPatient', fn ($q) => $q->where(function ($w) use ($search) {
                    $w->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                }));
            });
        }

        $pregnancies = $query->latest()->paginate(20)->withQueryString();
        $pendingCount = Pregnancy::active()->where('workflow_status', 'submitted_to_bhw_president')->count();

        return view('bhw-president.pregnancies.index', compact('pregnancies', 'search', 'filter', 'pendingCount'));
    }

    // Reports Index
    public function reports()
    {
        $filter = request('filter', 'all');
        
        $reports = BhwMonthlyReport::with('bhw')
            ->when($filter !== 'all', function ($query) use ($filter) {
                $query->where('report_type', $filter);
            })
            ->latest()
            ->paginate(20);

        return view('bhw-president.reports', compact('reports', 'filter'));
    }

    // Report Details
    public function reportShow($id)
    {
        $report = BhwMonthlyReport::with('bhw')->findOrFail($id);

        if ($report->report_type === 'health_records') {
            $healthRecords = HealthRecord::with(['woman', 'recordedBy'])
                ->where('recorded_by_id', $report->bhw_id)
                ->whereMonth('created_at', $report->report_month)
                ->whereYear('created_at', $report->report_year)
                ->when(($report->filters['patient_filter'] ?? 'all') === 'selected', function ($query) use ($report) {
                    $query->whereIn('user_id', $report->filters['user_ids'] ?? []);
                })
                ->latest()
                ->get();

            $uniquePatients = $healthRecords->pluck('user_id')->unique()->count();

            $riskDistribution = [
                'low' => $healthRecords->where('risk_level', 'Low')->count(),
                'medium' => $healthRecords->where('risk_level', 'Medium')->count(),
                'high' => $healthRecords->where('risk_level', 'High')->count(),
            ];

            return view('bhw-president.report-show', compact(
                'report',
                'healthRecords',
                'uniquePatients',
                'riskDistribution'
            ));
        } else {
            // Pregnancy reports
            $pregnancies = Pregnancy::whereNull('ended_at')
                ->whereMonth('created_at', $report->report_month)
                ->whereYear('created_at', $report->report_year)
                ->with('woman')
                ->when(($report->filters['patient_filter'] ?? 'all') === 'selected', function ($query) use ($report) {
                    $query->whereIn('user_id', $report->filters['user_ids'] ?? []);
                })
                ->latest()
                ->get();

            $uniquePatients = $pregnancies->pluck('user_id')->unique()->count();
            $riskDistribution = [
                'low' => $pregnancies->where('is_high_risk', false)->count(),
                'high' => $pregnancies->where('is_high_risk', true)->count(),
                'medium' => 0,
            ];

            return view('bhw-president.report-show-pregnancies', compact(
                'report',
                'pregnancies',
                'uniquePatients',
                'riskDistribution'
            ));
        }
    }

    // Delete Report
    public function reportDelete($id)
    {
        $report = BhwMonthlyReport::findOrFail($id);
        $report->delete();

        return redirect()->route('bhw-president.reports.index')
            ->with('success', 'Report archived successfully.');
    }

    // Approve Report and Submit to Midwife
    public function reportApprove(Request $request, $id)
    {
        $report = BhwMonthlyReport::findOrFail($id);
        
        if ($report->submission_status !== 'submitted_to_president') {
            return redirect()->route('bhw-president.reports.index')
                ->with('error', 'This report cannot be approved at this stage.');
        }

        $report->update([
            'submission_status' => 'submitted_to_midwife',
            'approved_by_president' => auth()->id(),
            'approved_by_president_at' => now(),
            'president_notes' => $request->input('notes'),
            'submitted_to_midwife_by' => auth()->id(),
            'submitted_to_midwife_at' => now(),
        ]);

        // 5. Transparency: BHW is told their report advanced.
        app(\App\Services\WorkflowService::class)->notifyAction(
            (int) $report->bhw_id,
            '✅ Monthly Report Approved',
            'Your report "' . ($report->title ?? "#{$report->id}") . '" was approved and forwarded to the midwife.',
            'success',
            route('bhw.reports.index')
        );

        return redirect()->route('bhw-president.reports.index')
            ->with('success', 'Report approved and submitted to Midwife successfully.');
    }

    // Reject Report
    public function reportReject(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string',
        ]);

        $report = BhwMonthlyReport::findOrFail($id);
        
        if ($report->submission_status !== 'submitted_to_president') {
            return redirect()->route('bhw-president.reports.index')
                ->with('error', 'This report cannot be rejected at this stage.');
        }

        // 1. Rejection Feedback Loop: mandatory note → Needs Revision queue + notify BHW.
        app(\App\Services\WorkflowService::class)->sendBackForRevision(
            'bhw_report',
            $report,
            auth()->id(),
            $request->input('notes')
        );

        return redirect()->route('bhw-president.reports.index')
            ->with('success', 'Report sent back to the BHW Needs Revision queue with your note.');
    }

    // Settings — Barangay & team level
    public function settings()
    {
        $me = auth()->user()->fresh();
        $barangay = (string) ($me->barangay ?? '');

        $inJurisdiction = function ($value) use ($barangay) {
            $a = \App\Services\BhwPresidentAssignmentService::normalizeBarangay($barangay);
            $b = \App\Services\BhwPresidentAssignmentService::normalizeBarangay((string) $value);
            return $a !== '' && $b !== '' && ($a === $b || str_contains($a, $b) || str_contains($b, $a));
        };

        $teamBhws = User::where('role', 'bhw')->where('status', 'approved')->get()->filter(
            fn ($bhw) => $inJurisdiction($bhw->barangay)
        );

        $puroks = \App\Models\Purok::orderBy('name')->get()->filter(
            fn ($purok) => $inJurisdiction($purok->barangay)
        );
        $assignedPurokIds = \App\Models\BhwAssignment::where('is_active', true)->pluck('purok_id')->all();
        $unassignedPuroks = $puroks->reject(fn ($purok) => in_array($purok->id, $assignedPurokIds));

        $pendingReports = \App\Models\BhwMonthlyReport::where('submission_status', 'submitted_to_president')
            ->whereIn('bhw_id', $teamBhws->pluck('id')->all())
            ->count();

        $highRiskCount = Pregnancy::active()->highRisk()->count();

        $midwife = User::where('role', 'midwife')->where('status', 'approved')->first();

        $deadlineDay = (int) \App\Models\Setting::get("president.{$me->id}.report_deadline_day", \App\Models\Setting::get('reports.deadline_day', 25));

        $alertPrefs = [
            'unassigned_puroks' => \App\Models\Setting::get("president.{$me->id}.alert_unassigned_puroks", '1') === '1',
            'pending_reports' => \App\Models\Setting::get("president.{$me->id}.alert_pending_reports", '1') === '1',
            'high_risk' => \App\Models\Setting::get("president.{$me->id}.alert_high_risk", '1') === '1',
        ];

        return view('bhw-president.settings', compact(
            'barangay', 'teamBhws', 'puroks', 'unassignedPuroks',
            'pendingReports', 'highRiskCount', 'midwife', 'deadlineDay', 'alertPrefs'
        ));
    }

    public function updateSettings(Request $request)
    {
        $section = $request->input('section', 'profile');
        $me = auth()->user();

        // ── Personal profile (contact + photo for team coordination) ──
        if ($section === 'profile') {
            $data = $request->validate([
                'contact_number' => 'nullable|string|max:20',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            $me->contact_number = $data['contact_number'] ?? $me->contact_number;
            if ($request->hasFile('profile_image')) {
                $me->profile_image = $request->file('profile_image')->store('uploads/profile', 'public');
            }
            $me->save();

            return back()->with('success', 'Profile updated.');
        }

        // ── Password ──────────────────────────────────────────────────
        if ($section === 'password') {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
                'confirm_password' => 'required|string|same:new_password',
            ]);
            if (!\Illuminate\Support\Facades\Hash::check($request->input('current_password'), $me->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
            $me->password = \Illuminate\Support\Facades\Hash::make($request->input('new_password'));
            $me->save();

            return back()->with('success', 'Password updated successfully.');
        }

        // ── Two-factor flag ───────────────────────────────────────────
        if ($section === '2fa') {
            $me->pref_2fa_enabled = $request->boolean('pref_2fa_enabled');
            $me->save();

            return back()->with('success', $me->pref_2fa_enabled ? 'Two-factor authentication enabled.' : 'Two-factor authentication disabled.');
        }

        // ── Team reporting deadline (day of month for BHW submissions) ─
        if ($section === 'deadline') {
            $data = $request->validate(['deadline_day' => 'required|integer|min:1|max:28']);
            \App\Models\Setting::set("president.{$me->id}.report_deadline_day", $data['deadline_day'], $me->id);

            return back()->with('success', "Monthly BHW submission deadline set to day {$data['deadline_day']}.");
        }

        // ── Barangay alert preferences ────────────────────────────────
        if ($section === 'alerts') {
            \App\Models\Setting::set("president.{$me->id}.alert_unassigned_puroks", $request->boolean('alert_unassigned_puroks') ? '1' : '0', $me->id);
            \App\Models\Setting::set("president.{$me->id}.alert_pending_reports", $request->boolean('alert_pending_reports') ? '1' : '0', $me->id);
            \App\Models\Setting::set("president.{$me->id}.alert_high_risk", $request->boolean('alert_high_risk') ? '1' : '0', $me->id);

            return back()->with('success', 'Alert preferences saved.');
        }

        return back()->with('error', 'Unknown settings section.');
    }
}
