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
     * GET /{portal}/sms — conversation workspace: the patient list on the left,
     * the selected thread on the right.
     */
    public function index(Request $request)
    {
        $userRole = auth()->user()->role;
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $type = (string) $request->query('type', '');
        $like = '%' . $search . '%';

        $patientsBase = User::where('role', 'user')
            ->where('status', 'approved')
            ->where('sms_opt_out', false)
            ->whereNotNull('contact_number')
            ->where('contact_number', '!=', '');

        if ($userRole === 'bhw' && auth()->user()->purok_id) {
            $patientsBase->where('purok_id', auth()->user()->purok_id);
        }

        // One closure builds every patient count and the list itself, so a tab can
        // never advertise patients that clicking it would not show. The status and
        // type conditions live inside a single whereHas: the same text message has
        // to be both sent and of that type, not one message of each.
        $messageable = function (?string $logStatus = null) use ($patientsBase, $search, $like, $type) {
            return (clone $patientsBase)->when(
                $search !== '',
                function ($query) use ($like) {
                    $query->where(function ($inner) use ($like) {
                        $inner->where('first_name', 'like', $like)
                            ->orWhere('middle_initial', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhere('contact_number', 'like', $like);
                    });
                }
            )->when(
                $logStatus !== null || $type !== '',
                fn ($query) => $query->whereHas('smsLogs', function ($log) use ($logStatus, $type) {
                    $log->when($logStatus !== null, fn ($s) => $s->where('status', $logStatus))
                        ->when($type !== '', fn ($t) => $t->where('type', $type));
                })
            );
        };

        $puroks = Purok::when(
            $userRole === 'bhw' && auth()->user()->purok_id,
            fn ($query) => $query->where('id', auth()->user()->purok_id)
        )->orderBy('name')->get();

        $enabled = (clone $patientsBase)->count();

        if ($userRole === 'cho') {
            // The CHO desk is an audit view: a flat city-wide log feed, no threads.
            $logs = $this->staffScopedLogs()
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($type !== '', fn ($query) => $query->where('type', $type))
                ->when($search !== '', function ($query) use ($like) {
                    $query->where(function ($inner) use ($like) {
                        $inner->where('phone_number', 'like', $like)
                            ->orWhereHas('user', fn ($u) => $u->where('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like));
                    });
                })
                ->latest()
                ->paginate(20)
                ->withQueryString();

            $stats = [
                'total'   => $this->staffScopedLogs()->count(),
                'sent'    => $this->staffScopedLogs()->where('status', 'sent')->count(),
                'failed'  => $this->staffScopedLogs()->where('status', 'failed')->count(),
                'enabled' => $enabled,
            ];

            return view('cho.sms.index', compact('logs', 'stats') + $this->thread($request));
        }

        $statusTabs = collect(['' => 'All', 'sent' => 'Sent', 'failed' => 'Failed', 'pending' => 'Pending'])
            ->mapWithKeys(fn ($label, $key) => [$key => [
                'label' => $label,
                'count' => $messageable($key === '' ? null : $key)->count(),
            ]]);

        $patients = $messageable($status === '' ? null : $status)
            ->orderBy('first_name')
            ->paginate(25)
            ->withQueryString();

        // Latest log per listed patient, so previews survive pagination.
        $latestIds = SmsLog::whereIn('user_id', $patients->getCollection()->pluck('id'))
            ->groupBy('user_id')
            ->selectRaw('MAX(id) as id')
            ->pluck('id');
        $recentByUser = $latestIds->isNotEmpty()
            ? SmsLog::whereIn('id', $latestIds)->get()->keyBy('user_id')
            : collect();

        $view = compact('patients', 'statusTabs', 'recentByUser', 'search', 'status', 'type', 'puroks')
            + ['stats' => ['enabled' => $enabled]]
            + $this->thread($request);

        return $userRole === 'rhu'
            ? view('rhu.sms.index', $view)
            : view('midwife.sms.index', $view);
    }

    /**
     * The selected thread, resolved from ?to= (a patient) or ?phone= (a number
     * with no account yet). The patient is fetched on its own because the
     * conversation list is paginated and the open thread is rarely on page one.
     *
     * @return array{threadUser: ?User, threadLogs: \Illuminate\Support\Collection, threadUserId: ?int, threadPhone: string}
     */
    private function thread(Request $request): array
    {
        $threadUserId = $request->integer('to') ?: null;
        $threadPhone = trim((string) $request->query('phone', ''));

        $query = $threadUserId
            ? SmsLog::with('user')->where('user_id', $threadUserId)
            : ($threadPhone !== '' ? SmsLog::with('user')->where('phone_number', $threadPhone) : null);

        return [
            'threadUser' => $threadUserId ? User::where('role', 'user')->find($threadUserId) : null,
            'threadLogs' => $query
                ? $query->latest()->limit(100)->get()->sortBy('created_at')->values()
                : collect(),
            'threadUserId' => $threadUserId,
            'threadPhone' => $threadPhone,
        ];
    }

    /** SMS history this account is allowed to see: staff only cover their own catchment. */
    private function staffScopedLogs()
    {
        $query = SmsLog::with('user');
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'], true)) {
            return $query;
        }

        $barangay = auth()->user()->barangay;
        $purokId = auth()->user()->purok_id;

        return $query->where(function ($q) use ($barangay, $purokId) {
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
