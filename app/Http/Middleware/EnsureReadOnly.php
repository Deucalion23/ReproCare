<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * View-only enforcement for patients awaiting RHU approval that have not
 * finished verification + onboarding (User::hasViewOnlyAccess()).
 *
 * View-only users may browse, but every state-changing request is bounced
 * — except the flows that graduate them: logout, email verification and
 * profile completion. Provisional and approved users always pass.
 *
 * Bounced GET requests land back on the dashboard with an explanatory
 * flash; bounced writes go back with input preserved; API/AJAX callers
 * get a 403 JSON payload.
 */
class EnsureReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasViewOnlyAccess()) {
            return $next($request);
        }

        // Graduation flows must always work (no redirect loops), and
        // read-only views stay readable — only writes bounce.
        if ($request->routeIs([
            'logout',
            'verification.*',
            'profile.complete',
            'profile.complete.store',
            'profile.show',
            'profile.view',
            'profile.download',
        ])) {
            return $next($request);
        }

        $message = 'Browsing is view-only until your account is verified. '
            . 'Finish email verification and profile setup to unlock more.';

        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json(['message' => $message], 403);
        }

        if (! $request->isMethod('get')) {
            return back()->withErrors(['access' => $message])->withInput();
        }

        return redirect()->route('user.dashboard')->with('viewonly_blocked', $message);
    }
}
