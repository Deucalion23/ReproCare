<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class GoogleOAuthTest extends AutomationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('emergency_contacts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('name')->nullable();
            $t->string('relationship')->nullable();
            $t->string('contact_number')->nullable();
            $t->string('address')->nullable();
            $t->integer('contact_order')->default(1);
            $t->boolean('is_primary')->default(false);
            $t->timestamps();
            $t->softDeletes();
        });

        if (! Schema::hasTable('puroks')) {
            Schema::create('puroks', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('barangay');
                $t->timestamps();
            });
        }
    }

    /**
     * Minimal 1px PNG payload. UploadedFile::fake()->image() needs the GD
     * extension (absent here), so real image bytes are used instead — they
     * also pass the controller's `image` MIME validation via fileinfo.
     */
    protected function fakeIdImage(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'idimg') . '.png';
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    protected function completionPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Maria',
            'middle_initial' => 'S',
            'last_name' => 'Santos',
            'date_of_birth' => '2000-05-10',
            'contact_number' => '09179998888',
            'house_number' => '123',
            'purok' => 'Purok 1 / Rizal St. / Sitio Centro',
            'barangay' => 'Burgos St',
            'id_image_front' => $this->fakeIdImage('id-front.png'),
            'id_image_back' => $this->fakeIdImage('id-back.png'),
            'partner_name' => 'Jose Santos',
            'partner_contact' => '09171112222',
            'emergency_name_1' => 'Ana Reyes',
            'emergency_relationship_1' => 'Mother',
            'emergency_contact_number_1' => '09173334444',
            'emergency_address_1' => 'Burgos St',
        ], $overrides);
    }

    protected function mockGoogleUser(string $id, string $email, string $name): void
    {
        $socialiteUser = \Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn($id);
        $socialiteUser->shouldReceive('getEmail')->andReturn($email);
        $socialiteUser->shouldReceive('getName')->andReturn($name);

        $provider = \Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_redirect_goes_to_google(): void
    {
        $response = $this->get(route('google.redirect'));

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertStringContainsString(
            'accounts.google.com',
            $location
        );
        // Forces Google's account chooser on every click so patients can
        // switch accounts (or back out) instead of silently reusing the
        // last Google session after sign-out.
        $this->assertStringContainsString('prompt=select_account', $location);
    }

    public function test_callback_creates_patient_and_sends_to_profile_completion(): void
    {
        $this->mockGoogleUser('google-123', 'newpatient@example.com', 'Maria Santos');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('profile.complete'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'newpatient@example.com',
            'google_id' => 'google-123',
            'role' => 'user',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);

        $user = User::where('email', 'newpatient@example.com')->first();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertFalse((bool) $user->is_profile_complete);
    }

    public function test_callback_links_existing_patient_and_completes_login(): void
    {
        $existing = $this->patient(['email' => 'returning@example.com']);
        $this->mockGoogleUser('google-456', 'returning@example.com', 'Returning Patient');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticatedAs($existing->fresh());
        $this->assertSame('google-456', $existing->fresh()->google_id);
    }

    public function test_callback_matches_existing_email_case_insensitively(): void
    {
        $existing = $this->patient();
        // Simulate an account originally registered with different casing.
        $existing->forceFill(['email' => 'MixedCase-Patient@example.com'])->save();
        $this->mockGoogleUser('google-999', 'mixedcase-patient@example.com', 'Mixed Case');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticatedAs($existing->fresh());
        $this->assertSame(1, User::whereRaw('LOWER(email) = ?', ['mixedcase-patient@example.com'])->count());
    }

    public function test_callback_restores_soft_deleted_patient(): void
    {
        $trashed = $this->patient(['email' => 'restored-patient@example.com', 'is_profile_complete' => false]);
        $trashed->delete();
        $this->assertSoftDeleted('users', ['id' => $trashed->id]);
        $this->mockGoogleUser('google-restore-1', 'restored-patient@example.com', 'Restored Patient');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('profile.complete'));
        $this->assertAuthenticated();
        $restored = User::where('email', 'restored-patient@example.com')->first();
        $this->assertNotNull($restored);
        $this->assertNull($restored->deleted_at);
        $this->assertSame('google-restore-1', $restored->google_id);
    }

    public function test_callback_rejects_soft_deleted_staff(): void
    {
        $staff = $this->patient(['role' => 'midwife', 'email' => 'deleted-staff@example.com']);
        $staff->delete();
        $this->mockGoogleUser('google-restore-2', 'deleted-staff@example.com', 'Deleted Staff');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSoftDeleted('users', ['id' => $staff->id]);
    }

    public function test_rejected_patient_is_bounced_to_login_and_requeued(): void
    {
        $rejected = $this->patient(['email' => 'rejected-patient@example.com', 'status' => 'rejected']);
        $this->mockGoogleUser('google-rej-1', 'rejected-patient@example.com', 'Rejected Patient');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame('pending', $rejected->fresh()->status);
        $this->assertStringContainsString(
            'Your account has been rejected, please fill up the correct information needed.',
            session('errors')->get('email')[0]
        );
    }

    public function test_rejected_trashed_patient_is_restored_and_requeued(): void
    {
        $trashed = $this->patient(['email' => 'rejected-trashed@example.com', 'status' => 'rejected']);
        $trashed->delete();
        $this->mockGoogleUser('google-rej-2', 'rejected-trashed@example.com', 'Rejected Trashed');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $restored = User::where('email', 'rejected-trashed@example.com')->first();
        $this->assertNotNull($restored);
        $this->assertNull($restored->deleted_at);
        $this->assertSame('pending', $restored->status);
    }

    public function test_live_rejected_session_cannot_reach_dashboard(): void
    {
        // Reproduces the production reload loop: a rejected account holding a
        // live session must be logged out with the rejection message instead
        // of bouncing between the dashboard and the login page forever.
        $rejected = $this->patient(['status' => 'rejected']);

        $response = $this->actingAs($rejected)->get(route('user.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame('pending', $rejected->fresh()->status);
    }

    public function test_pending_incomplete_session_keeps_onboarding(): void
    {
        $patient = $this->patient(['status' => 'pending', 'is_profile_complete' => false]);

        $response = $this->actingAs($patient)->get(route('forum.index'));

        $response->assertRedirect(route('profile.complete'));
        $this->assertAuthenticatedAs($patient->fresh());
    }

    public function test_password_login_rejected_shows_message_and_requeues(): void
    {
        $rejected = $this->patient(['status' => 'rejected']);

        $response = $this->post(route('login.post'), [
            'email' => $rejected->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame('pending', $rejected->fresh()->status);
    }

    public function test_callback_rejects_staff_emails(): void
    {
        $this->patient(['role' => 'midwife', 'email' => 'midwife-staff@example.com']);
        $this->mockGoogleUser('google-789', 'midwife-staff@example.com', 'Staff Midwife');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['google_id' => 'google-789']);
    }

    public function test_incomplete_patient_is_forced_to_complete_profile(): void
    {
        $patient = $this->patient(['is_profile_complete' => false]);

        $response = $this->actingAs($patient)->get(route('user.dashboard'));

        $response->assertRedirect(route('profile.complete'));
    }

    public function test_complete_patient_reaches_dashboard(): void
    {
        $patient = $this->patient();

        $response = $this->actingAs($patient)->get(route('dashboard'));

        $response->assertRedirect(route('user.dashboard'));
    }

    public function test_profile_completion_stores_full_registration_details(): void
    {
        Storage::fake('public');
        $patient = $this->patient([
            'is_profile_complete' => false,
            'contact_number' => null,
            'barangay' => null,
        ]);

        $response = $this->actingAs($patient)->post(
            route('profile.complete.store'),
            $this->completionPayload()
        );

        $response->assertRedirect(route('user.dashboard'));
        $fresh = $patient->fresh();
        $this->assertTrue((bool) $fresh->is_profile_complete);
        // Mirrors regular registration: names, contact, address, barangay.
        $this->assertSame('Maria', $fresh->first_name);
        $this->assertSame('S', $fresh->middle_initial);
        $this->assertSame('Santos', $fresh->last_name);
        $this->assertNull($fresh->gender);
        $this->assertNull($fresh->address_label);
        $this->assertSame('09179998888', $fresh->contact_number);
        $this->assertSame('Burgos St', $fresh->barangay);
        $this->assertStringContainsString('Burgos St', (string) $fresh->address);
        $this->assertStringContainsString('San Carlos City', (string) $fresh->address);
        $this->assertSame('Jose Santos', $fresh->partner_name);
        // Valid ID scans stored for RHU verification.
        Storage::disk('public')->assertExists($fresh->id_image_front);
        Storage::disk('public')->assertExists($fresh->id_image_back);
        // Database copies persist even when ephemeral disks are wiped.
        $this->assertStringStartsWith('data:image', (string) $fresh->id_image_front_data);
        $this->assertStringStartsWith('data:image', (string) $fresh->id_image_back_data);
        // Accessors serve the DB copy (file-independent).
        $this->assertStringStartsWith('data:image', (string) $fresh->id_image_front_url);
        $this->assertStringStartsWith('data:image', (string) $fresh->id_image_back_url);
        // Primary emergency contact recorded.
        $this->assertDatabaseHas('emergency_contacts', [
            'user_id' => $patient->id,
            'name' => 'Ana Reyes',
            'contact_order' => 1,
            'is_primary' => true,
        ]);
    }

    public function test_profile_completion_rejects_incomplete_payload(): void
    {
        $patient = $this->patient(['is_profile_complete' => false]);

        $response = $this->actingAs($patient)->post(route('profile.complete.store'), [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);

        $response->assertSessionHasErrors([
            'date_of_birth', 'barangay',
            'id_image_data_front', 'id_image_data_back',
            'emergency_name_1', 'emergency_relationship_1', 'emergency_contact_number_1',
        ]);
        $this->assertFalse((bool) $patient->fresh()->is_profile_complete);
        $this->assertDatabaseCount('emergency_contacts', 0);
    }

    public function test_middleware_passes_staff_through(): void
    {
        $staff = $this->patient(['role' => 'midwife', 'is_profile_complete' => false]);

        $request = Request::create('/user/dashboard', 'GET');
        $request->setUserResolver(fn () => $staff);

        $response = (new EnsureProfileComplete)->handle($request, fn () => response('next'));

        $this->assertSame('next', $response->getContent());
    }

    public function test_registration_fires_verification_notification(): void
    {
        Notification::fake();

        $user = $this->patient(['email_verified_at' => null]);

        event(new Registered($user));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_patient_sees_verification_notice(): void
    {
        $patient = $this->patient(['email_verified_at' => null]);

        $response = $this->actingAs($patient)->get(route('verification.notice'));

        $response->assertOk()->assertSee('Check your email');
    }

    public function test_signed_link_verifies_email(): void
    {
        $patient = $this->patient(['email_verified_at' => null]);

        $url = URL::signedRoute('verification.verify', [
            'id' => $patient->id,
            'hash' => sha1($patient->email),
        ]);

        $response = $this->actingAs($patient)->get($url);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue($patient->fresh()->hasVerifiedEmail());
    }

    public function test_demo_account_skips_verification_and_onboarding(): void
    {
        // Demo accounts (User::DEMO_EMAILS) verify instantly and log
        // straight into the dashboard — no verification-notice detour, no
        // Get-started onboarding — while status gates still apply.
        $demo = $this->patient([
            'email' => User::DEMO_EMAILS[0],
            'email_verified_at' => null,
            'is_profile_complete' => false,
        ]);

        $response = $this->post(route('login.post'), [
            'email' => $demo->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticatedAs($demo->fresh());
        $this->assertTrue($demo->fresh()->hasVerifiedEmail());
        $this->assertTrue((bool) $demo->fresh()->is_profile_complete);
    }

    public function test_password_login_gates_unverified_then_incomplete_patients(): void
    {
        $unverified = $this->patient(['email_verified_at' => null]);

        $this->post(route('login.post'), [
            'email' => $unverified->email,
            'password' => 'password',
        ])->assertRedirect(route('verification.notice'));

        $this->post(route('logout'));

        $incomplete = $this->patient(['is_profile_complete' => false]);

        $this->post(route('login.post'), [
            'email' => $incomplete->email,
            'password' => 'password',
        ])->assertRedirect(route('profile.complete'));
    }
}
