<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Refresh user from database to get latest role
        $user = $user->fresh();

        // Check if user has any of the required roles
        if (empty($roles) || !in_array($user->role, $roles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
