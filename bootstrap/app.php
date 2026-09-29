<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        // Portal pages are personalized — never let browsers/proxies
        // serve stale HTML or redirect targets after a deploy.
        $middleware->append(\App\Http\Middleware\NoCacheResponses::class);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'prevent-back' => \App\Http\Middleware\PreventBackButton::class,
            'auth.ensure' => \App\Http\Middleware\EnsureAuthenticated::class,
            'force.logout' => \App\Http\Middleware\ForceLogout::class,
            'absolute.logout' => \App\Http\Middleware\AbsoluteLogoutProtection::class,
            'midwife.readonly' => \App\Http\Middleware\MidwifeReadOnly::class,
            'sync' => \App\Http\Middleware\HandleSyncRequests::class,
            'complete.profile' => \App\Http\Middleware\EnsureProfileComplete::class,
            'account.active' => \App\Http\Middleware\EnsureAccountActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Expired/invalid CSRF token (e.g. session wiped by a redeploy,
        // or a stale cached login form) shows a bare 419 page by default.
        // Send users back to login with an explanation instead.
        // NOTE: the framework converts TokenMismatchException to a plain
        // HttpException(419) before render callbacks run, so match on the
        // status code and let anything else fall through (return null).
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Session expired. Please refresh and try again.'], 419);
            }

            return redirect()->guest(route('login'))
                ->withErrors(['session' => 'Your session expired. Please log in again.']);
        });
    })->create();
