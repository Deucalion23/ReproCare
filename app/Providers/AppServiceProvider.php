<?php

namespace App\Providers;

use App\Services\ProductionTestScenarioProvisioner;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Wire Laravel's built-in verification email to event(new Registered()).
        // With MAIL_MAILER=log this lands in laravel.log during local dev.
        Event::listen(Registered::class, SendEmailVerificationNotification::class);

        if (app()->environment('production') || env('RENDER') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            URL::forceScheme('https');
        }

        // Render normally seeds these during container startup. If that hook
        // was skipped, retry safely on the first production web request.
        if (!app()->runningInConsole()) {
            app()->booted(fn () => app(ProductionTestScenarioProvisioner::class)->ensure());
        }
    }
}
