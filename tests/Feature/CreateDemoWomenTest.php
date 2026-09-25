<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class CreateDemoWomenTest extends AutomationTestCase
{
    public function test_demo_command_validates_creates_ten_per_active_area_and_is_repeatable(): void
    {
        Schema::table('users', function (Blueprint $t) {
            foreach (['email', 'password', 'middle_initial', 'gender', 'address', 'barangay'] as $column) $t->string($column)->nullable();
            $t->unsignedBigInteger('purok_id')->nullable();
        });
        Schema::create('barangays', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->boolean('is_active'); $t->timestamps();
        });
        Schema::create('puroks', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('barangay'); $t->timestamps();
        });
        DB::table('barangays')->insert([
            ['name' => 'Demo Area A', 'is_active' => true], ['name' => 'Demo Area B', 'is_active' => true],
            ['name' => 'Inactive', 'is_active' => false],
        ]);
        $originalStorage = $this->app->storagePath();
        $testStorage = sys_get_temp_dir().'/reprocare-demo-test-'.bin2hex(random_bytes(5));
        $this->app->useStoragePath($testStorage);
        try {
            $this->artisan('demo:create-women')->assertSuccessful();
            $this->assertSame(0, DB::table('users')->count());
            $this->artisan('demo:create-women', ['--apply' => true])->assertSuccessful();
            $this->assertSame(20, DB::table('users')->count());
            foreach (['Demo Area A', 'Demo Area B'] as $area) $this->assertSame(10, DB::table('users')->where('barangay', $area)->count());
            $this->assertSame(0, DB::table('users')->whereNotNull('contact_number')->count());
            $this->assertSame(20, DB::table('users')->where('status', 'approved')->where('sms_opt_out', true)->count());
            $csv = glob($testStorage.'/app/private/demo-accounts/*.csv')[0];
            $file = fopen($csv, 'r'); fgetcsv($file); $row = fgetcsv($file); fclose($file);
            $this->assertTrue(Hash::check($row[6], DB::table('users')->where('email', $row[5])->value('password')));
            $this->artisan('demo:create-women', ['--apply' => true])->assertSuccessful();
            $this->assertSame(20, DB::table('users')->count());
            $this->assertSame(0, DB::table('sms_logs')->count());
        } finally {
            $this->app->useStoragePath($originalStorage);
            foreach (glob($testStorage.'/app/private/demo-accounts/*.csv') ?: [] as $file) unlink($file);
            foreach (['/app/private/demo-accounts', '/app/private', '/app', ''] as $suffix) {
                if (is_dir($testStorage.$suffix)) rmdir($testStorage.$suffix);
            }
        }
    }
}
