<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pregnancy;
use App\Models\Checkup;
use App\Models\HealthRecord;
use App\Models\Bhw;
use App\Models\BhwProfile;
use App\Models\BhwMonthlyReport;
use App\Models\ActivityLog;
use App\Models\CheckupReferral;
use App\Models\WalkInPatient;
use App\Models\Purok;
use App\Services\RiskAnalysisService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MidwifeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Dashboard
    public function dashboard()
    {
        // Auto-run alert checks when midwife visits dashboard
        try {
            // Check for overdue checkups across all women
            Checkup::markOverdueCheckups();
            
            // Re-evaluate risk for women with missed checkups
            $missedWomen = Checkup::missed()->distinct('user_id')->pluck('user_id');
            $riskService = new RiskAnalysisService();
            foreach ($missedWomen as $womanId) {
                $riskService->evaluate($womanId);
            }
        } catch (\Exception $e) {
            \Log::error('Midwife dashboard alert check failed: ' . $e->getMessage());
        }
        
        // Cache dashboard statistics for 5 minutes (300 seconds) for faster loading
        $cacheKey = 'midwife_dashboard_stats_' . auth()->id();
        $stats = Cache::remember($cacheKey, 300, function () {
            return [
                'totalPatients' => User::where('role', 'user')->where('status', 'approved')->count(),
                'activePregnancies' => Pregnancy::active()->count(),
                'scheduledCheckups' => Checkup::scheduled()->count(),
                'missedCheckups' => Checkup::missed()->count(),
                'highRiskPatients' => Pregnancy::active()->highRisk()->count(),
            ];
        });

        // Only select needed columns for better performance
        $recentCheckups = Checkup::with(['woman:id,first_name,middle_initial,last_name', 'midwife:id,first_name,middle_initial,last_name'])
            ->select('id', 'user_id', 'midwife_id', 'scheduled_date', 'status', 'purpose')
            ->scheduled()
            ->upcoming()
            ->latest('scheduled_date')
            ->limit(5)
            ->get();

        return view('midwife.dashboard', array_merge($stats, compact('recentCheckups')));
    }

    // Patients Management
    public function patients()
    {
        $search = request('search');
        $filter = request('filter', 'all');
        $barangay = request('barangay');
        $purokId = request('purok_id');
        $pregnancyStatus = request('pregnancy_status', 'all');
        $ageRange = request('age_range', 'all');
        $riskLevel = request('risk_level', 'all');
        $trimester = request('trimester', 'all');

        // Get registered patients
        $registeredQuery = User::where('role', 'user')
            ->with([
                'purok',
                'pregnancies' => function($query) {
                    $query->active();
                },
                'healthRecords' => function($query) {
                    $query->latest();
                },
            ])
            ->where('status', 'approved');

        if ($search) {
            $cleanSearch = ltrim($search, '#');
            $registeredQuery->where(function($q) use ($search, $cleanSearch) {
                $q->where('id', $cleanSearch)
                  ->orWhere('first_name', 'like', '%' . $search . '%')
                  ->orWhere('middle_initial', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('contact_number', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%')
                  ->orWhere('barangay', 'like', '%' . $search . '%');
            });
        }

        if ($barangay) {
            $registeredQuery->where('barangay', $barangay);
        }

        if ($purokId) {
            $registeredQuery->where('purok_id', $purokId);
        }

        $registeredPatients = $registeredQuery->get()->map(function($patient) {
            $patient->type = 'registered';
            return $patient;
        });

        // Get unregistered patients
        $unregisteredQuery = WalkInPatient::with(['recordedBy', 'purok', 'convertedToUser', 'pregnancies' => function($query) {
            $query->active();
        }])
            ->whereNull('converted_to_user_id')->whereNull('user_id');

        if ($search) {
            $unregisteredQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('middle_initial', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('barangay', 'like', '%' . $search . '%')
                    ->orWhere('contact_number', 'like', '%' . $search . '%');
            });
        }

        if ($barangay) {
            $unregisteredQuery->where('barangay', $barangay);
        }

        if ($purokId) {
            $unregisteredQuery->where('purok_id', $purokId);
        }

        $unregisteredPatients = $unregisteredQuery->latest()->get()->map(function($patient) {
            $patient->type = 'unregistered';
            return $patient;
        });

        // Combine both collections
        $allPatients = app(\App\Services\PatientPresentation::class)->sortPatients($registeredPatients->concat($unregisteredPatients));

        // Apply filter (registered/unregistered)
        if ($filter === 'registered') {
            $allPatients = $allPatients->where('type', 'registered');
        } elseif ($filter === 'unregistered') {
            $allPatients = $allPatients->where('type', 'unregistered');
        }

        if ($pregnancyStatus === 'pregnant') {
            $allPatients = $allPatients->filter(fn ($patient) => $patient->pregnancies && $patient->pregnancies->count() > 0);
        } elseif ($pregnancyStatus === 'not_pregnant') {
            $allPatients = $allPatients->filter(fn ($patient) => !$patient->pregnancies || $patient->pregnancies->count() === 0);
        }

        // Age filter (teen = under 20)
        if ($ageRange !== 'all') {
            $allPatients = $allPatients->filter(function ($patient) use ($ageRange) {
                $age = $patient->age;
                return match ($ageRange) {
                    'teen', 'under_20' => $age !== null && $age < 20,
                    '20_34' => $age !== null && $age >= 20 && $age <= 34,
                    '35_plus' => $age !== null && $age >= 35,
                    default => true,
                };
            });
        }

        // Risk Level filter
        if ($riskLevel !== 'all') {
            $allPatients = $allPatients->filter(function ($patient) use ($riskLevel) {
                $activePreg = $patient->pregnancies?->first();
                $latestRecord = $patient->healthRecords?->first();
                $currentRisk = strtolower($activePreg?->risk_level ?? $latestRecord?->risk_level ?? 'low');

                if ($riskLevel === 'high_risk_only') {
                    return in_array($currentRisk, ['high', 'critical']);
                }
                return $currentRisk === strtolower($riskLevel);
            });
        }

        // Trimester filter
        if ($trimester !== 'all') {
            $allPatients = $allPatients->filter(function ($patient) use ($trimester) {
                $activePreg = $patient->pregnancies?->first();
                if (!$activePreg) return false;
                $aog = (int)($activePreg->aog_weeks ?? 0);
                return match ($trimester) {
                    '1' => $aog <= 13,
                    '2' => $aog >= 14 && $aog <= 26,
                    '3' => $aog >= 27,
                    default => true,
                };
            });
        }

        // Paginate manually
        $page = request('page', 1);
        $perPage = 10;
        $patients = new \Illuminate\Pagination\LengthAwarePaginator(
            $allPatients->forPage($page, $perPage),
            $allPatients->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Get statistics
        $scheduledCheckups = Checkup::where('status', 'Scheduled')->count();
        $missedCheckups = Checkup::where('status', 'Missed')->count();
        $registeredCount = User::where('role', 'user')->where('status', 'approved')->count();
        $unregisteredCount = WalkInPatient::whereNull('converted_to_user_id')->count();
        $puroks = Purok::orderBy('name')->get();
        $barangays = \App\Models\Barangay::allNames();

        return view('midwife.patients', compact(
            'patients',
            'scheduledCheckups',
            'missedCheckups',
            'registeredCount',
            'unregisteredCount',
            'puroks',
            'purokId',
            'barangays',
            'barangay',
            'pregnancyStatus',
            'ageRange',
            'riskLevel',
            'trimester'
        ));
    }

    public function decisionSupport(\Illuminate\Http\Request $request)
    {
        $filters = $request->validate(['search' => 'nullable|string|max:100', 'type' => 'nullable|in:registered,walk-in']);
        $type = $filters['type'] ?? 'registered';
        $search = trim($filters['search'] ?? '');
        $query = $type === 'walk-in' ? WalkInPatient::query() : User::where('role', 'user');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%'.$search.'%')->orWhere('last_name', 'like', '%'.$search.'%');
            });
        }
        $patients = $query->orderBy('last_name')->orderBy('first_name')->paginate(15)->withQueryString();
        return view('midwife.decision-support', compact('patients', 'search', 'type'));
    }

    public function patientDetails($id)
    {
        // Eager load all relationships in single query for better performance
        $woman = User::where('role', 'user')->with([
            'emergencyContacts',
            'primaryEmergencyContact',
            'purok',
            'pregnancies' => function($q) { $q->latest()->select('id', 'user_id', 'lmp', 'edd', 'is_high_risk'); },
            'checkups' => function($q) { $q->with('midwife:id,first_name,middle_initial,last_name')->latest()->select('id', 'user_id', 'midwife_id', 'scheduled_date', 'status', 'purpose'); },
            'healthRecords' => function($q) { $q->with('recordedBy:id,first_name,middle_initial,last_name')->latest()->select('id', 'user_id', 'recorded_by_id', 'bp', 'weight', 'created_at')->take(50); }
        ])->select('id', 'first_name', 'middle_initial', 'last_name', 'email', 'address', 'barangay', 'purok_id', 'status', 'date_of_birth', 'contact_number', 'partner_name', 'partner_contact', 'profile_image', 'gender', 'created_at')->findOrFail($id);

        // Patient-seen status for the latest risk alert (Seen/Unseen + read_at)
        // so the managing midwife can tell whether the patient opened it.
        $riskAlertStatus = \App\Services\SmartNotificationService::patientRiskAlertStatus($woman->id);

        return view('midwife.patient-details', [
            'woman' => $woman,
            'pregnancies' => $woman->pregnancies,
            'checkups' => $woman->checkups,
            'healthRecords' => $woman->healthRecords,
            'riskAlertStatus' => $riskAlertStatus,
            'decisionSupport' => app(\App\Services\MaternalAnalyticsService::class)->patientSupport($woman),
        ]);
    }

    // Profile - Redirect to unified profile system
    public function profile()
    {
        return redirect()->route('profile.show');
    }

    // Settings
    public function settings()
    {
        $user = auth()->user();

        // ── 4. Assigned location oversight (real coverage data) ──
        $approvedPatients = User::where('role', 'user')->where('status', 'approved');
        $totalPatients = (clone $approvedPatients)->count();
        $bhwCount = User::where('role', 'bhw')->where('status', '!=', 'archived')->count();
        $barangayStats = (clone $approvedPatients)
            ->select('barangay', DB::raw('COUNT(*) as total'))
            ->groupBy('barangay')
            ->orderByDesc('total')
            ->limit(8)
            ->get();
        $purokStats = DB::table('puroks')
            ->leftJoin('users', function ($join) {
                $join->on('users.purok_id', '=', 'puroks.id')
                    ->where('users.role', 'user')
                    ->where('users.status', 'approved');
            })
            ->select('puroks.id', 'puroks.name', 'puroks.barangay', DB::raw('COUNT(users.id) as patients'))
            ->groupBy('puroks.id', 'puroks.name', 'puroks.barangay')
            ->orderByDesc('patients')
            ->limit(10)
            ->get();

        // ── 2. Pending approval workload (real queue counts) ──
        $pendingPatients = User::where('role', 'user')->where('status', 'pending')->count();
        $pendingRecords = HealthRecord::where('workflow_status', 'submitted_to_midwife')->count();
        $pendingReports = BhwMonthlyReport::where('submission_status', 'submitted_to_midwife')->count();

        // ── 5. Audit trail (real activity + sessions) ──
        $lastApproval = ActivityLog::where('user_id', $user->id)
            ->where('action', 'approve')
            ->latest()
            ->first();
        $recentActivity = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->limit(8)
            ->get();
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get();
        $currentSessionId = session()->getId();

        // ── 1. License validity countdown ──
        $licenseDaysLeft = $user->license_expiry
            ? (int) now()->startOfDay()->diffInDays($user->license_expiry, false)
            : null;

        return view('midwife.settings', compact(
            'totalPatients',
            'bhwCount',
            'barangayStats',
            'purokStats',
            'pendingPatients',
            'pendingRecords',
            'pendingReports',
            'lastApproval',
            'recentActivity',
            'sessions',
            'currentSessionId',
            'licenseDaysLeft'
        ));
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();
        $section = $request->input('section', 'preferences');

        // ── 3a. Change password ──
        if ($section === 'password') {
            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if (!Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }

            $user->password = $request->input('password');
            $user->save();
            ActivityLog::log('update', 'Changed account password');

            return back()->with('success', 'Password updated successfully.');
        }

        // ── 3b/1. Profile + clinical fields (license locked once verified) ──
        if ($section === 'profile') {
            $request->validate([
                'contact_number' => 'nullable|string|max:20',
                'specialization' => 'nullable|string|max:255',
            ]);

            $user->contact_number = $request->input('contact_number', $user->contact_number);
            // PRC license fields are editable only until the RHU Admin verifies the account.
            if (!$user->isApproved()) {
                $user->license_number = $request->input('license_number', $user->license_number);
                $user->specialization = $request->input('specialization', $user->specialization);
            }
            $user->save();
            ActivityLog::log('update', 'Updated settings profile information');

            return back()->with('success', 'Profile information updated.');
        }

        // ── 3c. Two-factor authentication enrollment flag ──
        if ($section === '2fa') {
            $user->pref_2fa_enabled = $request->boolean('pref_2fa_enabled');
            $user->save();
            ActivityLog::log('update', $user->pref_2fa_enabled ? 'Enabled two-factor authentication' : 'Disabled two-factor authentication');

            return back()->with('success', $user->pref_2fa_enabled ? 'Two-factor authentication enabled.' : 'Two-factor authentication disabled.');
        }

        // ── 2. Workflow & notification preferences ──
        $request->validate([
            'pref_approval_summary' => 'required|in:immediate,daily,weekly,off',
        ]);

        $user->pref_high_risk_email = $request->boolean('pref_high_risk_email');
        $user->pref_high_risk_sms = $request->boolean('pref_high_risk_sms');
        $user->pref_high_risk_dashboard = $request->boolean('pref_high_risk_dashboard');
        $user->pref_approval_summary = $request->input('pref_approval_summary', 'daily');
        $user->pref_escalation_alerts = $request->boolean('pref_escalation_alerts');
        $user->pref_2fa_enabled = $request->boolean('pref_2fa_enabled');
        $user->save();
        ActivityLog::log('update', 'Updated workflow and notification preferences');

        return back()->with('success', 'Preferences saved successfully.');
    }

    // Pregnant Patients Management
    public function pregnantPatients()
    {
        $search = request('search');
        $status = request('status', 'active');
        
        // Cache pregnancy stats for 5 minutes (search must not poison global totals)
        $statsCacheKey = 'pregnancy_stats_' . $status;
        $stats = Cache::remember($statsCacheKey, 300, function () {
            return [
                'totalActive' => Pregnancy::active()->count(),
                'totalCompleted' => Pregnancy::completed()->count(),
                'totalHighRisk' => Pregnancy::highRisk()->count(),
            ];
        });
        
        $query = Pregnancy::with(['woman' => fn($q) => $q->withTrashed(), 'walkInPatient'])
            ->select('id', 'user_id', 'walk_in_patient_id', 'lmp', 'edd', 'aog', 'is_high_risk', 'created_at')
            ->when($status === 'active', function($q) {
                return $q->active();
            })
            ->when($status === 'completed', function($q) {
                return $q->completed();
            })
            ->when($status === 'high-risk', function($q) {
                return $q->highRisk();
            });

        // Apply search if provided
        if ($search) {
            $query->whereHas('woman', function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        $pregnancies = $query->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('midwife.pregnant-patients', array_merge($stats, compact(
            'pregnancies',
            'search',
            'status'
        )));
    }

    public function pregnancyHistory($id)
    {
        $pregnancy = Pregnancy::with(['checkups' => function($query) {
            $query->with('midwife')->orderBy('scheduled_date', 'desc');
        }])->findOrFail($id);

        $woman = User::where('role', 'user')->find($pregnancy->user_id);
        $pregnancyHistory = Pregnancy::where('user_id', $woman->id)
            ->with('checkups')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('midwife.pregnancy-history', compact(
            'pregnancy',
            'woman',
            'pregnancyHistory'
        ));
    }

    // Notifications
    public function notifications()
    {
        $notifications = auth()->user()->notifications()
            ->with('patientAlert')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('midwife.notifications', compact('notifications'));
    }

    public function showNotification($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return view('midwife.notifications.show', compact('notification'));
    }

    public function createNotification()
    {
        return view('midwife.notifications.create');
    }

    public function storeNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,warning,success,error',
            'target_role' => 'nullable|in:user,bhw,midwife',
        ]);

        // Broadcast to every approved user of the target role (empty = all field roles).
        $roles = $request->filled('target_role') ? [$request->target_role] : ['user', 'bhw', 'midwife'];
        $recipients = User::whereIn('role', $roles)
            ->where('status', 'approved')
            ->where('id', '!=', auth()->id())
            ->pluck('id');

        foreach ($recipients as $userId) {
            \App\Models\Notification::createNotification(
                $userId,
                $request->message,
                $request->title,
                $request->type
            );
        }

        return redirect()->route('midwife.notifications.index')
            ->with('success', 'Notification sent to ' . $recipients->count() . ' user(s).');
    }

    public function markNotificationAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function deleteNotification($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();

        return redirect()->route('midwife.notifications.index')
            ->with('success', 'Notification deleted successfully');
    }



    // Checkup Referrals Management
    public function referrals()
    {
        $referrals = CheckupReferral::with(['woman', 'walkInPatient', 'referredByBhw', 'assignedMidwife', 'convertedCheckup'])
            ->where('assigned_midwife_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('midwife.referrals.index', compact('referrals'));
    }

    public function showReferral($id)
    {
        $referral = CheckupReferral::with(['woman', 'walkInPatient', 'pregnancy.healthRecords', 'referredByBhw', 'assignedMidwife', 'convertedCheckup'])
            ->where('assigned_midwife_id', auth()->id())
            ->findOrFail($id);

        $attachedRecords = $referral->attachedHealthRecords();

        return view('midwife.referrals.show', compact('referral', 'attachedRecords'));
    }

    public function reviewReferral($id)
    {
        $referral = CheckupReferral::where('assigned_midwife_id', auth()->id())->findOrFail($id);
        $referral->update([
            'status' => 'reviewed',
            'reviewed_at' => now(),
        ]);

        // 5. Transparency: the referring BHW sees progress, not silence.
        app(\App\Services\WorkflowService::class)->notifyAction(
            (int) $referral->referred_by_bhw_id,
            '👀 Referral Under Review',
            "Your referral for {$referral->patient_name} is now under midwife review.",
            'info',
            route('bhw.referrals.show', $referral->id)
        );

        return redirect()->route('midwife.referrals.show', $id)
            ->with('success', 'Referral marked as reviewed.');
    }

    public function convertToCheckup($id)
    {
        $referral = CheckupReferral::with(['woman', 'walkInPatient'])->where('assigned_midwife_id', auth()->id())->findOrFail($id);

        // Idempotent: an already-accepted referral keeps its history and checkup.
        if ($referral->converted_checkup_id && ($checkup = Checkup::find($referral->converted_checkup_id))) {
            return redirect()->route('midwife.checkups.edit', $checkup->id)
                ->with('success', 'This referral was already accepted. Continuing with the linked checkup.');
        }

        // Create the checkup
        $checkup = Checkup::create([
            'user_id' => $referral->user_id,
            'walk_in_patient_id' => $referral->walk_in_patient_id,
            'midwife_id' => auth()->id(),
            'scheduled_by_id' => $referral->referred_by_bhw_id,
            'scheduled_date' => now()->addDay(), // Default to tomorrow
            'scheduled_time' => '09:00:00',
            'purpose' => $referral->reason,
            'notes' => $referral->bhw_notes,
            'status' => 'Scheduled',
        ]);

        // Preserve the handoff history instead of deleting the referral.
        $referral->update([
            'status' => 'scheduled',
            'converted_checkup_id' => $checkup->id,
            'accepted_at' => now(),
            'scheduled_at' => now(),
        ]);

        // Notify the BHW
        \App\Models\Notification::createNotification(
            $referral->referred_by_bhw_id,
            "Your referral for {$referral->patient_name} has been converted to a scheduled checkup.",
            '✅ Referral Scheduled',
            'success',
            route('bhw.checkups.index')
        );

        return redirect()->route('midwife.checkups.edit', $checkup->id)
            ->with('success', 'Referral accepted and moved to checkups. Please set the final scheduled date and time.');
    }

    public function declineReferral(Request $request, $id)
    {
        $request->validate([
            'midwife_notes' => 'required|string|max:1000',
        ]);

        $referral = CheckupReferral::where('assigned_midwife_id', auth()->id())->findOrFail($id);
        $referral->update([
            'status' => 'declined',
            'midwife_notes' => $request->midwife_notes,
        ]);

        // Notify the BHW
        \App\Models\Notification::createNotification(
            $referral->referred_by_bhw_id,
            "Your referral for {$referral->patient_name} has been declined. Reason: {$request->midwife_notes}",
            '❌ Referral Declined',
            'error',
            route('bhw.referrals.show', $referral->id)
        );

        return redirect()->route('midwife.referrals.index')
            ->with('success', 'Referral declined and BHW notified.');
    }

    // Walk-in Patients Management
    public function walkInPatients()
    {
        $patients = WalkInPatient::with(['recordedBy', 'purok', 'convertedToUser'])
            ->latest()
            ->paginate(10);

        return view('midwife.walk-in-patients.index', compact('patients'));
    }

    public function showWalkInPatient($id)
    {
        $patient = WalkInPatient::with(['recordedBy', 'purok', 'convertedToUser', 'checkupReferrals'])
            ->findOrFail($id);
        $smsLogs = $patient->contact_number
            ? \App\Models\SmsLog::where('phone_number', $patient->contact_number)
                ->orWhere('phone_number', $patient->smsPhone())
                ->latest()->limit(10)->get()
            : collect();

        $decisionSupport = app(\App\Services\MaternalAnalyticsService::class)->patientSupport($patient);
        return view('midwife.walk-in-patients.show', compact('patient', 'smsLogs', 'decisionSupport'));
    }

    /**
     * Portal Account Activation (Unlinked Profile → Enrolled Account).
     * Generates app credentials for a BHW-Managed woman and links the
     * field record (user_id + has_portal_access). Staff-activated, so
     * the account is approved immediately — no verification queue.
     */
    public function activateWalkInPatient($id)
    {
        $walkInPatient = WalkInPatient::with(['recordedBy', 'purok'])->findOrFail($id);

        if ($walkInPatient->linkedUserId()) {
            return back()->with('error', 'This field record already has an active portal account.');
        }

        return view('midwife.walk-in-patients.activate', compact('walkInPatient'));
    }

    public function storeActivatedAccount(Request $request, $id)
    {
        $walkInPatient = WalkInPatient::findOrFail($id);

        if ($walkInPatient->linkedUserId()) {
            return back()->with('error', 'This field record already has an active portal account.');
        }

        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'required|string|max:500',
            'barangay' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
        ]);

        $user = User::create([
            'first_name' => $walkInPatient->first_name,
            'middle_initial' => $walkInPatient->middle_initial,
            'last_name' => $walkInPatient->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'status' => 'approved',
            'address' => $request->address,
            'barangay' => $request->barangay,
            'purok_id' => $walkInPatient->purok_id,
            'date_of_birth' => $walkInPatient->date_of_birth,
            'contact_number' => $request->contact_number,
            'created_by_midwife_id' => auth()->id(),
        ]);

        $walkInPatient->update([
            'user_id' => $user->id,
            'has_portal_access' => true,
            'converted_to_user_id' => $user->id,
            'converted_at' => now(),
        ]);

        return redirect()->route('midwife.walk-in-patients.show', $id)
            ->with('success', 'Portal account activated — Unlinked Profile is now an Enrolled Account (Portal-Active).');
    }

    // --- Reports ---
    public function reportsIndex(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = User::where('role', 'user')->where('status', 'approved');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Calculate metrics in SQL (no full-table load)
        $totalPatients = (clone $query)->count();
        $pregnantPatients = (clone $query)->whereHas('pregnancies', fn ($q) => $q->active())->count();

        // Count completed checkups within the date range
        $completedCheckupsQuery = Checkup::where('status', 'Completed');
        if ($startDate) $completedCheckupsQuery->whereDate('scheduled_date', '>=', $startDate);
        if ($endDate) $completedCheckupsQuery->whereDate('scheduled_date', '<=', $endDate);
        $completedCheckups = $completedCheckupsQuery->count();

        // Count upcoming appointments
        $upcomingAppointments = Checkup::where('status', 'Scheduled')
            ->whereDate('scheduled_date', '>=', Carbon::now())
            ->count();

        // Filter by status if needed
        if ($status === 'pregnant') {
            $query->whereHas('pregnancies', function($q) {
                $q->active();
            });
        } elseif ($status === 'postpartum') {
             $query->whereHas('pregnancies', function($q) {
                $q->completed();
            });
        } elseif ($status === 'not_pregnant') {
             $query->whereDoesntHave('pregnancies', function($q) {
                $q->active();
            })->whereDoesntHave('pregnancies', function($q) {
                $q->completed();
            });
        }

        $patients = $query->paginate(10)->withQueryString();

        return view('midwife.reports.index', compact(
            'patients', 'totalPatients', 'pregnantPatients', 
            'completedCheckups', 'upcomingAppointments', 
            'search', 'status', 'startDate', 'endDate'
        ));
    }

    public function reportsDetails($id)
    {
        $woman = User::with(['healthRecords', 'checkups', 'pregnancies'])->findOrFail($id);
        return view('midwife.reports.details', compact('woman'));
    }

    public function reportsExportCsv(Request $request)
    {
        $patients = $this->filteredReportPatients($request);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="midwife_patients_report_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($patients) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Patient Name', 'Age', 'Status', 'Email', 'Contact Number', 'Barangay']);

            foreach ($patients as $patient) {
                fputcsv($file, [
                    $patient->name,
                    $patient->age,
                    $patient->pregnancy_status,
                    $patient->email,
                    $patient->contact_number,
                    $patient->barangay ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function reportsExportPdf(Request $request)
    {
        $patients = $this->filteredReportPatients($request);
        return view('midwife.reports.print', compact('patients'));
    }

    /**
     * Shared patient scope for the midwife reports browser + exports.
     */
    private function filteredReportPatients(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');

        $query = User::where('role', 'user')->where('status', 'approved');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status === 'pregnant') {
            $query->whereHas('pregnancies', fn ($q) => $q->active());
        } elseif ($status === 'postpartum') {
            $query->whereHas('pregnancies', fn ($q) => $q->completed());
        } elseif ($status === 'not_pregnant') {
            $query->whereDoesntHave('pregnancies', fn ($q) => $q->active())
                ->whereDoesntHave('pregnancies', fn ($q) => $q->completed());
        }

        return $query->orderBy('last_name')->orderBy('first_name')->get();
    }

    // --- Risk Alerts inbox (clinical review queue) ---

    public function riskAlerts()
    {
        $highRiskPregnancies = Pregnancy::active()
            ->highRisk()
            ->with(['woman', 'walkInPatient'])
            ->latest()
            ->paginate(10, ['*'], 'pregnancies_page');

        $urgentReferrals = \App\Models\CheckupReferral::with(['woman', 'walkInPatient', 'referredByBhw'])
            ->whereIn('urgency', ['urgent', 'emergency'])
            ->whereIn('status', ['pending', 'reviewed'])
            ->latest()
            ->paginate(10, ['*'], 'referrals_page');

        $missedCheckups = Checkup::missed()
            ->with(['woman', 'walkInPatient', 'midwife'])
            ->latest('scheduled_date')
            ->paginate(10, ['*'], 'missed_page');

        $pendingRecords = HealthRecord::with(['woman', 'walkInPatient', 'recordedBy'])
            ->where('workflow_status', 'submitted_to_midwife')
            ->latest()
            ->paginate(10, ['*'], 'records_page');

        return view('midwife.risk-alerts.index', compact(
            'highRiskPregnancies',
            'urgentReferrals',
            'missedCheckups',
            'pendingRecords'
        ));
    }
}
