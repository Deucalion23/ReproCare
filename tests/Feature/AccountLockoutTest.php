<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AccountLockoutTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'email')) $t->string('email')->nullable()->unique();
            if (!Schema::hasColumn('users', 'password')) $t->string('password')->nullable();
            if (!Schema::hasColumn('users', 'failed_login_attempts')) $t->integer('failed_login_attempts')->default(0);
        });

        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('user_role')->nullable();
            $t->string('user_name')->nullable();
            $t->string('action')->nullable();
            $t->string('model_type')->nullable();
            $t->unsignedBigInteger('model_id')->nullable();
            $t->text('description')->nullable();
            $t->boolean('is_protected')->default(false);
            $t->string('ip_address')->nullable();
            $t->text('user_agent')->nullable();
            $t->timestamps();
        });
    }

    private function account(string $role): User
    {
        return User::create([
            'first_name' => ucfirst($role),
            'last_name' => 'Staff',
            'email' => $role . '@lockout.test',
            'password' => Hash::make('correct-password'),
            'role' => $role,
            'status' => 'approved',
        ]);
    }

    private function failLogin(string $email, string $password = 'wrong-password')
    {
        return $this->post('/auth/login', ['email' => $email, 'password' => $password]);
    }

    public function test_each_covered_role_locks_after_three_failures(): void
    {
        foreach (['user', 'bhw', 'bhw_president', 'midwife', 'rhu'] as $role) {
            $user = $this->account($role);

            $this->failLogin($user->email);
            $this->failLogin($user->email);
            $response = $this->failLogin($user->email);

            $response->assertRedirect();
            $response->assertSessionHas('account_locked', AuthController::LOCKOUT_MESSAGE);
            $this->assertSame('inactive', $user->fresh()->status);
        }
    }

    public function test_cho_never_locks(): void
    {
        $cho = $this->account('cho');

        for ($i = 0; $i < 3; $i++) {
            $this->failLogin($cho->email);
        }

        $this->assertSame('approved', $cho->fresh()->status);
        $this->assertEmpty($cho->fresh()->failed_login_attempts);
    }

    public function test_locked_owner_sees_modal_again_on_retry(): void
    {
        $user = $this->account('user');

        $this->failLogin($user->email);
        $this->failLogin($user->email);
        $this->failLogin($user->email);
        $this->assertSame('inactive', $user->fresh()->status);

        // Wrong password again → same modal message, not a generic mismatch.
        $retry = $this->failLogin($user->email);
        $retry->assertSessionHas('account_locked', AuthController::LOCKOUT_MESSAGE);

        // Right password while still locked → same modal message.
        $correct = $this->post('/auth/login', ['email' => $user->email, 'password' => 'correct-password']);
        $correct->assertSessionHas('account_locked', AuthController::LOCKOUT_MESSAGE);
        $this->assertGuest();
    }

    public function test_rhu_reactivates_locked_midwife_and_president(): void
    {
        $rhu = $this->account('rhu');
        $midwife = $this->account('midwife');
        $president = $this->account('bhw_president');
        $midwife->update(['status' => 'inactive']);
        $president->update(['status' => 'inactive']);

        $this->actingAs($rhu)->post(route('rhu.midwives.activate', $midwife->id))
            ->assertRedirect();
        $this->actingAs($rhu)->post(route('rhu.bhw-presidents.activate', $president->id))
            ->assertRedirect();

        $this->assertSame('approved', $midwife->fresh()->status);
        $this->assertSame('approved', $president->fresh()->status);
        $this->assertSame(0, (int) $midwife->fresh()->failed_login_attempts);

        // Reactivated account can log in again.
        $this->post('/auth/login', ['email' => $midwife->email, 'password' => 'correct-password'])
            ->assertRedirect(route('midwife.dashboard'));
    }

    public function test_cho_reactivates_locked_rhu(): void
    {
        $cho = $this->account('cho');
        $rhu = $this->account('rhu');
        $rhu->update(['status' => 'inactive']);

        $this->actingAs($cho)->post(route('cho.users.activate', $rhu->id))
            ->assertRedirect();

        $this->assertSame('approved', $rhu->fresh()->status);
    }
}
