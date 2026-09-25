<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AbsoluteLogoutProtection
{
    protected function hasAuthenticatedUser(): bool
    {
        return Auth::check();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$this->hasAuthenticatedUser()) {
            if (!Session::has('user_logged_out')) {
                Session::put('user_logged_out', true);
            }
            // Preserve proper 302 semantics so route expectations, caching,
            // and /dashboard dispatch keep working. JSON callers get 401.
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401)
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private')
                    ->header('Pragma', 'no-cache');
            }
            return redirect()->route('login')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0, private')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
                ->header('Surrogate-Control', 'no-store');
        }

        // Check if user just logged in (clear logout flag)
        if (Session::has('user_logged_out') && $this->hasAuthenticatedUser()) {
            // Clear the logout flag since user is now authenticated
            Session::forget('user_logged_out');
        }

        $response = $next($request);
        
        // Add aggressive headers to prevent caching for authenticated pages
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
        $response->headers->set('Surrogate-Control', 'no-store');
        $response->headers->set('Vary', 'Accept-Encoding');
        
        return $response;
    }
}
