<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Legacy city-wide 86-barangay list. Kept for reference only —
     * user-facing forms must use Barangay::catchmentNames('RHU 1')
     * (16 barangays) since this deployment serves RHU 1 alone.
     */
    public static function getSanCarlosBarangays(): array
    {
        return [
            'Abanon', 'Agdao', 'Anando', 'Ano', 'Antipangol', 'Aponit', 'Bacnar', 'Balaya', 'Balayong', 'Baldog',
            'Balite Sur', 'Balococ', 'Bani', 'Bega', 'Bocboc', 'Bogaoan', 'Bolingit', 'Bolosan', 'Bonifacio (Poblacion)',
            'Buenglat', 'Bugallon-Posadas Street (Poblacion)', 'Burgos Padlan (Poblacion)', 'Cacaritan', 'Caingal',
            'Calobaoan', 'Calomboyan', 'Caoayan-Kiling', 'Capataan', 'Cobol', 'Coliling', 'Cruz', 'Doyong', 'Gamata',
            'Guelew', 'Ilang', 'Inerangan', 'Isla', 'Libas', 'Lilimasan', 'Longos', 'Lucban (Poblacion)', 'M. Soriano',
            'Mabalbalino', 'Mabini (Poblacion)', 'Magtaking', 'Malacañang', 'Maliwara', 'Mamarlao', 'Manzon',
            'Matagdem', 'Mestizo Norte', 'Naguilayan', 'Nilentap', 'Padilla-Gomez', 'Pagal', 'Paitan-Panoypoy',
            'Palaming', 'Palaris (Poblacion)', 'Palospos', 'Pangalangan', 'Pangoloan', 'Pangpang', 'Parayao',
            'Payapa', 'Payar', 'Perez Boulevard (Poblacion)', 'PNR Station Site', 'Polo', 'Quezon Boulevard (Poblacion)',
            'Quintong', 'Rizal (Poblacion)', 'Roxas Boulevard (Poblacion)', 'Salinap', 'San Juan', 'San Pedro-Taloy',
            'Sapinit', 'Supo', 'Talang', 'Tamayo', 'Tandang Sora', 'Tandoc', 'Tarece', 'Tarectec', 'Tayambani', 'Tebag', 'Turac'
        ];
    }

    // Show registration form — RHU 1 serves 16 catchment barangays only.
    public function showRegisterForm()
    {
        $barangays = Barangay::allNames();

        return view('auth.register', compact('barangays'));
    }

    // Handle registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:10',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'house_number' => 'nullable|string|max:100',
            'purok' => 'nullable|string|max:100',
            'sitio' => 'nullable|string|max:200',
            'barangay' => ['required', 'string', 'max:255', Rule::in(Barangay::allNames())],
            'purok_id' => 'nullable|exists:puroks,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address_label' => 'nullable|string|max:500',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'id_image_data_front' => 'required|string',
            'id_image_data_back' => 'required|string',
            
            // Partner / Additional Contact
            'partner_name' => 'nullable|string|max:255',
            'partner_contact' => 'nullable|string|max:20',
            
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

        $barangay = $validated['barangay'];

        // Compose full address from components if address field is empty
        $addressParts = array_filter([
            $request->filled('house_number') ? 'House/Unit ' . $request->house_number : null,
            $request->filled('purok') ? (str_starts_with(strtolower($request->purok), 'purok') ? $request->purok : 'Purok ' . $request->purok) : null,
            $request->filled('sitio') ? $request->sitio : null,
            $barangay,
            'San Carlos City, Pangasinan'
        ]);
        $resolvedAddress = !empty($validated['address']) ? $validated['address'] : (!empty($addressParts) ? implode(', ', $addressParts) : null);

        // Process ID images (front and back)
        $idImageFrontPath = null;
        $idImageBackPath = null;

        // Process front ID image
        if ($request->id_image_data_front) {
            $idImageFrontPath = $this->processIdImage($request->id_image_data_front, 'front');
        }

        // Process back ID image
        if ($request->id_image_data_back) {
            $idImageBackPath = $this->processIdImage($request->id_image_data_back, 'back');
        }

        $resolvedPurokId = $validated['purok_id']
            ?? Purok::resolveIdFromText($request->input('purok'), $barangay);
        $user = User::create([
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'contact_number' => $validated['contact_number'] ?? null,
            'address' => trim(($resolvedAddress ?? '').($request->filled('address_label') ? ' ('.$request->address_label.')' : '')) ?: null,
            'barangay' => $barangay,
            'purok_id' => $resolvedPurokId,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'address_label' => $validated['address_label'] ?? null,
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'user',
            'status' => 'pending',
            'id_image_front' => $idImageFrontPath,
            'id_image_back'  => $idImageBackPath,
            'partner_name'   => $validated['partner_name'] ?? null,
            'partner_contact'=> $validated['partner_contact'] ?? null,
        ]);

        // Save Primary Emergency Contact
        $user->emergencyContacts()->create([
            'name' => $validated['emergency_name_1'],
            'relationship' => $validated['emergency_relationship_1'],
            'contact_number' => $validated['emergency_contact_number_1'],
            'address' => $validated['emergency_address_1'] ?? null,
            'contact_order' => 1,
            'is_primary' => true,
        ]);

        // Save Secondary Emergency Contact (if provided)
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

        // Save Tertiary Emergency Contact (if provided)
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

        // Do NOT auto-login — show pending message
        return redirect()->route('login')
            ->with('pending_registration', true);
    }

    /**
     * Process ID image from base64 data
     */
    private function processIdImage($imageData, $side)
    {
        // Remove data URI prefix if present
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $extension = $matches[1];
        } else {
            $extension = 'jpg';
        }
        
        $imageData = base64_decode($imageData);
        $fileName = 'id_' . $side . '_' . time() . '_' . uniqid() . '.' . $extension;
        $imagePath = 'uploads/ids/' . $fileName;
        
        Storage::disk('public')->put($imagePath, $imageData);
        
        return $imagePath;
    }

    // Show login form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'remember' => 'nullable|boolean',
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $user = Auth::user();

            // Block pending users from logging in
            if ($user->status === 'pending') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is pending RHU verification. You will receive an SMS once approved.',
                ])->onlyInput('email');
            }

            // Block suspended users
            if ($user->status === 'suspended') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been suspended. Please contact the administrator.',
                ])->onlyInput('email');
            }

            // Block deactivated / archived accounts (e.g. former admins after a role handover).
            // Only approved accounts may hold an active session.
            if (($user->status ?? 'approved') !== 'approved') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is no longer active. Please contact the City Health Office.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Redirect based on role
            return match($user->role) {
                'cho' => redirect()->route('cho.dashboard'),
                'rhu' => redirect()->route('rhu.dashboard'),
                'midwife' => redirect()->route('midwife.dashboard'),
                'bhw' => redirect()->route('bhw.dashboard'),
                'bhw_president' => redirect()->route('bhw-president.dashboard'),
                'user' => redirect()->route('user.dashboard'),
                default => redirect()->route('dashboard'),
            };
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'If that email exists, a reset link was sent.')
            : back()->withErrors(['email' => 'Unable to send reset link. Try again later.'])->onlyInput('email');
    }

    public function showResetForm(string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => request('email')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required', 'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password)])->save();
        });
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password reset. Please log in.')
            : back()->withErrors(['email' => 'This reset link is invalid or expired.'])->onlyInput('email');
    }

    // Handle logout
    public function logout(Request $request)
    {
        // Clear the logout flag first to allow logout
        $request->session()->forget('user_logged_out');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
            ->header('Surrogate-Control', 'no-store')
            ->header('X-Frame-Options', 'DENY')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-XSS-Protection', '1; mode=block');
    }
}
