<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces patient accounts with an incomplete onboarding profile back to
 * the profile completion form before they can use the rest of the app.
 *
 * - Only applies to the patient role ('user'). Staff roles (cho, rhu,
 *   midwife, bhw, bhw_president) are never gated by this middleware.
 * - The completion form, its submit endpoint, logout, and email
 *   verification routes are always allowed through (no redirect loop).
 */
class EnsureProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Staff accounts are never subject to patient onboarding.
        if (($user->role ?? null) !== 'user') {
            return $next($request);
        }

        if ((bool) ($user->is_profile_complete ?? true)) {
            return $next($request);
        }

        // Allow the completion flow itself (plus logout / verification)
        // so incomplete users can never get stuck in a redirect loop.
        if ($request->routeIs([
            'profile.complete',
            'profile.complete.store',
            'logout',
            'verification.*',
        ])) {
            return $next($request);
        }

        return redirect()->route('profile.complete');
    }
}
