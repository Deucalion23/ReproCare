<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
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
            // Add headers to prevent caching
            return response()->view('auth.login')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
                ->header('Surrogate-Control', 'no-store');
        }

        $response = $next($request);
        
        // Add aggressive headers to prevent caching for authenticated pages
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
        $response->headers->set('Surrogate-Control', 'no-store');
        
        return $response;
    }
}
