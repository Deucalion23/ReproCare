<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Drop-in replacement for Laravel's `verified` middleware on patient
 * browsing routes. Verified users pass as usual; unverified users who
 * qualify for view-only access (pending RHU approval) also pass so they
 * can browse the portal in read-only mode. Any other unverified user
 * (e.g. approved but never verified) is sent to the verification
 * notice exactly like before.
 */
class EnsureEmailVerifiedOrViewOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail()) {
            return $next($request);
        }

        if (method_exists($user, 'hasViewOnlyAccess') && $user->hasViewOnlyAccess()) {
            return $next($request);
        }

        return $request->expectsJson()
            ? abort(403, 'Your email address is not verified.')
            : redirect()->route('verification.notice');
    }
}
