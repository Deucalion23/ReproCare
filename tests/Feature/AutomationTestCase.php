<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

abstract class AutomationTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Legacy migrations contain MySQL-only consolidation SQL. Exercise the
        // current domain schema and the actual new migrations in isolated SQLite.
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        Carbon::setTestNow(Carbon::parse('2026-09-15 09:00:00', 'Asia/Manila'));
        Http::preventStrayRequests();
        config(['services.sms_provider' => 'textbee', 'services.movider.mock' => false,
            'services.textbee.api_key' => 'test-key', 'services.textbee.device_id' => 'test-device']);
        Http::fake(['*' => Http::response(['data' => ['success' => true, 'smsBatchId' => 'test-batch']], 200)]);

        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('first_name'); $t->string('last_name');
            $t->string('role')->default('user'); $t->string('status')->default('approved');
            $t->string('contact_number')->nullable(); $t->boolean('sms_opt_out')->default(false);
            $t->date('date_of_birth')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('notifications', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->string('title');
            $t->text('message'); $t->string('type')->default('info'); $t->string('action_url')->nullable();
            $t->boolean('is_read')->default(false); $t->timestamp('read_at')->nullable();
            $t->timestamps(); $t->softDeletes();
        });
        Schema::create('sms_logs', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id')->nullable(); $t->string('phone_number');
            $t->text('message'); $t->string('type'); $t->string('status');
            $t->text('error_message')->nullable(); $t->string('provider_sid')->nullable();
            $t->timestamp('sent_at')->nullable(); $t->timestamps();
        });
        Schema::create('cycles', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->date('period_start_date');
            $t->date('period_end_date')->nullable(); $t->integer('cycle_length')->nullable();
            $t->text('notes')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('health_records', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('recorded_by_id')->nullable();
            $t->string('risk_level')->default('Low'); $t->string('risk_assessment_mode')->default('automatic');
            $t->text('recommendations')->nullable(); $t->string('bp')->nullable();
            $t->timestamps(); $t->softDeletes();
        });
        Schema::create('checkups', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id')->nullable(); $t->unsignedBigInteger('walk_in_patient_id')->nullable();
            $t->unsignedBigInteger('midwife_id')->nullable(); $t->unsignedBigInteger('scheduled_by_id')->nullable();
            $t->date('scheduled_date'); $t->time('scheduled_time')->nullable();
            $t->string('status')->default('Scheduled'); $t->text('purpose')->nullable(); $t->text('notes')->nullable();
            $t->timestamps(); $t->softDeletes();
        });
        Schema::create('walk_in_patients', function (Blueprint $t) {
            $t->id(); $t->string('first_name'); $t->string('last_name'); $t->string('contact_number')->nullable();
            $t->timestamps(); $t->softDeletes();
        });
        Schema::create('pregnancies', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->date('edd')->nullable();
            $t->timestamp('ended_at')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('preventive_interventions', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->string('risk_level'); $t->text('trigger_reason');
            $t->text('recommendations'); $t->string('intervention_type'); $t->string('status');
            $t->timestamp('triggered_at'); $t->timestamps();
        });
        Schema::create('messages', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('sender_id'); $t->unsignedBigInteger('receiver_id');
            $t->string('subject')->nullable(); $t->text('body'); $t->unsignedBigInteger('reply_to_id')->nullable();
            $t->boolean('is_read')->default(false); $t->timestamp('read_at')->nullable();
            $t->timestamps(); $t->softDeletes();
        });
        (require database_path('migrations/2026_09_18_000001_add_alert_lifecycle_to_notifications.php'))->up();
        (require database_path('migrations/2026_09_18_000002_restore_scheduled_message_delivery_fields.php'))->up();
        (require database_path('migrations/2026_09_18_000003_allow_critical_risk_in_alert_records.php'))->up();
        (require database_path('migrations/2026_09_19_000001_add_client_uuid_to_messages.php'))->up();
        Schema::table('pregnancies', function (Blueprint $t) {
            $t->unsignedInteger('gravida')->nullable();
            $t->unsignedInteger('para')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        // Setting::$cache is process-static; flush it so one test's settings
        // table can never poison another test's fallback defaults.
        \App\Models\Setting::flushCache();
        parent::tearDown();
    }

    protected function patient(array $attributes = []): User
    {
        return User::create(array_merge(['first_name' => 'Test', 'last_name' => 'Patient',
            'role' => 'user', 'status' => 'approved', 'contact_number' => '09171234567'], $attributes));
    }
}
