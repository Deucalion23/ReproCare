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
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'prevent-back' => \App\Http\Middleware\PreventBackButton::class,
            'auth.ensure' => \App\Http\Middleware\EnsureAuthenticated::class,
            'force.logout' => \App\Http\Middleware\ForceLogout::class,
            'absolute.logout' => \App\Http\Middleware\AbsoluteLogoutProtection::class,
            'midwife.readonly' => \App\Http\Middleware\MidwifeReadOnly::class,
            'sync' => \App\Http\Middleware\HandleSyncRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
