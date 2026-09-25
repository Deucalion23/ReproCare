<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ChoHandover;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChoHandoverController extends Controller
{
    /**
     * Handover wizard: current CHO, recovery-key status, history.
     */
    public function index()
    {
        $outgoing = auth()->user()->fresh();
        $candidates = User::whereIn('role', ['rhu', 'midwife'])
            ->where('status', 'approved')
            ->where('id', '!=', $outgoing->id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'email', 'role']);
        $history = ChoHandover::with(['outgoing', 'incoming'])->latest()->take(10)->get();
        $recoveryKeySet = (bool) Setting::get('handover.recovery_key_hash');

        return view('cho.handover.index', compact('outgoing', 'candidates', 'history', 'recoveryKeySet'));
    }

    /**
     * Generate (or regenerate) the system recovery key.
     * Requires the outgoing CHO's current password. Shown once.
     */
    public function setupRecoveryKey(Request $request)
    {
        $request->validate(['current_password' => 'required|string']);
        $user = auth()->user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $plainKey = 'RC-' . Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4));
        Setting::set('handover.recovery_key_hash', Hash::make($plainKey), $user->id);
        Setting::set('handover.recovery_key_set_at', now()->toDateTimeString(), $user->id);
        ActivityLog::log('update', 'CHO generated a new role-handover recovery key (previous key invalidated)');

        return back()->with('recovery_key_plain', $plainKey)
            ->with('success', 'New recovery key generated. Copy it now — it will never be shown again. Store it sealed with the city records.');
    }

    /**
     * Execute the role transfer atomically. Dual authorization required:
     * outgoing password + (recovery key OR RHU admin co-signature).
     */
    public function execute(Request $request)
    {
        $outgoing = auth()->user()->fresh();
        abort_unless($outgoing && $outgoing->role === 'cho' && $outgoing->status === 'approved', 403);

        $data = $request->validate([
            'mode' => 'required|in:existing,new',
            'incoming_user_id' => 'required_if:mode,existing|nullable|exists:users,id',
            'first_name' => 'required_if:mode,new|nullable|string|max:100',
            'middle_initial' => 'nullable|string|max:5',
            'last_name' => 'required_if:mode,new|nullable|string|max:100',
            'email' => 'required_if:mode,new|nullable|email|max:255|unique:users,email',
            'new_password' => 'required_if:mode,new|nullable|string|min:8',
            'contact_number' => 'nullable|string|max:30',
            'outgoing_password' => 'required|string',
            'auth_method' => 'required|in:recovery_key,rhu_cosign',
            'recovery_key' => 'required_if:auth_method,recovery_key|nullable|string',
            'cosigner_email' => 'required_if:auth_method,rhu_cosign|nullable|email',
            'cosigner_password' => 'required_if:auth_method,rhu_cosign|nullable|string',
            'confirm_email' => 'required|email',
            'acknowledge' => 'accepted',
        ]);

        // ── First authorization: outgoing CHO password ──
        if (!Hash::check($data['outgoing_password'], $outgoing->password)) {
            return back()->withErrors(['outgoing_password' => 'Outgoing authorization failed: password is incorrect.'])->withInput();
        }

        // ── Resolve + validate incoming account ──
        if ($data['mode'] === 'existing') {
            $incoming = User::find($data['incoming_user_id']);
            if (!$incoming || $incoming->id === $outgoing->id) {
                return back()->withErrors(['incoming_user_id' => 'Select a different account from the outgoing CHO.'])->withInput();
            }
            if ($incoming->status !== 'approved') {
                return back()->withErrors(['incoming_user_id' => 'Incoming account must be approved before transfer.'])->withInput();
            }
            if (!in_array($incoming->role, ['rhu', 'midwife'], true)) {
                return back()->withErrors(['incoming_user_id' => 'Only approved RHU or Midwife staff may receive the CHO role.'])->withInput();
            }
            $incomingEmail = $incoming->email;
        } else {
            $incoming = null;
            $incomingEmail = strtolower($data['email']);
        }

        if (strtolower($data['confirm_email']) !== strtolower($incomingEmail)) {
            return back()->withErrors(['confirm_email' => 'Confirmation email must exactly match the incoming account email.'])->withInput();
        }

        // ── Second authorization ──
        $cosigner = null;
        if ($data['auth_method'] === 'recovery_key') {
            $hash = Setting::get('handover.recovery_key_hash');
            if (!$hash || !Hash::check((string) $data['recovery_key'], $hash)) {
                return back()->withErrors(['recovery_key' => 'Invalid recovery key.'])->withInput();
            }
        } else {
            $cosigner = User::where('email', $data['cosigner_email'])
                ->where('role', 'rhu')
                ->where('status', 'approved')
                ->first();
            if (!$cosigner || $cosigner->id === $outgoing->id || ($incoming && $cosigner->id === $incoming->id)) {
                return back()->withErrors(['cosigner_email' => 'Co-signer must be an approved RHU admin other than the parties.'])->withInput();
            }
            if (!Hash::check((string) $data['cosigner_password'], $cosigner->password)) {
                return back()->withErrors(['cosigner_password' => 'RHU co-signer credentials are incorrect.'])->withInput();
            }
        }

        $outgoingName = $outgoing->name;
        $handover = null;

        DB::transaction(function () use ($data, $outgoing, $outgoingName, &$incoming, &$handover, $cosigner) {
            // 1. Prepare incoming account (promote or create — never touch outgoing first).
            if ($incoming) {
                $incoming->update(['role' => 'cho', 'status' => 'approved']);
                $incoming->refresh();
            } else {
                $incoming = User::create([
                    'first_name' => $data['first_name'],
                    'middle_initial' => $data['middle_initial'] ?? null,
                    'last_name' => $data['last_name'],
                    'email' => strtolower($data['email']),
                    'password' => Hash::make($data['new_password']),
                    'contact_number' => $data['contact_number'] ?? null,
                    'role' => 'cho',
                    'status' => 'approved',
                    'address' => 'City Health Office, San Carlos City',
                    'barangay' => 'Poblacion',
                ]);
            }
            // Incoming receives all city-wide alerts from day one.
            $incoming->update([
                'pref_mortality_alerts' => true,
                'pref_audit_warnings' => true,
                'pref_compliance_updates' => true,
                'pref_escalation_alerts' => true,
            ]);

            // 2. Retire outgoing account: archive, unbind signature, silence alerts.
            $formerSignature = $outgoing->signature_image;
            $archivedSignature = null;
            if ($formerSignature && Storage::disk('public')->exists($formerSignature)) {
                $ext = pathinfo($formerSignature, PATHINFO_EXTENSION) ?: 'png';
                $archivedSignature = 'signatures/archive/former-cho-' . $outgoing->id . '-' . now()->format('YmdHis') . '.' . $ext;
                Storage::disk('public')->move($formerSignature, $archivedSignature);
            }
            $outgoing->update([
                'status' => 'archived',
                'signature_image' => null,
                'pref_mortality_alerts' => false,
                'pref_audit_warnings' => false,
                'pref_compliance_updates' => false,
                'pref_escalation_alerts' => false,
                'pref_high_risk_email' => false,
                'pref_high_risk_sms' => false,
                'pref_high_risk_dashboard' => false,
                'out_of_office' => false,
                'delegate_to_user_id' => null,
            ]);

            // 3. Terminate every browser session of the outgoing account.
            \App\Services\SessionRevocationService::terminateUserSessions((int) $outgoing->id);

            // 4. Record the transfer + unalterable system event (history keeps user_ids).
            $handover = ChoHandover::create([
                'outgoing_user_id' => $outgoing->id,
                'incoming_user_id' => $incoming->id,
                'performed_by_id' => $outgoing->id,
                'outgoing_name' => $outgoingName,
                'incoming_name' => $incoming->name,
                'auth_method' => $data['auth_method'],
                'former_signature_path' => $archivedSignature,
            ]);
            ActivityLog::logProtected(
                'handover',
                'SYSTEM EVENT: CHO Super Admin Role transferred from [' . $outgoingName . '] to [' . $incoming->name . '] on [' . now()->toDateTimeString() . '] via ' . ($data['auth_method'] === 'recovery_key' ? 'system recovery key' : 'RHU co-signature by ' . ($cosigner ? $cosigner->name : 'council')) . '.',
                $handover
            );

            // 5. Invalidate the used recovery key; the new CHO must generate a fresh one.
            Setting::forget('handover.recovery_key_hash');
            Setting::forget('handover.recovery_key_set_at');

            // 6. Notify the incoming CHO (onboarding prompt).
            Notification::createNotification(
                $incoming->id,
                'You are now the CHO Super Admin. Complete onboarding: update contact details, upload your digital signature + license, and enable 2FA in Settings → My Profile.',
                'CHO Role Transferred to You',
                'warning',
                route('cho.settings')
            );
        });

        // Operator was the outgoing CHO: end their session immediately.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with(
            'success',
            'Handover complete. ' . $outgoingName . ' is archived; ' . $incoming->name . ' is now CHO Super Admin. ' .
            'New CHO: sign in and finish onboarding (My Profile → signature, license, 2FA) before generating official reports.'
        );
    }
}
