<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kills sessions whose account is no longer approved.
 *
 * Without this, an account rejected/suspended AFTER logging in keeps a valid
 * session: portal pages still render (no per-request status check), the PWA
 * session-check then bounces the browser to /auth/login, and the guest
 * middleware bounces it straight back — an endless dashboard↔login reload.
 *
 * - approved → pass.
 * - rejected → restore (rejection soft-deletes), re-queue as pending so the
 *   RHU 1 administrator can reassess / re-verify, log out, and show the
 *   rejection message on the login page. Never reaches any dashboard.
 * - pending + incomplete patient profile → keep the session, send to
 *   onboarding (Google sign-ups must finish phone + barangay first).
 * - pending patients otherwise → PROVISIONAL or VIEW-ONLY session stays
 *   alive; downstream gates route them (verification notice, onboarding,
 *   limited dashboard) and the full.access / readonly middlewares enforce
 *   the limits. Only non-patient pending sessions are abnormal.
 * - anything else (suspended/inactive/archived/declined) → log out + inactive
 *   notice. Staff are always created approved, so a non-approved staff
 *   session is abnormal by definition.
 *
 * The onboarding / verification / logout routes are exempt so pending users
 * can always finish the flows that unblock them (no redirect loops).
 */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($request->routeIs([
            'profile.complete',
            'profile.complete.store',
            'logout',
            'verification.*',
        ])) {
            return $next($request);
        }

        // The auth middleware has already loaded this user from the database
        // for the current request. Refreshing it again made every navigation
        // wait for a second remote database query. On the next request the
        // guard loads the current account state again before this middleware
        // runs, so status changes still take effect immediately.
        $status = $user->status ?? 'approved';

        if ($status === 'approved') {
            return $next($request);
        }

        if ($status === 'rejected') {
            if ($user->trashed()) {
                $user->restore();
            }
            $user->update(['status' => 'pending']);
            \App\Models\ActivityLog::log(
                'update',
                "Rejected account re-queued for RHU re-verification on access attempt: {$user->name} ({$user->email})",
                $user
            );
            $this->killSession($request);

            return redirect()->route('login')->withErrors([
                'email' => User::REJECTED_LOGIN_MESSAGE,
            ])->onlyInput('email');
        }

        // Provisional + view-only access: any pending patient session stays
        // alive. The email.viewonly / complete.profile middlewares route
        // them (verification notice, onboarding, limited dashboard) and the
        // full.access / readonly middlewares enforce the limits.
        // (Demo accounts pass here too — downstream gates grant them full
        // access.) Non-patient pending sessions stay abnormal: kill them.
        if ($status === 'pending' && ($user->role ?? null) === 'user') {
            return $next($request);
        }

        $this->killSession($request);

        if ($status === 'pending') {
            return redirect()->route('login')->with('pending_registration', true);
        }

        return redirect()->route('login')->withErrors([
            'email' => 'Your account is no longer active. Please contact the City Health Office.',
        ])->onlyInput('email');
    }

    protected function killSession(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
