<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locks clinical and write features for provisional (limited-access)
 * patients — accounts that verified email + finished onboarding but are
 * still awaiting RHU approval (User::hasProvisionalAccess()).
 *
 * Locked: pregnancies, cycle/menstruation logging, clinic checkups,
 * health records, care-team messaging, community posting/likes.
 * Open (no middleware): dashboard, learning materials, read-only
 * community browsing, notifications, own profile/settings.
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

        if (! $user || ! $user->hasProvisionalAccess()) {
            return $next($request);
        }

        $message = 'This feature unlocks after RHU approval of your account. '
            . 'Learning materials, community browsing and your profile are available meanwhile.';

        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json(['message' => $message], 403);
        }

        if (! $request->isMethod('get')) {
            return back()->withErrors(['access' => $message])->withInput();
        }

        return redirect()->route('user.dashboard')->with('provisional_blocked', $message);
    }
}
