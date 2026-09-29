<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

/**
 * "Continue with Google" (Laravel Socialite) + mandatory profile completion.
 *
 * SECURITY: social login is strictly limited to patient accounts ('user').
 * Staff / clinical roles (cho, rhu, midwife, bhw, bhw_president) are NEVER
 * created, linked, or signed in through this controller.
 */
class GoogleController extends Controller
{
    /**
     * Redirect the patient to Google's OAuth consent screen.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the Google OAuth callback.
     *
     * - Existing email + patient role → link google_id if missing,
     *   mark email verified (Google proved ownership), log in, then send
     *   incomplete profiles to onboarding, everyone else to the dashboard.
     * - Existing email + staff role → reject (no link, no login).
     * - Unknown email → create a patient ('user') record with
     *   is_profile_complete = false, log in, send to onboarding.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in failed. Please try again.',
            ]);
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));

        if ($email === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Google did not share an email address. Please register with email and password instead.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            // ── Staff accounts must never enter through public social login.
            if (($user->role ?? null) !== 'user') {
                return redirect()->route('login')->withErrors([
                    'email' => 'This email belongs to a staff account. Please sign in with your email and password.',
                ])->onlyInput('email');
            }

            if (empty($user->google_id)) {
                $user->forceFill(['google_id' => $googleUser->getId()])->save();
            }

            // Google already verified ownership of this email address.
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            return $this->logGoogleUserIn($user);
        }

        // ── New patient via Google. RHU approval still applies (status
        // pending); onboarding (phone + barangay) comes first.
        [$firstName, $lastName] = $this->splitName((string) $googleUser->getName());

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'google_id' => $googleUser->getId(),
            // users.password is NOT NULL → random unusable password; this
            // account authenticates via Google, never via password.
            'password' => Str::random(40),
            'role' => 'user',
            'status' => 'pending',
            'is_profile_complete' => false,
        ]);

        $user->markEmailAsVerified();

        return $this->logGoogleUserIn($user);
    }

    /**
     * Log an OAuth user in and route them: blocked statuses stay blocked,
     * incomplete profiles go to onboarding, everyone else to the dashboard.
     */
    protected function logGoogleUserIn(User $user): RedirectResponse
    {
        $user = $user->fresh() ?? $user;

        // Suspended / deactivated accounts can never hold a session.
        if (in_array($user->status ?? 'approved', ['suspended', 'inactive'], true)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your account is no longer active. Please contact the City Health Office.',
            ])->onlyInput('email');
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        // Pending RHU approval: onboarding first, then wait for approval.
        if (($user->status ?? 'approved') === 'pending') {
            if ($user->needsProfileCompletion()) {
                return redirect()->route('profile.complete');
            }

            Auth::logout();

            return redirect()->route('login')->with('pending_registration', true);
        }

        if ($user->needsProfileCompletion()) {
            return redirect()->route('profile.complete');
        }

        return redirect()->route('user.dashboard');
    }

    /**
     * Show the mandatory profile completion form (phone + barangay).
     */
    public function showCompleteProfile(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->needsProfileCompletion()) {
            return redirect()->route('user.dashboard');
        }

        $barangays = Barangay::allNames();

        return view('auth.complete-profile', compact('user', 'barangays'));
    }

    /**
     * Store the onboarding fields and lift the profile gate.
     */
    public function updateCompleteProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->needsProfileCompletion()) {
            return redirect()->route('user.dashboard');
        }

        $validated = $request->validate([
            'contact_number' => ['required', 'string', 'max:20'],
            'barangay' => ['required', 'string', 'max:255', Rule::in(Barangay::allNames())],
        ]);

        $user->forceFill([
            // phone_number (spec) maps to the existing contact_number column.
            'contact_number' => $validated['contact_number'],
            'barangay' => $validated['barangay'],
            'is_profile_complete' => true,
        ])->save();

        // Still awaiting RHU approval → back to login with the pending notice.
        if (($user->status ?? 'approved') === 'pending') {
            Auth::logout();

            return redirect()->route('login')->with('pending_registration', true);
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Profile completed. Welcome to ReproCare!');
    }

    /**
     * Split a Google display name ("Maria Santos Reyes") into
     * first_name ("Maria") + last_name ("Santos Reyes").
     *
     * @return array{0: string, 1: string}
     */
    protected function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) === 0) {
            return ['Google', 'User'];
        }

        if (count($parts) === 1) {
            return [$parts[0], 'User'];
        }

        return [$parts[0], implode(' ', array_slice($parts, 1))];
    }
}
