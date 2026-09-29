<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class GoogleOAuthTest extends AutomationTestCase
{
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
        $this->assertStringContainsString(
            'accounts.google.com',
            $response->headers->get('Location')
        );
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

    public function test_profile_completion_stores_phone_and_barangay(): void
    {
        $patient = $this->patient([
            'is_profile_complete' => false,
            'contact_number' => null,
            'barangay' => null,
        ]);

        $response = $this->actingAs($patient)->post(route('profile.complete.store'), [
            'contact_number' => '09179998888',
            'barangay' => 'Burgos St',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $fresh = $patient->fresh();
        $this->assertTrue((bool) $fresh->is_profile_complete);
        $this->assertSame('09179998888', $fresh->contact_number);
        $this->assertSame('Burgos St', $fresh->barangay);
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
