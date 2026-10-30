<?php

namespace App\Services;

use Database\Seeders\TestScenarioAccountsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Provides a web-request fallback for Render deployments.
 *
 * Container startup normally creates these fixtures. This fallback is only
 * enabled explicitly in production and retries a missed startup seed on the
 * next request, without changing an already-populated database.
 */
class ProductionTestScenarioProvisioner
{
    private const EXPECTED_ACCOUNT_COUNT = 100;

    public function ensure(): void
    {
        if (!app()->environment('production')
            || !config('reprocare.seed_test_scenarios', false)
            || !Schema::hasTable('users')) {
            return;
        }

        try {
            $count = DB::table('users')
                ->where('email', 'like', 'reprocare-test-%@example.test')
                ->count();

            if ($count >= self::EXPECTED_ACCOUNT_COUNT) {
                return;
            }

            Log::warning('Test scenario accounts are incomplete; running the request fallback seeder.', [
                'existing_count' => $count,
            ]);

            app(TestScenarioAccountsSeeder::class)->run();
        } catch (\Throwable $exception) {
            // A fixture failure must never block real users from opening the
            // application. The next request will retry and the error is kept
            // in the deployment log for diagnosis.
            Log::error('Production test scenario fallback seeding failed.', [
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
