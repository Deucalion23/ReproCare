<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BhwMonthlyReport;
use App\Models\Checkup;
use App\Models\MaternalDeath;
use App\Models\MaternalMorbidity;
use App\Models\Pregnancy;
use App\Models\SupplyRequest;
use App\Models\User;
use App\Services\AIInsightService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChoController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::whereIn('role', ['rhu', 'midwife', 'bhw_president', 'bhw'])->count();
        $totalPatients = User::where('role', 'user')->where('status', 'approved')->count();
        $activePregnancies = Pregnancy::active()->count();
        $totalSupplyRequests = SupplyRequest::count();
        $pendingSupplyRequests = SupplyRequest::where('status', 'submitted')->count();
        $totalDeaths = MaternalDeath::count();
        $totalNearMiss = MaternalMorbidity::count();
        $ancCoverageRate = $this->ancCoverageRate();
        $recentRequests = SupplyRequest::with('requestedBy')->latest()->limit(5)->get();
        $recentDeaths = MaternalDeath::latest('death_date')->limit(5)->get();
        $me = auth()->user()->fresh();
        $signatureReady = $me && $me->signature_image && $me->license_number && $me->employee_id;
        $onboardingMissing = $me ? array_values(array_filter([
            !$me->employee_id ? 'Plantilla ID' : null,
            !$me->license_number ? 'Medical license' : null,
            !$me->signature_image ? 'Digital signature' : null,
            !$me->pref_2fa_enabled ? 'Two-factor authentication' : null,
        ])) : [];
        $dashboardRiskReport = app(\App\Services\MaternalAnalyticsService::class)->report([
            'from' => now()->subMonthsNoOverflow(11)->startOfMonth()->toDateString(),
            'to' => today()->toDateString(),
            'barangay' => null,
            'rhu' => null,
        ]);
        $dashboardMapData = app(\App\Services\AnalyticsMap::class)->build($dashboardRiskReport);

        return view('cho.dashboard', compact(
            'totalUsers',
            'totalPatients',
            'activePregnancies',
            'totalSupplyRequests',
            'pendingSupplyRequests',
            'totalDeaths',
            'totalNearMiss',
            'ancCoverageRate',
            'recentRequests',
            'recentDeaths',
            'signatureReady',
            'onboardingMissing',
            'dashboardMapData'
        ));
    }

    public function settings()
    {
        return view('cho.settings');
    }

    /**
     * Official city-wide documents require the sitting CHO's
     * signature asset, medical license, and plantilla ID on file.
     */
    public static function signatureReadyFor(?User $user): bool
    {
        return (bool) ($user && $user->signature_image && $user->license_number && $user->employee_id);
    }

    protected function redirectIfSignatureNotReady()
    {
        if (!self::signatureReadyFor(auth()->user()?->fresh())) {
            return redirect()->route('cho.settings')
                ->with('error', 'Official documents are locked until the sitting CHO completes My Profile: digital signature, medical license number, and plantilla ID.');
        }
        return null;
    }

    public function updateSettings(Request $request)
    {
        $section = $request->input('section', 'thresholds');

        if ($section === 'appearance') {
            $data = $request->validate([
                'mode' => 'required|in:light,dark',
            ]);
            // Keep the city-wide brand palette consistent. Only its default
            // light/dark mode remains an administrator preference.
            $data = array_merge(config('appearance.defaults'), $data);
            $data['revision'] = (string) \Illuminate\Support\Str::uuid();
            \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
                \App\Models\Setting::updateOrCreate(['key' => 'appearance.theme'], [
                    'value' => json_encode($data), 'group' => 'appearance',
                    'label' => 'City-wide ReproCare appearance', 'updated_by_id' => auth()->id(),
                ]);
                // updateOrCreate bypasses Setting::set(), so clear the read cache explicitly.
                \App\Models\Setting::flushCache('appearance.theme');
                ActivityLog::log('update', 'CHO updated the city-wide default appearance mode');
            });
            return redirect()->to(route('cho.settings').'#appearance')
                ->with('success', 'Appearance saved for all ReproCare users.');
        }

        if ($section === 'thresholds') {
            $data = $request->validate([
                'bp_systolic_high' => 'required|numeric|min:90|max:220',
                'bp_diastolic_high' => 'required|numeric|min:50|max:140',
                'hemoglobin_low' => 'required|numeric|min:5|max:15',
                'gestational_age_max' => 'required|integer|min:37|max:45',
            ]);
            foreach ($data as $field => $value) {
                \App\Models\Setting::set('threshold.' . $field, $value, auth()->id());
            }
            \App\Models\ActivityLog::log('update', 'CHO updated city-wide clinical risk thresholds');
            return back()->with('success', 'Clinical risk thresholds updated city-wide.');
        }

        if ($section === 'office') {
            $data = $request->validate([
                'office_name' => 'required|string|max:255',
                'contact_number' => 'nullable|string|max:30',
                'director_name' => 'nullable|string|max:255',
            ]);
            foreach ($data as $field => $value) {
                \App\Models\Setting::set('cho.' . $field, $value ?? '', auth()->id());
            }
            \App\Models\ActivityLog::log('update', 'CHO updated office profile');
            return back()->with('success', 'CHO office profile updated.');
        }

        if ($section === 'maintenance') {
            $request->validate(['maintenance_mode' => 'required|in:on,off']);
            try {
                if ($request->maintenance_mode === 'on') {
                    \Illuminate\Support\Facades\Artisan::call('down', ['--secret' => 'reprocare-admin']);
                } else {
                    \Illuminate\Support\Facades\Artisan::call('up');
                }
            } catch (\Throwable $e) {
                return back()->with('error', 'Could not toggle maintenance mode: ' . $e->getMessage());
            }
            \App\Models\ActivityLog::log('update', 'CHO toggled maintenance mode ' . $request->maintenance_mode);
            return back()->with('success', 'Maintenance mode ' . ($request->maintenance_mode === 'on' ? 'enabled.' : 'disabled.'));
        }

        if ($section === 'audit') {
            $data = $request->validate(['retention_days' => 'required|integer|min:30|max:3650']);
            \App\Models\Setting::set('audit.retention_days', $data['retention_days'], auth()->id());
            if ($request->boolean('prune_now')) {
                $cutoff = now()->subDays($data['retention_days']);
                $count = \App\Models\ActivityLog::where('created_at', '<', $cutoff)->where('is_protected', false)->delete();
                \App\Models\ActivityLog::log('delete', "CHO pruned {$count} activity log(s) per retention policy");
                return back()->with('success', "Retention saved. Pruned {$count} old log(s).");
            }
            \App\Models\ActivityLog::log('update', 'CHO updated audit log retention policy');
            return back()->with('success', 'Audit retention policy saved.');
        }

        if ($section === 'sms') {
            $data = $request->validate([
                'provider' => 'required|in:movider,textbee',
                'mock' => 'required|in:0,1',
                'textbee_api_key' => 'nullable|string|max:255',
                'textbee_device_id' => 'nullable|string|max:255',
            ]);
            foreach ($data as $field => $value) {
                \App\Models\Setting::set('sms.' . $field, $value ?? '', auth()->id());
            }
            \App\Models\ActivityLog::log('update', 'CHO updated SMS gateway configuration');
            return back()->with('success', 'SMS gateway configuration saved.');
        }

        if ($section === 'password') {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
                'confirm_password' => 'required|string|same:new_password',
            ]);
            $user = auth()->user();
            if (!\Illuminate\Support\Facades\Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
            $user->password = \Illuminate\Support\Facades\Hash::make($request->input('new_password'));
            $user->save();
            return back()->with('success', 'Password updated successfully.');
        }

        if ($section === 'myprofile') {
            $data = $request->validate([
                'first_name' => 'required|string|max:100',
                'middle_initial' => 'nullable|string|max:5',
                'last_name' => 'required|string|max:100',
                'official_title' => 'nullable|string|max:150',
                'employee_id' => 'nullable|string|max:60',
                'license_number' => 'nullable|string|max:60',
                'license_expiry' => 'nullable|date',
                'specialization' => 'nullable|string|max:150',
                'contact_number' => 'nullable|string|max:30',
                'secondary_email' => 'nullable|email|max:255',
                'office_extension' => 'nullable|string|max:20',
                'emergency_mobile' => 'nullable|string|max:30',
            ]);
            $user = auth()->user();
            $user->update($data);
            \App\Models\ActivityLog::log('update', 'CHO updated My Profile identity and contact details');
            return back()->with('success', 'My Profile updated.');
        }

        if ($section === 'signature') {
            $user = auth()->user();
            if ($request->boolean('remove_signature')) {
                if ($user->signature_image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($user->signature_image);
                }
                $user->update(['signature_image' => null]);
                \App\Models\ActivityLog::log('update', 'CHO removed official digital signature');
                return back()->with('success', 'Digital signature removed.');
            }
            $data = $request->validate([
                'signature_image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
            ]);
            $path = $request->file('signature_image')->store('signatures', 'public');
            if ($user->signature_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->signature_image);
            }
            $user->update(['signature_image' => $path]);
            \App\Models\ActivityLog::log('update', 'CHO uploaded official digital signature');
            return back()->with('success', 'Digital signature uploaded.');
        }

        if ($section === 'exec_notifications') {
            $user = auth()->user();
            $user->update([
                'pref_mortality_alerts' => $request->boolean('pref_mortality_alerts'),
                'pref_audit_warnings' => $request->boolean('pref_audit_warnings'),
                'pref_compliance_updates' => $request->boolean('pref_compliance_updates'),
                'pref_escalation_alerts' => $request->boolean('pref_escalation_alerts'),
            ]);
            \App\Models\ActivityLog::log('update', 'CHO updated executive notification preferences');
            return back()->with('success', 'Executive notification preferences saved.');
        }

        if ($section === 'twofa') {
            $user = auth()->user();
            $user->update(['pref_2fa_enabled' => $request->boolean('pref_2fa_enabled')]);
            \App\Models\ActivityLog::log('update', 'CHO ' . ($user->pref_2fa_enabled ? 'enabled' : 'disabled') . ' two-factor authentication');
            return back()->with('success', 'Two-factor authentication ' . ($user->pref_2fa_enabled ? 'enabled.' : 'disabled.'));
        }

        if ($section === 'sessions') {
            $request->validate(['current_password' => 'required|string']);
            $user = auth()->user();
            if (!\Illuminate\Support\Facades\Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
            \Illuminate\Support\Facades\Auth::logoutOtherDevices($request->input('current_password'));
            \App\Models\ActivityLog::log('update', 'CHO revoked all other active sessions');
            return back()->with('success', 'All other devices have been signed out.');
        }

        return back()->with('error', 'Unknown settings section.');
    }

    public function users(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role', 'all');

        $users = User::whereIn('role', ['rhu', 'midwife', 'bhw_president', 'bhw'])
            ->when($role !== 'all', fn ($query) => $query->where('role', $role))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_initial', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('barangay', 'like', "%{$search}%")
                        ->orWhere('rhu_assignment', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('cho.users.index', compact('users', 'search', 'role'));
    }

    public function createUser()
    {
        return view('cho.users.create');
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|in:rhu,midwife,bhw_president,bhw',
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:2',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'contact_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'rhu_assignment' => 'nullable|string|max:255',
            'cho_office' => 'nullable|string|max:255',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'approved';
        $data['registered_by_cho_id'] = auth()->id();

        $user = User::create($data);
        ActivityLog::log('create', "CHO registered {$user->name} as {$user->role}", $user);

        return redirect()->route('cho.users.index')->with('success', 'Staff account registered successfully.');
    }

    public function userDetails($id)
    {
        $user = User::whereIn('role', ['rhu', 'midwife', 'bhw_president', 'bhw'])->findOrFail($id);

        return view('cho.users.show', compact('user'));
    }

    public function approveUser($id)
    {
        $user = User::whereIn('role', ['rhu', 'midwife', 'bhw_president', 'bhw'])->findOrFail($id);
        $user->update(['status' => 'approved', 'rejection_reason' => null]);
        ActivityLog::log('approve', "CHO approved staff account for {$user->name}", $user);

        return back()->with('success', 'Staff account approved.');
    }

    public function rejectUser(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $user = User::whereIn('role', ['rhu', 'midwife', 'bhw_president', 'bhw'])->findOrFail($id);
        $name = $user->name;
        $user->update(['status' => 'archived', 'rejection_reason' => $request->input('reason')]);
        $user->delete();
        ActivityLog::log('archive', "CHO rejected and archived staff account for {$name}");

        return redirect()->route('cho.users.index')->with('success', 'Staff account rejected and archived.');
    }

    public function deactivateUser($id)
    {
        $user = User::whereIn('role', ['rhu', 'midwife', 'bhw_president', 'bhw'])->findOrFail($id);

        // Offboarding guard: suspension strands work the same as archiving.
        if ($block = app(\App\Services\WorkflowService::class)->guardOffboarding($user)) {
            return $block;
        }

        $user->update(['status' => 'suspended']);
        ActivityLog::log('update', "CHO suspended staff account for {$user->name}", $user);

        return back()->with('success', 'Staff account suspended.');
    }

    public function activateUser($id)
    {
        $user = User::whereIn('role', ['rhu', 'midwife', 'bhw_president', 'bhw'])->findOrFail($id);
        $user->update(['status' => 'approved']);
        ActivityLog::log('update', "CHO reactivated staff account for {$user->name}", $user);

        return back()->with('success', 'Staff account activated.');
    }

    public function supplyRequests(Request $request)
    {
        $requests = SupplyRequest::with(['requestedBy', 'approvedBy'])
            ->when($request->filled('status') && $request->status !== 'all', fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('urgency') && $request->urgency !== 'all', fn ($query) => $query->where('urgency', $request->urgency))
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('cho.supply-requests.index', compact('requests'));
    }

    public function showSupplyRequest($id)
    {
        $supplyRequest = SupplyRequest::with(['requestedBy', 'approvedBy'])->findOrFail($id);

        return view('cho.supply-requests.show', compact('supplyRequest'));
    }

    public function approveSupplyRequest(Request $request, $id)
    {
        $data = $request->validate([
            'expected_delivery_date' => 'nullable|date|after_or_equal:today',
            'cho_notes' => 'nullable|string|max:1000',
        ]);

        $supplyRequest = SupplyRequest::findOrFail($id);
        $supplyRequest->update([
            'status' => 'approved',
            'approved_by_id' => auth()->id(),
            'cho_notes' => $data['cho_notes'] ?? null,
            'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
            'reviewed_at' => now(),
            'approved_at' => now(),
        ]);
        ActivityLog::log('approve', "CHO approved supply request for {$supplyRequest->supply_name}", $supplyRequest);

        return redirect()->route('cho.supply-requests.index')->with('success', 'Supply request approved.');
    }

    public function rejectSupplyRequest(Request $request, $id)
    {
        $data = $request->validate(['cho_notes' => 'required|string|max:1000']);

        $supplyRequest = SupplyRequest::findOrFail($id);
        $supplyRequest->update([
            'status' => 'declined',
            'approved_by_id' => auth()->id(),
            'cho_notes' => $data['cho_notes'],
            'reviewed_at' => now(),
        ]);
        ActivityLog::log('reject', "CHO declined supply request for {$supplyRequest->supply_name}", $supplyRequest);

        return redirect()->route('cho.supply-requests.index')->with('success', 'Supply request declined.');
    }

    public function pregnancies(Request $request)
    {
        $status = $request->input('status', 'all');
        $riskLevel = $request->input('risk_level', 'all');
        $search = $request->input('search');

        $pregnancies = app(\App\Services\PatientPresentation::class)->originalPatientsFirst(Pregnancy::with(['woman', 'walkInPatient']))
            ->when($status === 'active', fn ($query) => $query->whereNull('ended_at'))
            ->when($status === 'completed', fn ($query) => $query->whereNotNull('ended_at'))
            ->when($riskLevel !== 'all' && $riskLevel, fn ($query) => $query->where('risk_level', $riskLevel))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($outer) use ($search) {
                    $outer->whereHas('woman', function ($inner) use ($search) {
                        $inner->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })->orWhereHas('walkInPatient', function ($inner) use ($search) {
                        $inner->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('cho.pregnancies.index', compact('pregnancies', 'status', 'riskLevel', 'search'));
    }

    public function pregnancyDetails($id)
    {
        $pregnancy = Pregnancy::with(['woman', 'walkInPatient', 'healthRecords'])->findOrFail($id);

        return view('cho.pregnancies.show', compact('pregnancy'));
    }

    public function immunizationRecords(Request $request)
    {
        $search = $request->input('search');
        $barangay = $request->input('barangay');

        $records = \App\Models\HealthRecord::whereNotNull('immunization_status')
            ->with(['woman', 'walkInPatient', 'recordedBy'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($outer) use ($search) {
                    $outer->whereHas('woman', function ($inner) use ($search) {
                        $inner->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })->orWhereHas('walkInPatient', function ($inner) use ($search) {
                        $inner->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                });
            })
            ->when($barangay, fn ($query) => $query->whereHas('woman', fn ($inner) => $inner->where('barangay', $barangay)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('cho.immunization.index', compact('records', 'search', 'barangay'));
    }

    public function reports(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $status = $request->input('status', 'all');
        [$year, $mon] = array_map('intval', explode('-', $month) + [date('Y'), date('m')]);

        $reports = BhwMonthlyReport::with('bhw')
            ->where('report_year', $year)
            ->where('report_month', $mon)
            ->when($status !== 'all' && $status, fn ($query) => $query->where('submission_status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('cho.reports.index', compact('reports', 'month', 'status'));
    }

    public function exportReportsCsv(Request $request)
    {
        $pendingSignature = !self::signatureReadyFor(auth()->user()?->fresh());
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $mon] = array_map('intval', explode('-', $month) + [date('Y'), date('m')]);
        $reports = BhwMonthlyReport::with('bhw')
            ->where('report_year', $year)
            ->where('report_month', $mon)
            ->get();

        $csv = "BHW,Period,Type,Records,Status,Signature\n";
        foreach ($reports as $report) {
            $csv .= "\"{$report->bhw?->name}\",\"{$report->report_period}\",\"{$report->report_type}\",\"{$report->total_records}\",\"{$report->submission_status}\",\"".($pendingSignature ? 'PENDING SIGNATURE' : 'SIGNED')."\"\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=reports-{$month}.csv");
    }

    public function exportReportsPdf(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $mon] = array_map('intval', explode('-', $month) + [date('Y'), date('m')]);
        $reports = BhwMonthlyReport::with('bhw')
            ->where('report_year', $year)
            ->where('report_month', $mon)
            ->get();
        $signatory = auth()->user()->fresh();
        $pendingSignature = !self::signatureReadyFor($signatory);

        return view('cho.reports.pdf', compact('reports', 'month', 'signatory', 'pendingSignature'));
    }

    public function staffIndex(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role', 'all');

        $staff = User::whereIn('role', ['rhu', 'midwife', 'bhw_president', 'bhw'])
            ->when($role !== 'all', fn ($query) => $query->where('role', $role))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('cho.staff.index', compact('staff', 'search', 'role'));
    }

    public function midwives(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'approved');

        $midwives = User::where('role', 'midwife')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('cho.staff.midwives', compact('midwives', 'search', 'status'));
    }

    public function bhws(Request $request)
    {
        $search = $request->input('search');
        $barangay = $request->input('barangay');
        $status = $request->input('status', 'approved');

        $bhws = User::where('role', 'bhw')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($barangay, fn ($query) => $query->where('barangay', $barangay))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('cho.staff.bhws', compact('bhws', 'search', 'barangay', 'status'));
    }

    public function maternalDeaths(Request $request)
    {
        $deaths = MaternalDeath::with(['user', 'walkInPatient', 'recordedBy', 'purok'])
            ->when($request->filled('audit_status') && $request->audit_status !== 'all', fn ($query) => $query->where('audit_status', $request->audit_status))
            ->latest('death_date')
            ->paginate(15)
            ->withQueryString();

        return view('cho.maternal-deaths.index', compact('deaths'));
    }

    public function showMaternalDeath($id)
    {
        $death = MaternalDeath::with(['user', 'walkInPatient', 'pregnancy', 'recordedBy', 'reviewedBy', 'purok'])->findOrFail($id);

        return view('cho.maternal-deaths.show', compact('death'));
    }

    public function auditMaternalDeath(Request $request, $id)
    {
        $pendingSignature = !self::signatureReadyFor(auth()->user()?->fresh());
        $data = $request->validate([
            'audit_status' => 'required|in:reviewed,closed',
            'audit_notes' => 'required|string|max:2000',
        ]);

        $death = MaternalDeath::findOrFail($id);
        $death->update([
            'audit_status' => $data['audit_status'],
            'audit_notes' => $data['audit_notes'].($pendingSignature ? ' [PENDING SIGNATURE]' : ''),
            'reviewed_by_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        ActivityLog::log('update', "CHO audited maternal death case for {$death->patient_name}".($pendingSignature ? ' (pending signature)' : ''), $death);

        return back()->with('success', $pendingSignature ? 'Audit saved with PENDING SIGNATURE watermark. Complete My Profile to sign.' : 'Maternal death audit updated.');
    }

    public function logs()
    {
        $logs = ActivityLog::with('user')->latest()->paginate(25);

        return view('cho.logs.index', compact('logs'));
    }

    public function analytics(\App\Http\Requests\AnalyticsRequest $request)
    {
        $analytics = app(\App\Services\MaternalAnalyticsService::class);
        $report = $analytics->report($request->validated());
        $suggestions = app(AIInsightService::class)->suggestions($report);
        $aiStatus = app(AIInsightService::class)->status();
        $areaOptions = $analytics->areaOptions();
        $queue = new \Illuminate\Pagination\LengthAwarePaginator(
            $report['queue']->forPage(max(1, $request->integer('page', 1)), 15)->values(),
            $report['queue']->count(),
            15,
            max(1, $request->integer('page', 1)),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('cho.analytics', compact('report', 'suggestions', 'areaOptions', 'queue', 'aiStatus'));
    }

    public function analyticsChat(\App\Http\Requests\AnalyticsRequest $request)
    {
        $report = app(\App\Services\MaternalAnalyticsService::class)->report($request->validated());

        return response()->json(app(AIInsightService::class)->chat($request->validated('question'), $report));
    }

    private function ancCoverageRate(): float
    {
        $completedPregnancies = Pregnancy::completed()->withCount('healthRecords')->get();
        $totalCompleted = $completedPregnancies->count();

        if ($totalCompleted === 0) {
            return 0;
        }

        $compliant = $completedPregnancies->filter(fn ($pregnancy) => $pregnancy->health_records_count >= 4)->count();

        return round(($compliant / $totalCompleted) * 100, 1);
    }

    private function facilityBirthRate(): float
    {
        $completed = Pregnancy::completed()->count();

        if ($completed === 0) {
            return 0;
        }

        $facilityDeliveries = Pregnancy::completed()
            ->whereNotNull('facility_delivery_place')
            ->where('facility_delivery_place', '!=', 'home')
            ->count();

        return round(($facilityDeliveries / $completed) * 100, 1);
    }
}
