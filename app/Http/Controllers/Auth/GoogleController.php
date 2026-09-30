<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Purok;
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

        // withTrashed: a previously deleted account still holds its unique
        // email at the DB level. Without this, the lookup misses while the
        // insert below keeps violating users_email_unique on every retry.
        $user = User::withTrashed()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user) {
            return $this->handleExistingGoogleUser($user, (string) $googleUser->getId());
        }

        // ── New patient via Google. RHU approval still applies (status
        // pending); onboarding (phone + barangay) comes first.
        [$firstName, $lastName] = $this->splitName((string) $googleUser->getName());

        try {
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
        } catch (\Illuminate\Database\QueryException $e) {
            // Race guard: the callback was hit twice at once (double-click on
            // Google's consent screen). The other request already inserted
            // this email → fall through to the normal existing-user flow.
            $user = User::withTrashed()->whereRaw('LOWER(email) = ?', [$email])->first();

            if (! $user) {
                report($e);

                return redirect()->route('login')->withErrors([
                    'email' => 'Google sign-in hit a temporary issue. Please try again.',
                ])->onlyInput('email');
            }

            return $this->handleExistingGoogleUser($user, (string) $googleUser->getId());
        }

        $user->markEmailAsVerified();

        return $this->logGoogleUserIn($user);
    }

    /**
     * Existing email: link google_id if missing, trust Google's email
     * verification, then run the shared login routing. Staff accounts are
     * always rejected — public social login is patients-only.
     */
    protected function handleExistingGoogleUser(User $user, string $googleId): RedirectResponse
    {
        if (($user->role ?? null) !== 'user') {
            return redirect()->route('login')->withErrors([
                'email' => 'This email belongs to a staff account. Please sign in with your email and password.',
            ])->onlyInput('email');
        }

        // Returning patient whose account was previously deleted: Google just
        // proved ownership of the email, so restore rather than crash on the
        // unique index that still covers the trashed row. Account status is
        // left untouched (RHU approval / blocks still apply below).
        if ($user->trashed()) {
            $user->restore();
            $user = $user->fresh() ?? $user;
        }

        if (empty($user->google_id)) {
            $user->forceFill(['google_id' => $googleId])->save();
        }

        // Google already verified ownership of this email address.
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $this->logGoogleUserIn($user);
    }

    /**
     * Log an OAuth user in and route them: blocked statuses stay blocked,
     * incomplete profiles go to onboarding, everyone else to the dashboard.
     */
    protected function logGoogleUserIn(User $user): RedirectResponse
    {
        $user = $user->fresh() ?? $user;

        // Rejected accounts never enter any portal and never hold a session:
        // re-queue as pending for RHU 1 re-verification and show the
        // rejection message on the login page.
        if (($user->status ?? null) === 'rejected') {
            $user->update(['status' => 'pending']);
            \App\Models\ActivityLog::log('update', "Rejected account re-queued for RHU re-verification on Google sign-in: {$user->name} ({$user->email})", $user);

            return redirect()->route('login')->withErrors([
                'email' => \App\Models\User::REJECTED_LOGIN_MESSAGE,
            ])->onlyInput('email');
        }

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
     * Show the mandatory profile completion form. This mirrors the regular
     * self-registration form step-by-step — the ONLY things a Google user
     * never fills in are email and password (Google supplies the identity).
     * Name fields are left blank so the patient types them manually.
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
     * Store the onboarding fields and lift the profile gate. Validation
     * mirrors AuthController::register minus email/password: same fill-ups,
     * same step-by-step order, no gender field, no address landmark, and the
     * ID step uses the same upload-or-camera base64 capture as registration
     * (legacy file uploads still accepted for backward compatibility).
     */
    public function updateCompleteProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->needsProfileCompletion()) {
            return redirect()->route('user.dashboard');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:10',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:tomorrow',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'house_number' => 'nullable|string|max:100',
            // Merged "Purok / Street / Sitio" single line (same as signup location step).
            'purok' => 'nullable|string|max:255',
            'sitio' => 'nullable|string|max:200',
            'barangay' => ['required', 'string', 'max:255', Rule::in(Barangay::allNames())],
            'purok_id' => 'nullable|exists:puroks,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            // Same capture as regular signup (upload file OR camera → base64).
            'id_image_data_front' => 'required_without:id_image_front|nullable|string|max:8388608',
            'id_image_data_back' => 'required_without:id_image_back|nullable|string|max:8388608',
            'id_image_front' => 'required_without:id_image_data_front|nullable|image|mimes:jpeg,png,jpg|max:5120',
            'id_image_back' => 'required_without:id_image_data_back|nullable|image|mimes:jpeg,png,jpg|max:5120',

            // Partner / Additional Contact
            'partner_name' => 'nullable|string|max:255',
            'partner_contact' => 'nullable|string|max:20',

            // Primary emergency contact
            'emergency_name_1' => 'required|string|max:255',
            'emergency_relationship_1' => 'required|string|max:255',
            'emergency_contact_number_1' => 'required|string|max:255',
            'emergency_address_1' => 'nullable|string|max:500',

            // Secondary emergency contact
            'emergency_name_2' => 'nullable|string|max:255',
            'emergency_relationship_2' => 'nullable|required_with:emergency_name_2|string|max:255',
            'emergency_contact_number_2' => 'nullable|required_with:emergency_name_2|string|max:255',
            'emergency_address_2' => 'nullable|string|max:500',

            // Tertiary emergency contact
            'emergency_name_3' => 'nullable|string|max:255',
            'emergency_relationship_3' => 'nullable|required_with:emergency_name_3|string|max:255',
            'emergency_contact_number_3' => 'nullable|required_with:emergency_name_3|string|max:255',
            'emergency_address_3' => 'nullable|string|max:500',
        ]);

        $barangay = $validated['barangay'];

        // Require an ID photo per side — either the signup-style base64 capture
        // (upload or camera) or a legacy file upload.
        $hasFront = $request->filled('id_image_data_front') || $request->hasFile('id_image_front');
        $hasBack = $request->filled('id_image_data_back') || $request->hasFile('id_image_back');

        if (! $hasFront || ! $hasBack) {
            return back()->withErrors(array_filter([
                ...(!$hasFront ? ['id_image_data_front' => 'Front of ID is required — upload a file or capture with your camera.'] : []),
                ...(!$hasBack ? ['id_image_data_back' => 'Back of ID is required — upload a file or capture with your camera.'] : []),
            ]))->withInput();
        }

        // Compose full address from components if address field is empty.
        // "purok" holds the merged "Purok / Street / Sitio" line; a legacy
        // "sitio" value (older payloads) is appended when different.
        $purokLine = trim((string) $request->input('purok', ''));
        $sitioLegacy = trim((string) $request->input('sitio', ''));
        if ($sitioLegacy !== '' && stripos($purokLine, $sitioLegacy) === false) {
            $purokLine = trim($purokLine !== '' ? $purokLine.' / '.$sitioLegacy : $sitioLegacy);
        }
        $addressParts = array_filter([
            $request->filled('house_number') ? 'House/Unit ' . $request->house_number : null,
            $purokLine !== '' ? $purokLine : null,
            $barangay,
            'San Carlos City, Pangasinan'
        ]);
        $resolvedAddress = !empty($validated['address']) ? $validated['address'] : (!empty($addressParts) ? implode(', ', $addressParts) : null);

        $resolvedPurokId = $validated['purok_id']
            ?? Purok::resolveIdFromText($purokLine !== '' ? $purokLine : $request->input('purok'), $barangay);

        $idFrontPath = $request->hasFile('id_image_front')
            ? $request->file('id_image_front')->store('uploads/ids', 'public')
            : $this->storeIdDataUri((string) $request->input('id_image_data_front'), 'front');
        $idBackPath = $request->hasFile('id_image_back')
            ? $request->file('id_image_back')->store('uploads/ids', 'public')
            : $this->storeIdDataUri((string) $request->input('id_image_data_back'), 'back');

        // Database copies so the scans survive ephemeral disks (deploys).
        $idFrontData = $request->filled('id_image_data_front')
            ? (string) $request->input('id_image_data_front')
            : self::fileToDataUri($request->file('id_image_front'));
        $idBackData = $request->filled('id_image_data_back')
            ? (string) $request->input('id_image_data_back')
            : self::fileToDataUri($request->file('id_image_back'));

        $user->forceFill([
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => null,
            // phone_number (spec) maps to the existing contact_number column.
            'contact_number' => $validated['contact_number'] ?? null,
            'address' => $resolvedAddress,
            'barangay' => $barangay,
            'purok_id' => $resolvedPurokId,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'address_label' => null,
            'id_image_front' => $idFrontPath,
            'id_image_back' => $idBackPath,
            'id_image_front_data' => $idFrontData,
            'id_image_back_data' => $idBackData,
            'partner_name' => $validated['partner_name'] ?? null,
            'partner_contact' => $validated['partner_contact'] ?? null,
            'is_profile_complete' => true,
        ])->save();

        // Primary emergency contact.
        $user->emergencyContacts()->create([
            'name' => $validated['emergency_name_1'],
            'relationship' => $validated['emergency_relationship_1'],
            'contact_number' => $validated['emergency_contact_number_1'],
            'address' => $validated['emergency_address_1'] ?? null,
            'contact_order' => 1,
            'is_primary' => true,
        ]);

        // Secondary emergency contact (if provided).
        if (!empty($validated['emergency_name_2'])) {
            $user->emergencyContacts()->create([
                'name' => $validated['emergency_name_2'],
                'relationship' => $validated['emergency_relationship_2'],
                'contact_number' => $validated['emergency_contact_number_2'],
                'address' => $validated['emergency_address_2'] ?? null,
                'contact_order' => 2,
                'is_primary' => false,
            ]);
        }

        // Tertiary emergency contact (if provided).
        if (!empty($validated['emergency_name_3'])) {
            $user->emergencyContacts()->create([
                'name' => $validated['emergency_name_3'],
                'relationship' => $validated['emergency_relationship_3'],
                'contact_number' => $validated['emergency_contact_number_3'],
                'address' => $validated['emergency_address_3'] ?? null,
                'contact_order' => 3,
                'is_primary' => false,
            ]);
        }

        // Rejected accounts are re-queued (never onboarded into a portal).
        if (($user->status ?? null) === 'rejected') {
            $user->update(['status' => 'pending']);
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'email' => \App\Models\User::REJECTED_LOGIN_MESSAGE,
            ])->onlyInput('email');
        }

        // Still awaiting RHU approval → back to login with the pending notice.
        if (($user->status ?? 'approved') === 'pending') {
            Auth::logout();

            return redirect()->route('login')->with('pending_registration', true);
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Profile completed. Welcome to ReproCare!');
    }

    /**
     * Build a data URL from an uploaded file (database copy of the scan).
     */
    protected static function fileToDataUri($file): ?string
    {
        try {
            if (! $file || ! method_exists($file, 'getRealPath') || ! is_file($file->getRealPath())) {
                return null;
            }
            $mime = method_exists($file, 'getMimeType') ? ($file->getMimeType() ?: 'image/jpeg') : 'image/jpeg';
            if (! str_starts_with($mime, 'image/')) {
                return null;
            }
            $raw = @file_get_contents($file->getRealPath());
            if ($raw === false) {
                return null;
            }

            return 'data:' . $mime . ';base64,' . base64_encode($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Persist a signup-style base64 ID capture (upload or camera) to disk.
     */
    protected function storeIdDataUri(string $dataUri, string $side): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $dataUri, $matches)) {
            $dataUri = substr($dataUri, strpos($dataUri, ',') + 1);
            $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        } else {
            $extension = 'jpg';
        }

        $fileName = 'id_' . $side . '_' . time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $extension;
        $path = 'uploads/ids/' . $fileName;
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, base64_decode($dataUri));

        return $path;
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
