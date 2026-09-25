<?php

namespace App\Http\Controllers;

use App\Models\SmsLog;
use App\Models\User;
use App\Models\Purok;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * GET /cho/sms — SMS log dashboard with send form.
     */
    public function index(Request $request)
    {
        $userRole = auth()->user()->role;
        $query = SmsLog::with('user')->latest();
        if (in_array($userRole, ['bhw', 'midwife'], true)) {
            $barangay = auth()->user()->barangay;
            $purokId = auth()->user()->purok_id;
            $query->where(function ($q) use ($barangay, $purokId) {
                $q->whereHas('user', function ($u) use ($barangay, $purokId) {
                    if ($barangay) {
                        $u->where('barangay', $barangay);
                    }
                    if ($purokId) {
                        $u->where('purok_id', $purokId);
                    }
                })->orWhereNull('user_id');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('phone_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', fn($u) => $u->where('first_name', 'like', '%' . $request->search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->search . '%'));
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        $stats = [
            'total'   => SmsLog::count(),
            'sent'    => SmsLog::where('status', 'sent')->count(),
            'failed'  => SmsLog::where('status', 'failed')->count(),
            'enabled' => User::where('role', 'user')->where('status', 'approved')
                ->where('sms_opt_out', false)->whereNotNull('contact_number')
                ->where('contact_number', '!=', '')->count(),
        ];

        if ($userRole === 'cho') {
            // Full history of the selected thread (chat view is not paginated).
            $threadPhone = trim((string) $request->query('phone'));
            $threadLogs = $threadPhone !== ''
                ? SmsLog::with('user')->where('phone_number', $threadPhone)->latest()->limit(100)->get()->sortBy('created_at')->values()
                : collect();

            return view('cho.sms.index', compact('logs', 'stats', 'threadLogs'));
        }

        $patientsQuery = User::where('role', 'user')
            ->where('status', 'approved')
            ->where('sms_opt_out', false)
            ->whereNotNull('contact_number');

        $puroksQuery = Purok::orderBy('name');

        if ($userRole === 'bhw') {
            // Scope patients to BHW's purok if they have one
            if (auth()->user()->purok_id) {
                $patientsQuery->where('purok_id', auth()->user()->purok_id);
                $puroksQuery->where('id', auth()->user()->purok_id);
            }
        }

        $patients = $patientsQuery->orderBy('first_name')->get(['id', 'first_name', 'middle_initial', 'last_name', 'contact_number']);
        $puroks = $puroksQuery->get();

        // Full history of the selected patient thread (chat view is not paginated).
        $threadUserId = $request->integer('to') ?: null;
        $threadLogs = $threadUserId
            ? SmsLog::with('user')->where('user_id', $threadUserId)->latest()->limit(100)->get()->sortBy('created_at')->values()
            : collect();

        // Latest log per patient for accurate conversation-list previews.
        $latestIds = SmsLog::whereIn('user_id', $patients->pluck('id'))
            ->groupBy('user_id')
            ->selectRaw('MAX(id) as id')
            ->pluck('id');
        $recentByUser = $latestIds->isNotEmpty()
            ? SmsLog::whereIn('id', $latestIds)->get()->keyBy('user_id')
            : collect();

        if ($userRole === 'rhu') {
            return view('rhu.sms.index', compact('logs', 'patients', 'puroks', 'stats', 'threadLogs', 'recentByUser'));
        }

        return view('midwife.sms.index', compact('logs', 'patients', 'puroks', 'stats', 'threadLogs', 'recentByUser'));
    }

    /**
     * POST /cho/sms/send — Send a custom SMS to a single patient.
     */
    public function send(Request $request)
    {
        if (!in_array(auth()->user()->role, ['midwife', 'rhu'], true)) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'message_en' => 'required|string|max:320',
            'message_tl' => 'nullable|string|max:320',
        ]);

        $user = User::findOrFail($request->user_id);

        if (!$user->hasSmsEnabled()) {
            return back()->with('error', "Patient {$user->name} has SMS disabled or no contact number.");
        }

        $smsService = new SmsService();
        $sent = $smsService->sendCustom($user, $request->message_en, $request->message_tl ?? '');

        \App\Models\ActivityLog::log('create', "Sent custom SMS to {$user->name} ({$user->contact_number})");

        if (!$sent) {
            return back()->with('error', "Failed to send SMS. Check the SMS gateway configuration.");
        }

        // Tell the truth when the system is in mock mode (logged, not delivered).
        if (\App\Models\Setting::get('sms.mock', '1') === '1') {
            return back()->with('success', "SMS logged (MOCK mode — nothing was delivered). Switch Mode to Live in CHO → Settings → SMS Gateway to send real texts.");
        }

        return back()->with('success', "SMS sent to {$user->name} successfully.");
    }

    /**
     * POST /midwife|sms|bhw/sms/walk-in — Manual SMS to a walk-in patient.
     * Walk-ins have no portal account; the contact number is the channel.
     */
    public function sendWalkIn(Request $request)
    {
        if (!in_array(auth()->user()->role, ['midwife', 'rhu'], true)) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'walk_in_patient_id' => 'required|exists:walk_in_patients,id',
            'message_en' => 'required|string|max:320',
            'message_tl' => 'nullable|string|max:320',
        ]);
        $walkIn = \App\Models\WalkInPatient::findOrFail($request->walk_in_patient_id);
        if (!$walkIn->hasSmsEnabled()) {
            return back()->with('error', "Walk-in {$walkIn->full_name} has no contact number on file.");
        }
        $message = $request->message_en;
        if ($request->filled('message_tl')) {
            $message .= "\n\n".$request->message_tl;
        }
        $sent = app(\App\Services\SmsService::class)->sendToPhone($walkIn->contact_number, $message, 'custom');
        \App\Models\ActivityLog::log('create', "Sent custom SMS to walk-in {$walkIn->full_name} ({$walkIn->contact_number})");
        if (!$sent) {
            return back()->with('error', 'Failed to send SMS. Check the SMS gateway configuration.');
        }
        if (\App\Models\Setting::get('sms.mock', '1') === '1') {
            return back()->with('success', 'SMS logged (MOCK mode — nothing was delivered).');
        }
        return back()->with('success', "SMS sent to {$walkIn->full_name} successfully.");
    }

    /**
     * POST /cho/sms/broadcast — Send a broadcast SMS to a group of patients.
     */
    public function broadcast(Request $request)
    {
        if (!in_array(auth()->user()->role, ['midwife', 'rhu'], true)) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'message_en' => 'required|string|max:320',
            'message_tl' => 'nullable|string|max:320',
            'purok_id'   => 'nullable|exists:puroks,id',
            'barangay'   => 'nullable|string|max:255',
        ]);

        $purokId = $request->purok_id ? (int)$request->purok_id : null;
        if (auth()->user()->role === 'bhw' && auth()->user()->purok_id) {
            $purokId = auth()->user()->purok_id;
        }

        $smsService = new SmsService();
        $sent = $smsService->broadcast(
            $request->message_en,
            $request->message_tl ?? '',
            $purokId,
            $request->barangay
        );

        $scope = $purokId ? "Purok #{$purokId}" : ($request->barangay ?: 'all patients');
        \App\Models\ActivityLog::log('create', "Sent broadcast SMS to {$scope}. Total sent: {$sent}");

        if (\App\Models\Setting::get('sms.mock', '1') === '1') {
            return back()->with('success', "Broadcast logged for {$sent} patient(s) (MOCK mode — nothing was delivered). Switch Mode to Live in CHO → Settings → SMS Gateway to send real texts.");
        }

        return back()->with('success', "Broadcast SMS sent to {$sent} patient(s) successfully.");
    }
}
