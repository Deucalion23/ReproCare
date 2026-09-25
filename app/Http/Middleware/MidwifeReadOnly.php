<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MidwifeReadOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = optional($request->route())->getName();
        $method = strtoupper($request->method());

        $writeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

        if (in_array($method, $writeMethods, true)) {
            abort(403, 'Midwives are only permitted to view and review records.');
        }

        if ($routeName && (str_ends_with($routeName, '.create') || str_ends_with($routeName, '.edit'))) {
            abort(403, 'Midwives are only permitted to view and review records.');
        }

        return $next($request);
    }
}
