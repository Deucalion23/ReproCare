<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locks clinical and write features for patients awaiting RHU approval —
 * both the provisional tier (verified + onboarded) and the view-only tier
 * (verification or onboarding still outstanding).
 *
 * Locked: pregnancies, cycle/menstruation logging, clinic checkups,
 * health records, care-team messaging, community posting/likes.
 * Open (no middleware): dashboard, learning materials (browse; quiz
 * submit is separately read-only gated), read-only community browsing,
 * notifications, own profile viewing.
 *
 * Demo accounts always pass (full access). Approved users always pass.
 * Bounced GET requests land back on the dashboard with an explanatory
 * flash; bounced writes go back with input preserved; API/AJAX callers
 * get a 403 JSON payload.
 */
class EnsureFullAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $this->isLocked($user)) {
            return $next($request);
        }

        $message = $this->messageFor($user);

        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json(['message' => $message], 403);
        }

        if (! $request->isMethod('get')) {
            return back()->withErrors(['access' => $message])->withInput();
        }

        return redirect()->route('user.dashboard')->with('provisional_blocked', $message);
    }

    /**
     * Any pending patient (except code-listed demo accounts) is locked
     * out of clinical + write features — provisional and view-only alike.
     */
    protected function isLocked($user): bool
    {
        return ($user->role ?? null) === 'user'
            && ($user->status ?? 'approved') === 'pending'
            && ! \App\Models\User::isDemoAccount($user->email ?? null);
    }

    /**
     * Point view-only users at the exact step that unlocks them.
     */
    protected function messageFor($user): string
    {
        if (method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
            return 'Verify your email first — check your inbox for the verification link, then this feature unlocks after RHU approval.';
        }

        if (method_exists($user, 'needsProfileCompletion') && $user->needsProfileCompletion()) {
            return 'Finish setting up your profile first — then this feature unlocks after RHU approval.';
        }

        return 'This feature unlocks after RHU approval of your account. '
            . 'Learning materials, community browsing and your profile are available meanwhile.';
    }
}
