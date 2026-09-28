<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stop browsers / proxies from serving stale pages and assets metadata.
 *
 * Authenticated pages already send these headers (see
 * AbsoluteLogoutProtection); public pages (login, landing) did not, so
 * users kept seeing pre-deploy HTML after each release. Dynamic portal
 * responses must always be revalidated.
 */
class NoCacheResponses
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');

        return $response;
    }
}
