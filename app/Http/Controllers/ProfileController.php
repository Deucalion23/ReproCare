<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Purok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    private const PROFILE_IMAGE_DIRECTORIES = [
        'uploads/profile/',
        'profile/',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get current authenticated user
     */
    private function getCurrentUser()
    {
        return auth()->user();
    }

    /**
     * Get current user type for view routing
     */
    private function getUserType()
    {
        $user = auth()->user();
        return match($user->role) {
            'midwife' => 'midwife',
            'bhw' => 'bhw',
            'bhw_president' => 'bhw-president',
            'cho' => 'cho',
            'rhu' => 'rhu',
            'user' => 'user',
            default => null,
        };
    }

    /**
     * Settings route name for the current user's role.
     * Profile is managed inside Settings (My Profile section) — no standalone profile page.
     */
    private function settingsRouteName(): string
    {
        return match(auth()->user()?->role) {
            'cho' => 'cho.settings',
            'rhu' => 'rhu.settings',
            'midwife' => 'midwife.settings',
            'bhw' => 'bhw.settings',
            'bhw_president' => 'bhw-president.settings',
            default => 'user.settings',
        };
    }

    /**
     * Show user's profile — retired: profile lives in Settings, redirect there.
     */
    public function show()
    {
        $user = $this->getCurrentUser();
        if (!$user) return redirect()->route('login');

        return redirect()->route($this->settingsRouteName());
    }

    /**
     * Show profile edit form — retired: profile lives in Settings, redirect there.
     */
    public function edit()
    {
        $user = $this->getCurrentUser();
        if (!$user) return redirect()->route('login');

        // Patient portal still edits profile via its own view; staff go to Settings.
        if (($this->getUserType()) === 'user') {
            $puroks = Purok::orderBy('name')->get();
            return view('user.profile.edit', compact('user', 'puroks'));
        }

        return redirect()->route($this->settingsRouteName());
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) return redirect()->route('login');

        $userType = get_class($user);

        // Determine table for unique email validation
        $emailTable = match($userType) {
            Midwife::class => 'midwives',
            Bhw::class => 'bhws',
            User::class => 'users',
            default => 'users',
        };

        $isAddressUpdate = $request->input('_section') === 'address' || (!$request->has('first_name') && ($request->has('barangay') || $request->has('house_number') || $request->has('purok') || $request->has('sitio')));
        $isHealthUpdate  = $request->input('_section') === 'health' || (!$request->has('first_name') && ($request->has('blood_type') || $request->has('height') || $request->has('weight') || $request->has('medical_history') || $request->has('medical_notes')));
        $isPasswordUpdate = $request->has('password') && !$request->has('first_name');

        $validationRules = [
            'first_name' => ($isAddressUpdate || $isHealthUpdate || $isPasswordUpdate) ? 'nullable|string|max:255' : 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:2',
            'last_name' => ($isAddressUpdate || $isHealthUpdate || $isPasswordUpdate) ? 'nullable|string|max:255' : 'required|string|max:255',
            'email' => ($isAddressUpdate || $isHealthUpdate || $isPasswordUpdate)
                ? 'nullable|string|email|max:255'
                : 'required|string|email|max:255|unique:' . $emailTable . ',email,' . $user->id,
            'contact_number' => 'nullable|string|max:20',
            'barangay' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:100',
            'purok' => 'nullable|string|max:100',
            'sitio' => 'nullable|string|max:200',
            'address' => 'nullable|string|max:500',
            'purok_id' => 'nullable',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|string|max:20',
            'partner_name' => 'nullable|string|max:255',
            'partner_contact' => 'nullable|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // Max 5MB
            'medical_history' => 'nullable|string|max:2000',
            'medical_notes' => 'nullable|string|max:2000',
        ];

        $request->validate($validationRules);

        // Manually validate password only if it's being changed
        $passwordValue = $request->input('password');
        if ($passwordValue !== null && $passwordValue !== '' && trim($passwordValue) !== '') {
            if (strlen($passwordValue) < 8) {
                return back()->withErrors(['password' => 'Password must be at least 8 characters.'])->withInput();
            }
            if ($passwordValue !== $request->input('password_confirmation')) {
                return back()->withErrors(['password' => 'Password confirmation does not match.'])->withInput();
            }
        }

        $updateData = [];

        if ($request->filled('first_name')) {
            $updateData['first_name'] = $request->first_name;
        }
        if ($request->has('middle_initial')) {
            $updateData['middle_initial'] = $request->middle_initial;
        }
        if ($request->filled('last_name')) {
            $updateData['last_name'] = $request->last_name;
        }
        if ($request->filled('email')) {
            $updateData['email'] = $request->email;
        }
        if ($request->has('contact_number')) {
            $updateData['contact_number'] = $request->filled('contact_number') ? $request->contact_number : null;
        }
        if ($request->has('date_of_birth')) {
            $updateData['date_of_birth'] = $request->filled('date_of_birth') ? $request->date_of_birth : null;
        }
        if ($request->has('gender')) {
            $updateData['gender'] = $request->gender ?? $user->gender;
        }

        if ($request->filled('barangay')) {
            $updateData['barangay'] = $request->barangay;
        } elseif ($request->filled('purok_id')) {
            $selectedPurok = Purok::find($request->purok_id);
            if ($selectedPurok) {
                $updateData['purok_id'] = $selectedPurok->id;
                $updateData['barangay'] = $selectedPurok->barangay;
            }
        }

        // Free-text Sitio / Street / Purok entries join the registry automatically.
        if ($request->filled('purok') && empty($updateData['purok_id'])) {
            $resolvedId = \App\Models\Purok::resolveIdFromText(
                $request->purok,
                $updateData['barangay'] ?? $user->barangay
            );
            if ($resolvedId) {
                $updateData['purok_id'] = $resolvedId;
            }
        }

        // Compose full address if parts provided or accept address directly
        if ($request->filled('address')) {
            $updateData['address'] = $request->address;
        } elseif ($request->filled('house_number') || $request->filled('purok') || $request->filled('sitio') || $request->filled('barangay')) {
            $targetBarangay = $request->filled('barangay') ? $request->barangay : $user->barangay;
            $addressParts = array_filter([
                $request->filled('house_number') ? 'House/Unit ' . $request->house_number : null,
                $request->filled('purok') ? (str_starts_with(strtolower($request->purok), 'purok') ? $request->purok : 'Purok ' . $request->purok) : null,
                $request->filled('sitio') ? $request->sitio : null,
                $targetBarangay,
                'San Carlos City, Pangasinan'
            ]);
            $updateData['address'] = implode(', ', $addressParts);
        }

        if ($request->has('medical_history') || $request->has('medical_notes')) {
            $updateData['medical_history'] = $request->input('medical_history', $request->input('medical_notes', $user->medical_history));
        }

        if ($request->has('partner_name')) {
            $updateData['partner_name'] = $request->filled('partner_name') ? $request->partner_name : null;
        }

        if ($request->has('partner_contact')) {
            $updateData['partner_contact'] = $request->filled('partner_contact') ? $request->partner_contact : null;
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            $this->deleteProfileImage($user->profile_image);

            // Upload new image with unique filename
            $image = $request->file('profile_image');
            $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('uploads/profile', $filename, 'public');
            $updateData['profile_image'] = $path;
        }

        // Handle password update if provided — requires current password
        // verification plus minimum-length confirmation, like staff flows.
        if ($request->filled('password')) {
            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
            $updateData['password'] = Hash::make($request->password);
        }

        // Reminder preferences (patient Notifications card).
        if ($request->has('pref_checkup_reminders')) {
            $updateData['pref_checkup_reminders'] = $request->boolean('pref_checkup_reminders');
        }

        $user->update($updateData);

        // Redirect to previous page or role settings page (profile lives in Settings)
        $redirectTo = $request->input('redirect_to', url()->previous());

        // If redirect_to is a retired profile page, go to Settings instead
        if (in_array($redirectTo, [route('profile.edit'), route('profile.show'), url()->current()], true)) {
            $redirectTo = route($this->settingsRouteName());
        }

        return redirect($redirectTo)->with('success', 'Profile updated successfully.');
    }

    /**
     * Remove profile image
     */
    public function removeImage()
    {
        $user = $this->getCurrentUser();
        if (!$user) return redirect()->route('login');

        if ($this->deleteProfileImage($user->profile_image)) {
            $user->update(['profile_image' => null]);
        }

        // Redirect to previous page or role settings page (profile lives in Settings)
        $redirectTo = request()->input('redirect_to', url()->previous());

        // If redirect_to is a retired profile page, go to Settings instead
        if (in_array($redirectTo, [route('profile.edit'), route('profile.show'), url()->current()], true)) {
            $redirectTo = route($this->settingsRouteName());
        }

        return redirect($redirectTo)->with('success', 'Profile image removed successfully.');
    }

    private function deleteProfileImage(?string $filename): bool
    {
        if (!$filename) {
            return false;
        }

        $publicDisk = Storage::disk('public');
        $normalizedFilename = basename($filename);

        if ($publicDisk->exists($filename)) {
            $publicDisk->delete($filename);
            return true;
        }

        foreach (self::PROFILE_IMAGE_DIRECTORIES as $directory) {
            $path = $directory . $normalizedFilename;

            if ($publicDisk->exists($path)) {
                $publicDisk->delete($path);
                return true;
            }
        }

        foreach (self::PROFILE_IMAGE_DIRECTORIES as $directory) {
            $legacyPublicPath = public_path($directory . $normalizedFilename);

            if (file_exists($legacyPublicPath)) {
                @unlink($legacyPublicPath);
                return true;
            }
        }

        return false;
    }

    /**
     * Find user by ID across all tables
     */
    private function findUserById($id)
    {
        $user = User::find($id);
        if ($user) return $user;
        return $user;
    }

    /**
     * Get user profile (for API or other users to view)
     */
    public function viewProfile($id)
    {
        $user = $this->findUserById($id);
        if (!$user) abort(404);
        
        $authUser = $this->getCurrentUser();
        if (!$authUser) return redirect()->route('login');
        
        $userType = $this->getUserType();
        
        // Use role-specific views to keep dashboard visible
        if ($userType === 'midwife') {
            return view('midwife.profile.view', compact('user'));
        } elseif ($userType === 'bhw') {
            return view('bhw.profile.view', compact('user'));
        } elseif ($userType === 'bhw-president') {
            return view('bhw-president.profile.view', compact('user'));
        } elseif ($userType === 'cho' || $userType === 'rhu') {
            $profileLayout = $userType . '.layout';
            $profileSection = $userType . '-content';

            return view('profile.view', compact('user', 'profileLayout', 'profileSection'));
        } else {
            return view('user.profile.view', compact('user'));
        }
    }

    /**
     * Update user's emergency contacts
     */
    public function updateEmergencyContacts(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) return redirect()->route('login');

        $validated = $request->validate([
            // Primary emergency contact validation
            'emergency_name_1' => 'required|string|max:255',
            'emergency_relationship_1' => 'required|string|max:255',
            'emergency_contact_number_1' => 'required|string|max:255',
            'emergency_address_1' => 'nullable|string|max:500',
             
            // Secondary emergency contact validation
            'emergency_name_2' => 'nullable|string|max:255',
            'emergency_relationship_2' => 'nullable|required_with:emergency_name_2|string|max:255',
            'emergency_contact_number_2' => 'nullable|required_with:emergency_name_2|string|max:255',
            'emergency_address_2' => 'nullable|string|max:500',
             
            // Tertiary emergency contact validation
            'emergency_name_3' => 'nullable|string|max:255',
            'emergency_relationship_3' => 'nullable|required_with:emergency_name_3|string|max:255',
            'emergency_contact_number_3' => 'nullable|required_with:emergency_name_3|string|max:255',
            'emergency_address_3' => 'nullable|string|max:500',
        ]);

        // Update/create Primary Emergency Contact
        $user->emergencyContacts()->updateOrCreate(
            ['contact_order' => 1],
            [
                'name' => $validated['emergency_name_1'],
                'relationship' => $validated['emergency_relationship_1'],
                'contact_number' => $validated['emergency_contact_number_1'],
                'address' => $validated['emergency_address_1'] ?? null,
                'contact_order' => 1,
            ]
        );

        // Update/create Secondary Emergency Contact (if provided)
        if (!empty($validated['emergency_name_2'])) {
            $user->emergencyContacts()->updateOrCreate(
                ['contact_order' => 2],
                [
                    'name' => $validated['emergency_name_2'],
                    'relationship' => $validated['emergency_relationship_2'],
                    'contact_number' => $validated['emergency_contact_number_2'],
                    'address' => $validated['emergency_address_2'] ?? null,
                    'contact_order' => 2,
                    'is_primary' => false,
                ]
            );
        } else {
            // Delete secondary contact if it was cleared
            $user->emergencyContacts()->where('contact_order', 2)->delete();
        }

        // Update/create Tertiary Emergency Contact (if provided)
        if (!empty($validated['emergency_name_3'])) {
            $user->emergencyContacts()->updateOrCreate(
                ['contact_order' => 3],
                [
                    'name' => $validated['emergency_name_3'],
                    'relationship' => $validated['emergency_relationship_3'],
                    'contact_number' => $validated['emergency_contact_number_3'],
                    'address' => $validated['emergency_address_3'] ?? null,
                    'contact_order' => 3,
                    'is_primary' => false,
                ]
            );
        } else {
            // Delete tertiary contact if it was cleared
            $user->emergencyContacts()->where('contact_order', 3)->delete();
        }

        return back()->with('success', 'Emergency contacts updated successfully.');
    }

    /**
     * Download the authenticated patient's own data (Data Privacy Act
     * right to access) as a CSV bundle: health records, pregnancies,
     * checkups, and cycle history.
     */
    public function downloadData()
    {
        $user = $this->getCurrentUser();
        if (!$user) return redirect()->route('login');

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['ReproCare personal data export', $user->name, now()->toDateTimeString()]);
        fputcsv($stream, []);

        fputcsv($stream, ['HEALTH RECORDS']);
        fputcsv($stream, ['Date', 'BP', 'Weight (kg)', 'Heart Rate', 'Temperature', 'Risk Level', 'Notes']);
        foreach ($user->healthRecords()->latest()->get() as $record) {
            fputcsv($stream, [
                optional($record->created_at)->toDateString(),
                $record->bp, $record->weight, $record->heart_rate,
                $record->temperature, $record->risk_level, $record->notes,
            ]);
        }
        fputcsv($stream, []);

        fputcsv($stream, ['PREGNANCIES']);
        fputcsv($stream, ['LMP', 'EDD', 'AOG (wks)', 'Risk', 'Ended']);
        foreach ($user->pregnancies()->latest()->get() as $pregnancy) {
            fputcsv($stream, [
                optional($pregnancy->lmp)->toDateString(),
                optional($pregnancy->edd)->toDateString(),
                $pregnancy->aog, $pregnancy->risk_level,
                optional($pregnancy->ended_at)->toDateString(),
            ]);
        }
        fputcsv($stream, []);

        fputcsv($stream, ['CHECKUPS']);
        fputcsv($stream, ['Scheduled Date', 'Purpose', 'Status']);
        foreach ($user->checkups()->latest('scheduled_date')->get() as $checkup) {
            fputcsv($stream, [
                optional($checkup->scheduled_date)->toDateString(),
                $checkup->purpose, $checkup->status,
            ]);
        }
        fputcsv($stream, []);

        fputcsv($stream, ['CYCLES']);
        fputcsv($stream, ['Period Start', 'Period End', 'Cycle Length', 'Notes']);
        foreach ($user->cycles()->latest('period_start_date')->get() as $cycle) {
            fputcsv($stream, [
                optional($cycle->period_start_date)->toDateString(),
                optional($cycle->period_end_date)->toDateString(),
                $cycle->cycle_length, $cycle->notes,
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        \App\Models\ActivityLog::log('export', 'Patient downloaded personal data export');

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reprocare-my-data-' . $user->id . '-' . now()->format('Ymd') . '.csv"',
        ]);
    }

    /**
     * Patient-initiated account deactivation: password-confirmed archive
     * (status + soft delete) plus immediate sign-out. Clinical rows stay
     * for RHU records compliance and the account can be restored by the RHU.
     */
    public function destroyAccount(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) return redirect()->route('login');

        $request->validate(['current_password' => 'required|string']);
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect. Account was NOT deactivated.']);
        }

        \App\Models\ActivityLog::log('archive', 'Patient deactivated own portal account (archived, restorable by RHU)', $user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $user->update(['status' => 'archived', 'archived_at' => now(), 'archived_reason' => 'Deactivated by account owner via portal']);
        $user->delete();

        return redirect()->route('home')->with('success', 'Your account has been deactivated and archived. Your clinical history remains with the RHU — contact them to restore access.');
    }
}
