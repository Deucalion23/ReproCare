<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pregnancy;
use App\Models\Checkup;
use App\Models\HealthRecord;
use App\Models\BhwMonthlyReport;
use App\Models\SupplyRequest;
use App\Models\MaternalDeath;
use App\Models\MaternalMorbidity;
use App\Models\WalkInPatient;
use App\Models\ActivityLog;
use App\Models\Purok;
use App\Services\RiskAnalysisService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class RhuController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // RHU Dashboard
    public function dashboard()
    {
        $rhuUser = auth()->user();
        
        // Auto-run alert checks when RHU admin visits dashboard
        try {
            Checkup::markOverdueCheckups();
        } catch (\Exception $e) {
            \Log::error('RHU dashboard alert check failed: ' . $e->getMessage());
        }

        // Stats
        $stats = [
            'totalPatients' => User::where('role', 'user')->where('status', 'approved')->count(),
            'activePregnancies' => Pregnancy::active()->count(),
            'highRiskPatients' => Pregnancy::active()->highRisk()->count(),
            'pendingSupplyRequests' => SupplyRequest::where('status', 'submitted')->count(),
            'pendingBhwReports' => BhwMonthlyReport::where('submission_status', 'submitted_to_midwife')->count(),
            'maternalDeathsCount' => MaternalDeath::count(),
            'nearMissCount' => MaternalMorbidity::count(),
        ];

        // ANC Coverage rate computation (Feature E)
        // DOH standard: at least 4 prenatal visits (1 in 1st trimester, 1 in 2nd, 2 in 3rd)
        // For dashboard purposes, we calculate the percentage of completed pregnancies that had 4+ visits
        $completedPregnancies = Pregnancy::completed()->withCount('healthRecords')->get();
        $totalCompleted = $completedPregnancies->count();
        $ancCompliant = $completedPregnancies->filter(fn($p) => $p->health_records_count >= 4)->count();
        $stats['ancCoverageRate'] = $totalCompleted > 0 ? round(($ancCompliant / $totalCompleted) * 100, 1) : 0;

        // Recent Maternal Deaths
        $recentDeaths = MaternalDeath::with(['user', 'walkInPatient', 'purok'])
            ->latest('death_date')
            ->limit(5)
            ->get();

        // Recent Near-Miss Events
        $recentMorbidities = MaternalMorbidity::with(['user', 'walkInPatient', 'purok'])
            ->latest('event_date')
            ->limit(5)
            ->get();

        // Pending Supply Requests
        $recentSupplyRequests = SupplyRequest::with('requestedBy')
            ->latest()
            ->limit(5)
            ->get();

        return view('rhu.dashboard', array_merge($stats, compact(
            'recentDeaths',
            'recentMorbidities',
            'recentSupplyRequests'
        )));
    }

    // Settings
    public function settings()
    {
        $staff = User::whereIn('role', ['midwife', 'bhw', 'bhw_president'])
            ->orderBy('role')->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'role', 'status', 'barangay']);
        $presidents = User::where('role', 'bhw_president')
            ->where('status', '!=', 'archived')
            ->orderBy('barangay')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'status', 'barangay']);
        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);
        $promotableBhws = User::where('role', 'bhw')
            ->where('status', 'approved')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'barangay']);
        $delegationCandidates = User::whereIn('role', ['rhu', 'midwife'])
            ->where('status', 'approved')
            ->where('id', '!=', auth()->id())
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'role']);

        return view('rhu.settings', compact('staff', 'presidents', 'barangays', 'promotableBhws', 'delegationCandidates'));
    }

    // ─── Midwife Management ──────────────────────────────────────────

    public function midwives()
    {
        $midwives = User::where('role', 'midwife')
            ->where('status', '!=', 'archived')
            ->latest()
            ->paginate(15);

        return view('rhu.midwives.index', compact('midwives'));
    }

    public function createMidwife()
    {
        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);

        return view('rhu.midwives.create', compact('barangays'));
    }

    public function storeMidwife(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'contact_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'license_number' => 'required|string|max:60',
            'license_expiry' => 'nullable|date|after:today',
            'specialization' => 'nullable|string|max:150',
            'rhu_assignment' => 'nullable|string|max:255',
            'assigned_barangay' => 'nullable|string|max:255',
            'catchment_barangays' => 'nullable|array',
            'catchment_barangays.*' => 'string|max:255',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'midwife';
        $data['status'] = 'approved';
        $data['registered_by_rhu_id'] = auth()->id();
        $data['catchment_barangays'] = array_values($request->input('catchment_barangays', []));

        $midwife = User::create($data);

        ActivityLog::log('create', "Created midwife account for {$midwife->name} (Lic. {$midwife->license_number})", $midwife);

        return redirect()->route('rhu.midwives.index')
            ->with('success', 'Midwife created successfully with credentials provisioned.');
    }

    public function midwifeDetails($id)
    {
        $midwife = User::where('role', 'midwife')->findOrFail($id);
        return view('rhu.midwives.show', compact('midwife'));
    }

    public function editMidwife($id)
    {
        $midwife = User::where('role', 'midwife')->findOrFail($id);
        return view('rhu.midwives.edit', compact('midwife'));
    }

    public function updateMidwife(Request $request, $id)
    {
        $midwife = User::where('role', 'midwife')->findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'contact_number' => 'required|string|max:20',
            'license_number' => 'nullable|string|max:60',
            'license_expiry' => 'nullable|date',
            'specialization' => 'nullable|string|max:150',
            'rhu_assignment' => 'nullable|string|max:255',
            'assigned_barangay' => 'nullable|string|max:255',
            'catchment_barangays' => 'nullable|array',
            'catchment_barangays.*' => 'string|max:255',
        ]);

        $data = $request->except(['password', 'password_confirmation']);
        $data['catchment_barangays'] = array_values($request->input('catchment_barangays', []));

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $midwife->update($data);

        ActivityLog::log('update', "Updated midwife account for {$midwife->name}", $midwife);

        return redirect()->route('rhu.midwives.index')
            ->with('success', 'Midwife updated successfully.');
    }

    public function destroyMidwife(Request $request, $id)
    {
        // Single archive function — data-retention rule: never hard-delete
        // clinical staff, so consultations and sign-offs stay attached.
        $midwife = User::where('role', 'midwife')->findOrFail($id);
        $name = $midwife->name;
        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'Midwife archived by RHU via console';
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveUser($midwife, $reason, auth()->user());
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            return back()->withErrors(['handover' => $e->getMessage()])->withInput();
        }

        return redirect()->route('rhu.midwives.index')
            ->with('success', "Midwife {$name} archived successfully. Use Staff Transitions to reassign their cases.");
    }

    // ─── BHW President Management (Copied from MidwifeController) ────

    public function bhwPresidents()
    {
        $bhwPresidents = User::where('role', 'bhw_president')
            ->where('status', '!=', 'archived')
            ->latest()
            ->paginate(15);

        foreach ($bhwPresidents as $president) {
            $president->managed_bhw_count = User::where('role', 'bhw')->count();
            $president->supervised_patients_count = User::where('role', 'user')->count();
            $president->active_pregnancies_count = Pregnancy::active()->count();
        }

        // Provide a view-friendly variable name ($presidents) expected by the blade
        $presidents = $bhwPresidents;

        return view('rhu.bhw-presidents.index', compact('presidents'));
    }

    public function createBhwPresident()
    {
        $currentPresident = User::where('role', 'bhw_president')
            ->where('status', '!=', 'archived')
            ->latest()
            ->first();

        $promotableBhws = User::where('role', 'bhw')
            ->where('status', 'approved')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'barangay']);

        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);

        return view('rhu.bhw-presidents.create', compact('currentPresident', 'promotableBhws', 'barangays'));
    }

    public function storeBhwPresident(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'contact_number' => 'required|string|max:20',
            'barangay' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        // ONE active President per ONE active Barangay.
        \App\Services\BhwPresidentAssignmentService::assertAvailable($request->barangay);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'bhw_president';
        $data['status'] = 'approved';
        $data['barangay'] = \App\Services\BhwPresidentAssignmentService::displayBarangay($request->barangay);

        $president = User::create($data);

        ActivityLog::log('create', "Appointed BHW President {$president->name}", $president);

        return redirect()->route('rhu.bhw-presidents.index')
            ->with('success', 'BHW President appointed successfully.');
    }

    /**
     * Promote an existing BHW (e.g. Ana M. Malasan) to BHW President
     * of the selected barangay lookup.
     */
    public function promoteBhwPresident(Request $request)
    {
        $request->validate([
            'bhw_id' => 'required|exists:users,id',
            'barangay' => 'required|string|max:255',
        ]);

        $bhw = User::where('role', 'bhw')->findOrFail($request->bhw_id);

        $president = \App\Services\BhwPresidentAssignmentService::promoteToPresident($bhw, $request->barangay);

        \App\Models\Notification::createNotification(
            $president->id,
            'You have been promoted to BHW President of ' . $president->barangay . '.',
            'Promotion to BHW President',
            'success',
            '/bhw-president/dashboard'
        );

        ActivityLog::log('update', "Promoted {$president->name} to BHW President of {$president->barangay}", $president);

        return redirect()->route('rhu.bhw-presidents.index')
            ->with('success', "{$president->name} promoted to BHW President of {$president->barangay}.");
    }

    public function showBhwPresident($id)
    {
        $bhwPresident = User::where('role', 'bhw_president')->findOrFail($id);
        $stats = [
            'managed_bhw_count' => User::where('role', 'bhw')->count(),
            'supervised_patients_count' => User::where('role', 'user')->count(),
            'active_pregnancies_count' => Pregnancy::active()->count(),
        ];

        return view('rhu.bhw-presidents.show', compact('bhwPresident', 'stats'));
    }

    public function editBhwPresident($id)
    {
        $bhwPresident = User::where('role', 'bhw_president')->findOrFail($id);
        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);
        return view('rhu.bhw-presidents.edit', compact('bhwPresident', 'barangays'));
    }

    public function updateBhwPresident(Request $request, $id)
    {
        $bhwPresident = User::where('role', 'bhw_president')->findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'contact_number' => 'required|string|max:20',
            'barangay' => 'required|string|max:255',
        ]);

        // Keep the one-president-per-barangay invariant on reassignment.
        \App\Services\BhwPresidentAssignmentService::assertAvailable($request->barangay, (int) $id);

        $data = $request->except(['password', 'password_confirmation']);
        $data['barangay'] = \App\Services\BhwPresidentAssignmentService::displayBarangay($request->barangay);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $bhwPresident->update($data);

        ActivityLog::log('update', "Updated BHW President {$bhwPresident->name}", $bhwPresident);

        return redirect()->route('rhu.bhw-presidents.index')
            ->with('success', 'BHW President updated successfully.');
    }

    public function destroyBhwPresident(Request $request, $id)
    {
        // Single archive function — data-retention rule: archive instead of deleting.
        $bhwPresident = User::where('role', 'bhw_president')->findOrFail($id);
        $name = $bhwPresident->name;
        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'BHW President archived by RHU via console';
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveUser($bhwPresident, $reason, auth()->user());
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            return back()->withErrors(['handover' => $e->getMessage()])->withInput();
        }

        return redirect()->route('rhu.bhw-presidents.index')
            ->with('success', 'BHW President archived successfully (retained for audit).');
    }

    // ─── BHW Account Management (RHU Admin) ───────────────────────────
    // Full lifecycle for rank-and-file BHWs: list, register, edit,
    // archive/activate. Day-to-day supervision stays with the BHW President.

    public function bhws(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $barangay = $request->input('barangay');

        $query = User::where('role', 'bhw');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($barangay) {
            $query->where('barangay', $barangay);
        }

        $bhws = $query->with(['purok', 'activeBhwAssignment.purok'])
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        foreach ($bhws as $bhw) {
            $bhw->records_count = HealthRecord::where('recorded_by_id', $bhw->id)->count();
            $bhw->checkups_count = Checkup::where('scheduled_by_id', $bhw->id)->count();
        }

        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);

        return view('rhu.bhws.index', compact('bhws', 'search', 'status', 'barangay', 'barangays'));
    }

    public function createBhw()
    {
        $puroks = Purok::orderBy('barangay')->orderBy('name')->get();
        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);

        return view('rhu.bhws.create', compact('puroks', 'barangays'));
    }

    public function storeBhw(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:2',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'contact_number' => 'required|string|max:20',
            'purok' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'barangay' => 'nullable|string|max:255',
            'certification_number' => 'nullable|string|max:255',
            'certification_date' => 'nullable|date',
        ]);

        $selectedBarangay = $request->barangay;
        $purokId = \App\Models\Purok::resolveIdFromText($request->purok, $selectedBarangay);

        $bhw = User::create([
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'contact_number' => $validated['contact_number'],
            'phone' => $validated['contact_number'],
            'address' => $request->address ?: implode(', ', array_filter([$request->purok, $selectedBarangay, 'San Carlos City, Pangasinan'])),
            'purok_id' => $purokId,
            'barangay' => $selectedBarangay,
            'certification_number' => $validated['certification_number'] ?? null,
            'certification_date' => $validated['certification_date'] ?? null,
            'role' => 'bhw',
            'status' => 'approved',
        ]);

        ActivityLog::log('create', "RHU registered BHW {$bhw->name}", $bhw);

        return redirect()->route('rhu.bhws.index')
            ->with('success', 'BHW registered successfully.');
    }

    public function editBhw($id)
    {
        $bhw = User::where('role', 'bhw')->findOrFail($id);
        $puroks = Purok::orderBy('barangay')->orderBy('name')->get();
        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);

        return view('rhu.bhws.edit', compact('bhw', 'puroks', 'barangays'));
    }

    public function updateBhw(Request $request, $id)
    {
        $bhw = User::where('role', 'bhw')->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:2',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'contact_number' => 'required|string|max:20',
            'purok' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'barangay' => 'nullable|string|max:255',
            'status' => 'required|in:approved,inactive,archived,suspended',
            'certification_number' => 'nullable|string|max:255',
            'certification_date' => 'nullable|date',
        ]);

        $selectedBarangay = $request->barangay ?: $bhw->barangay;

        $wasApproved = ($bhw->status ?? 'approved') === 'approved';

        $bhw->update([
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'contact_number' => $validated['contact_number'],
            'phone' => $validated['contact_number'],
            'address' => $request->address ?: $bhw->address,
            'purok_id' => $request->filled('purok') ? \App\Models\Purok::resolveIdFromText($request->purok, $selectedBarangay) : $bhw->purok_id,
            'barangay' => $selectedBarangay,
            'status' => $validated['status'],
            'certification_number' => $validated['certification_number'] ?? null,
            'certification_date' => $validated['certification_date'] ?? null,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'required|string|min:8|confirmed']);
            $bhw->update(['password' => Hash::make($request->password)]);
        }

        // Leaving approved status revokes device access + offline cache.
        if ($wasApproved && ($bhw->fresh()->status ?? 'approved') !== 'approved') {
            $bhw->increment('pwa_cache_version');
            \App\Services\SessionRevocationService::terminateUserSessions((int) $bhw->id);
        }

        ActivityLog::log('update', "RHU updated BHW {$bhw->name}", $bhw);

        return redirect()->route('rhu.bhws.index')
            ->with('success', 'BHW updated successfully.');
    }

    public function archiveBhw(Request $request, $id)
    {
        // Reason is collected via prompt/modal; default keeps legacy icon-button working.
        $request->validate(['reason' => 'nullable|string|max:1000']);

        $bhw = User::where('role', 'bhw')->findOrFail($id);
        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'Archived by RHU via BHW console';
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveUser(
                $bhw,
                $reason,
                auth()->user()
            );
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            // Offboarding guard failures land here with a clear handover message.
            return back()->withErrors(['handover' => $e->getMessage()])->withInput();
        }

        return redirect()->route('rhu.bhws.index')
            ->with('success', 'BHW archived successfully. Their device cache was invalidated and sessions revoked.');
    }

    public function activateBhw($id)
    {
        $bhw = User::where('role', 'bhw')->findOrFail($id);
        $bhw->update(['status' => 'approved']);
        ActivityLog::log('update', "RHU re-activated BHW {$bhw->name}", $bhw);

        return redirect()->route('rhu.bhws.index')
            ->with('success', 'BHW re-activated successfully.');
    }

    // ─── RHU account settings ─────────────────────────────────────────

    public function updateSettings(Request $request)
    {
        $section = $request->input('section', 'password');

        // ── Own password ──────────────────────────────────────────────
        if ($section === 'password') {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
                'confirm_password' => 'required|string|same:new_password',
            ]);

            $user = auth()->user();

            if (!Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }

            $user->password = Hash::make($request->input('new_password'));
            $user->save();
            ActivityLog::log('update', 'RHU admin changed account password');

            return back()->with('success', 'Password updated successfully.');
        }

        // ── Facility profile ──────────────────────────────────────────
        if ($section === 'facility') {
            $data = $request->validate([
                'station_name' => 'required|string|max:255',
                'operating_hours' => 'nullable|string|max:255',
                'contact_number' => 'nullable|string|max:30',
                'catchment_barangays' => 'nullable|array',
                'catchment_barangays.*' => 'string|max:255',
            ]);
            \App\Models\Setting::set('rhu.station_name', $data['station_name'], auth()->id());
            \App\Models\Setting::set('rhu.operating_hours', $data['operating_hours'] ?? '', auth()->id());
            \App\Models\Setting::set('rhu.contact_number', $data['contact_number'] ?? '', auth()->id());
            \App\Models\Setting::set('rhu.catchment_barangays', json_encode(array_values($data['catchment_barangays'] ?? [])), auth()->id());
            ActivityLog::log('update', 'RHU admin updated facility profile');

            return back()->with('success', 'Facility profile saved.');
        }

        // ── Staff password reset (midwives, BHWs, presidents) ─────────
        if ($section === 'staff_reset') {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'temp_password' => 'required|string|min:8',
            ]);
            $staff = User::whereIn('role', ['midwife', 'bhw', 'bhw_president'])->findOrFail($data['user_id']);
            $staff->password = Hash::make($data['temp_password']);
            $staff->save();
            ActivityLog::log('update', "RHU reset password for {$staff->role} {$staff->name}", $staff);

            return back()->with('success', "Temporary password set for {$staff->name}. Share it securely — they should change it on next login.");
        }

        // ── Staff activation / deactivation ───────────────────────────
        if ($section === 'staff_status') {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'status' => 'required|in:approved,inactive,suspended,archived',
            ]);
            $staff = User::whereIn('role', ['midwife', 'bhw', 'bhw_president'])->findOrFail($data['user_id']);
            $staff->update(['status' => $data['status']]);
            ActivityLog::log('update', "RHU set {$staff->role} {$staff->name} to {$data['status']}", $staff);

            return back()->with('success', "{$staff->name} is now {$data['status']}.");
        }

        // ── BHW President bind / unbind (1-per-barangay lock) ─────────
        if ($section === 'president_bind') {
            $data = $request->validate([
                'bhw_id' => 'required|exists:users,id',
                'barangay' => 'required|string|max:255',
            ]);
            $bhw = User::where('role', 'bhw')->where('status', 'approved')->findOrFail($data['bhw_id']);

            try {
                \App\Services\BhwPresidentAssignmentService::assertAvailable($data['barangay']);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return back()->withErrors($e->errors())->withInput();
            }

            $bhw->update([
                'role' => 'bhw_president',
                'barangay' => \App\Services\BhwPresidentAssignmentService::displayBarangay($data['barangay']),
            ]);
            \App\Models\Notification::createNotification(
                $bhw->id, 'You have been appointed BHW President. Manage your barangay BHWs from your portal.',
                'Appointed BHW President', 'success', route('bhw-president.dashboard')
            );
            ActivityLog::log('update', "RHU bound {$bhw->name} as BHW President of {$data['barangay']}", $bhw);

            return back()->with('success', "{$bhw->name} is now BHW President of {$data['barangay']}.");
        }

        if ($section === 'president_unbind') {
            $data = $request->validate(['president_id' => 'required|exists:users,id']);
            $president = User::where('role', 'bhw_president')->findOrFail($data['president_id']);
            $president->update(['role' => 'bhw', 'status' => 'approved']);
            ActivityLog::log('update', "RHU unbound {$president->name} from BHW President of {$president->barangay}", $president);

            return back()->with('success', "{$president->name} returned to BHW. The barangay slot is now open.");
        }

        // ── Station escalation alerts ─────────────────────────────────
        if ($section === 'alerts') {
            $data = $request->validate([
                'recipients' => 'nullable|array',
                'recipients.*' => 'exists:users,id',
            ]);
            $ids = array_values(array_unique(array_map('intval', $data['recipients'] ?? [])));
            // Only RHU staff + midwives may receive escalations.
            $valid = User::whereIn('id', $ids)->whereIn('role', ['rhu', 'midwife'])
                ->where('status', 'approved')->pluck('id')->map(fn ($id) => (int) $id)->all();
            \App\Models\Setting::set('rhu.escalation_recipients', json_encode(array_values($valid)), auth()->id());
            ActivityLog::log('update', 'RHU updated high-risk escalation recipients');

            return back()->with('success', 'Escalation alert recipients saved. They will be notified on every high-risk flag.');
        }

        // ── Report schedule templates ─────────────────────────────────
        if ($section === 'templates') {
            $data = $request->validate([
                'deadline_day' => 'required|integer|min:1|max:28',
                'template_monthly' => 'nullable|string|max:5000',
                'template_quarterly' => 'nullable|string|max:5000',
            ]);
            \App\Models\Setting::set('reports.deadline_day', $data['deadline_day'], auth()->id());
            \App\Models\Setting::set('reports.template_monthly', $data['template_monthly'] ?? '', auth()->id());
            \App\Models\Setting::set('reports.template_quarterly', $data['template_quarterly'] ?? '', auth()->id());
            ActivityLog::log('update', 'RHU updated report schedule templates');

            return back()->with('success', 'Report templates and deadline saved.');
        }

        // ── My Profile: identity + operational contact + photo ──────────
        if ($section === 'myprofile') {
            $data = $request->validate([
                'first_name' => 'required|string|max:100',
                'middle_initial' => 'nullable|string|max:5',
                'last_name' => 'required|string|max:100',
                'official_title' => 'nullable|string|max:150',
                'employee_id' => 'nullable|string|max:60',
                'license_number' => 'nullable|string|max:60',
                'license_expiry' => 'nullable|date',
                'contact_number' => 'nullable|string|max:30',
                'secondary_contact' => 'nullable|string|max:30',
                'secondary_email' => 'nullable|email|max:255',
                'station_contact' => 'nullable|string|max:30',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
            $user = auth()->user();

            if ($request->hasFile('profile_image')) {
                // Remove the previous photo so storage never fills with orphans.
                if ($user->profile_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_image)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_image);
                }
                $data['profile_image'] = $request->file('profile_image')->store('uploads/profile', 'public');
            }

            $user->update($data);
            ActivityLog::log('update', 'RHU admin updated My Profile identity and contact details');

            return back()->with('success', isset($data['profile_image']) ? 'Profile photo updated.' : 'My Profile updated.');
        }

        // ── Operational notification preferences ────────────────────────
        if ($section === 'rhu_notifications') {
            $user = auth()->user();
            $user->update([
                'pref_bhw_conflicts' => $request->boolean('pref_bhw_conflicts'),
                'pref_pending_reports' => $request->boolean('pref_pending_reports'),
                'pref_highrisk_escalation' => $request->boolean('pref_highrisk_escalation'),
                'pref_escalation_alerts' => $request->boolean('pref_escalation_alerts'),
            ]);
            ActivityLog::log('update', 'RHU admin updated operational notification preferences');

            return back()->with('success', 'Operational notification preferences saved.');
        }

        // ── Password recovery security questions ────────────────────────
        if ($section === 'recovery') {
            $data = $request->validate([
                'recovery_question_1' => 'required|string|max:255',
                'recovery_answer_1' => 'required|string|min:3|max:255',
                'recovery_question_2' => 'nullable|string|max:255',
                'recovery_answer_2' => 'nullable|string|min:3|max:255',
            ]);
            $user = auth()->user();
            $user->update([
                'recovery_question_1' => $data['recovery_question_1'],
                'recovery_answer_1' => Hash::make($data['recovery_answer_1']),
                'recovery_question_2' => $data['recovery_question_2'] ?? null,
                'recovery_answer_2' => !empty($data['recovery_answer_2']) ? Hash::make($data['recovery_answer_2']) : null,
            ]);
            ActivityLog::log('update', 'RHU admin updated password recovery security questions');

            return back()->with('success', 'Recovery security questions saved.');
        }

        // ── Out-of-office delegation ────────────────────────────────────
        if ($section === 'delegation') {
            $data = $request->validate([
                'out_of_office' => 'nullable|boolean',
                'delegate_to_user_id' => 'nullable|exists:users,id',
                'ooo_note' => 'nullable|string|max:255',
            ]);
            $user = auth()->user();
            if (!empty($data['delegate_to_user_id'])) {
                $delegate = User::where('status', 'approved')
                    ->whereIn('role', ['rhu', 'midwife'])
                    ->where('id', '!=', $user->id)
                    ->find($data['delegate_to_user_id']);
                if (!$delegate) {
                    return back()->withErrors(['delegate_to_user_id' => 'Backup must be an approved RHU staff member or midwife.'])->withInput();
                }
            }
            $user->update([
                'out_of_office' => $request->boolean('out_of_office'),
                'delegate_to_user_id' => $data['delegate_to_user_id'] ?? null,
                'ooo_note' => $data['ooo_note'] ?? null,
            ]);
            ActivityLog::log('update', 'RHU admin updated out-of-office delegation');

            return back()->with('success', 'Delegation settings saved.');
        }

        // ── Two-factor authentication toggle ────────────────────────────
        if ($section === 'twofa') {
            $user = auth()->user();
            $user->update(['pref_2fa_enabled' => $request->boolean('pref_2fa_enabled')]);
            ActivityLog::log('update', 'RHU admin ' . ($user->pref_2fa_enabled ? 'enabled' : 'disabled') . ' two-factor authentication');

            return back()->with('success', 'Two-factor authentication ' . ($user->pref_2fa_enabled ? 'enabled.' : 'disabled.'));
        }

        // ── Sign out all other devices ──────────────────────────────────
        if ($section === 'sessions') {
            $request->validate(['current_password' => 'required|string']);
            $user = auth()->user();
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
            \Illuminate\Support\Facades\Auth::logoutOtherDevices($request->input('current_password'));
            ActivityLog::log('update', 'RHU admin revoked all other active sessions');

            return back()->with('success', 'All other devices have been signed out.');
        }

        return back()->with('error', 'Unknown settings section.');
    }

    // ─── Supply Requests (RHU → CHO) ────────────────────────────────

    public function supplyRequests()
    {
        $requests = SupplyRequest::with(['requestedBy', 'approvedBy'])
            ->latest()
            ->paginate(15);

        return view('rhu.supply-requests.index', compact('requests'));
    }

    public function createSupplyRequest()
    {
        return view('rhu.supply-requests.create');
    }

    public function storeSupplyRequest(\App\Http\Requests\StoreSupplyRequest $request)
    {
        $supplyName = $request->supply_name === '__other__' ? $request->validated('supply_name_other') : $request->validated('supply_name');

        $supplyRequest = SupplyRequest::create([
            'requested_by_id' => auth()->id(),
            'supply_category' => $request->supply_category,
            'supply_name' => $supplyName,
            'quantity_requested' => $request->quantity_requested,
            'unit' => $request->unit,
            'urgency' => $request->urgency,
            'reason' => $request->reason,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        ActivityLog::log('request_supply', "Requested {$request->quantity_requested} {$request->unit} of {$supplyName} from CHO", $supplyRequest);

        return redirect()->route('rhu.supply-requests.index')
            ->with('success', 'Supply request submitted successfully to CHO.');
    }

    public function showSupplyRequest($id)
    {
        $supplyRequest = SupplyRequest::with(['requestedBy', 'approvedBy'])->findOrFail($id);
        return view('rhu.supply-requests.show', compact('supplyRequest'));
    }

    public function destroySupplyRequest(Request $request, $id)
    {
        $supplyRequest = SupplyRequest::findOrFail($id);

        if ($supplyRequest->status !== 'draft' && $supplyRequest->status !== 'submitted') {
            return back()->with('error', 'Cannot delete a supply request that has been reviewed or approved.');
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveRecord(
                $supplyRequest,
                $request->input('reason', 'Supply request cancelled by RHU'),
                auth()->user()
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()])->withInput();
        }

        return redirect()->route('rhu.supply-requests.index')
            ->with('success', 'Supply request cancelled and archived (retained for audit).');
    }

    // ─── Maternal Deaths (Feature A) ──────────────────────────────────

    public function maternalDeaths()
    {
        $deaths = MaternalDeath::with(['user', 'walkInPatient', 'purok'])
            ->latest('death_date')
            ->paginate(15);

        return view('rhu.maternal-deaths.index', compact('deaths'));
    }

    public function createMaternalDeath()
    {
        $users = User::where('role', 'user')->where('status', 'approved')->orderBy('first_name')->get();
        $walkIns = WalkInPatient::whereNull('converted_to_user_id')->orderBy('first_name')->get();
        $pregnancies = Pregnancy::orderBy('lmp', 'desc')->get();
        $puroks = Purok::orderBy('name')->get();
        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);

        return view('rhu.maternal-deaths.create', compact('users', 'walkIns', 'pregnancies', 'puroks', 'barangays'));
    }

    public function storeMaternalDeath(Request $request)
    {
        $request->validate([
            'patient_type' => 'required|in:registered,walk_in',
            'user_id' => 'required_if:patient_type,registered|nullable|exists:users,id',
            'walk_in_patient_id' => 'required_if:patient_type,walk_in|nullable|exists:walk_in_patients,id',
            'pregnancy_id' => 'nullable|exists:pregnancies,id',
            'purok_id' => 'required|exists:puroks,id',
            'death_date' => 'required|date|before_or_equal:today',
            'death_time' => 'nullable',
            'age_at_death' => 'nullable|integer|min:10|max:60',
            'place_of_death' => 'required|in:home,barangay_health_station,rhu,city_hospital,provincial_hospital,private_hospital,in_transit,other',
            'cause_of_death' => 'required|string|max:500',
            'cause_category' => 'required|in:hemorrhage,hypertension_eclampsia,sepsis,obstructed_labor,unsafe_abortion,embolism,other_direct,indirect_cause,unknown',
            'death_timing' => 'required|in:during_pregnancy,during_delivery,within_24_hours_postpartum,within_7_days_postpartum,within_42_days_postpartum,unknown',
            'notes' => 'nullable|string|max:1000',
        ]);

        $selectedPurok = Purok::findOrFail($request->purok_id);

        $maternalDeath = MaternalDeath::create([
            'user_id' => $request->patient_type === 'registered' ? $request->user_id : null,
            'walk_in_patient_id' => $request->patient_type === 'walk_in' ? $request->walk_in_patient_id : null,
            'pregnancy_id' => $request->pregnancy_id,
            'recorded_by_id' => auth()->id(),
            'purok_id' => $selectedPurok->id,
            'barangay' => $selectedPurok->barangay,
            'death_date' => $request->death_date,
            'death_time' => $request->death_time,
            'age_at_death' => $request->age_at_death,
            'place_of_death' => $request->place_of_death,
            'cause_of_death' => $request->cause_of_death,
            'cause_category' => $request->cause_category,
            'death_timing' => $request->death_timing,
            'notes' => $request->notes,
            'audit_status' => 'pending',
        ]);

        // End active pregnancy if exists and linked
        if ($request->pregnancy_id) {
            $pregnancy = Pregnancy::find($request->pregnancy_id);
            if ($pregnancy) {
                $pregnancy->update([
                    'ended_at' => $request->death_date,
                    'workflow_status' => 'completed',
                    'workflow_notes' => 'Ended due to maternal mortality case ID: ' . $maternalDeath->id,
                ]);
            }
        }

        ActivityLog::log('create', "Recorded maternal death case for patient: {$maternalDeath->patient_name}", $maternalDeath);

        return redirect()->route('rhu.maternal-deaths.index')
            ->with('success', 'Maternal death case recorded successfully.');
    }

    public function showMaternalDeath($id)
    {
        $death = MaternalDeath::with(['user', 'walkInPatient', 'pregnancy', 'recordedBy', 'reviewedBy', 'purok'])->findOrFail($id);
        return view('rhu.maternal-deaths.show', compact('death'));
    }

    public function editMaternalDeath($id)
    {
        $death = MaternalDeath::findOrFail($id);
        $puroks = Purok::orderBy('name')->get();
        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);
        return view('rhu.maternal-deaths.edit', compact('death', 'puroks', 'barangays'));
    }

    public function updateMaternalDeath(Request $request, $id)
    {
        $death = MaternalDeath::findOrFail($id);

        $request->validate([
            'purok_id' => 'required|exists:puroks,id',
            'death_date' => 'required|date|before_or_equal:today',
            'death_time' => 'nullable',
            'age_at_death' => 'nullable|integer|min:10|max:60',
            'place_of_death' => 'required|in:home,barangay_health_station,rhu,city_hospital,provincial_hospital,private_hospital,in_transit,other',
            'cause_of_death' => 'required|string|max:500',
            'cause_category' => 'required|in:hemorrhage,hypertension_eclampsia,sepsis,obstructed_labor,unsafe_abortion,embolism,other_direct,indirect_cause,unknown',
            'death_timing' => 'required|in:during_pregnancy,during_delivery,within_24_hours_postpartum,within_7_days_postpartum,within_42_days_postpartum,unknown',
            'notes' => 'nullable|string|max:1000',
        ]);

        $selectedPurok = Purok::findOrFail($request->purok_id);

        $death->update([
            'purok_id' => $selectedPurok->id,
            'barangay' => $selectedPurok->barangay,
            'death_date' => $request->death_date,
            'death_time' => $request->death_time,
            'age_at_death' => $request->age_at_death,
            'place_of_death' => $request->place_of_death,
            'cause_of_death' => $request->cause_of_death,
            'cause_category' => $request->cause_category,
            'death_timing' => $request->death_timing,
            'notes' => $request->notes,
        ]);

        ActivityLog::log('update', "Updated maternal death case for patient: {$death->patient_name}", $death);

        return redirect()->route('rhu.maternal-deaths.show', $id)
            ->with('success', 'Maternal death details updated.');
    }

    public function destroyMaternalDeath(Request $request, $id)
    {
        // Mortality cases are legal medical history: archive only, never hard-delete.
        $request->validate(['reason' => 'nullable|string|max:1000']);

        $death = MaternalDeath::findOrFail($id);
        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'Maternal death case archived by RHU via console';
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveRecord(
                $death,
                $reason,
                auth()->user()
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()])->withInput();
        }

        return redirect()->route('rhu.maternal-deaths.index')
            ->with('success', 'Maternal death case archived (retained for audit).');
    }

    // ─── Maternal Morbidities / Near-Miss Events (Feature K) ──────────

    public function morbidities()
    {
        $morbidities = MaternalMorbidity::with(['user', 'walkInPatient', 'purok'])
            ->latest('event_date')
            ->paginate(15);

        return view('rhu.morbidities.index', compact('morbidities'));
    }

    public function createMorbidity()
    {
        $users = User::where('role', 'user')->where('status', 'approved')->orderBy('first_name')->get();
        $walkIns = WalkInPatient::whereNull('converted_to_user_id')->orderBy('first_name')->get();
        $pregnancies = Pregnancy::orderBy('lmp', 'desc')->get();
        $puroks = Purok::orderBy('name')->get();
        $deaths = MaternalDeath::orderBy('death_date', 'desc')->get();

        return view('rhu.morbidities.create', compact('users', 'walkIns', 'pregnancies', 'puroks', 'deaths'));
    }

    public function storeMorbidity(Request $request)
    {
        $request->validate([
            'patient_type' => 'required|in:registered,walk_in',
            'user_id' => 'required_if:patient_type,registered|nullable|exists:users,id',
            'walk_in_patient_id' => 'required_if:patient_type,walk_in|nullable|exists:walk_in_patients,id',
            'pregnancy_id' => 'nullable|exists:pregnancies,id',
            'purok_id' => 'required|exists:puroks,id',
            'event_date' => 'required|date|before_or_equal:today',
            'event_time' => 'nullable',
            'complication_type' => 'required|in:severe_hemorrhage,eclampsia,severe_preeclampsia,sepsis,ruptured_uterus,severe_anemia,obstructed_labor,placenta_previa,placental_abruption,other',
            'place_of_event' => 'required|in:home,barangay_health_station,rhu,city_hospital,provincial_hospital,private_hospital,in_transit,other',
            'outcome' => 'required|in:survived_no_intervention,survived_with_intervention,transferred_to_higher_facility,died',
            'maternal_death_id' => 'required_if:outcome,died|nullable|exists:maternal_deaths,id',
            'description' => 'nullable|string|max:1000',
            'interventions_done' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $selectedPurok = Purok::findOrFail($request->purok_id);

        $morbidity = MaternalMorbidity::create([
            'user_id' => $request->patient_type === 'registered' ? $request->user_id : null,
            'walk_in_patient_id' => $request->patient_type === 'walk_in' ? $request->walk_in_patient_id : null,
            'pregnancy_id' => $request->pregnancy_id,
            'recorded_by_id' => auth()->id(),
            'purok_id' => $selectedPurok->id,
            'barangay' => $selectedPurok->barangay,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'complication_type' => $request->complication_type,
            'place_of_event' => $request->place_of_event,
            'outcome' => $request->outcome,
            'maternal_death_id' => $request->outcome === 'died' ? $request->maternal_death_id : null,
            'description' => $request->description,
            'interventions_done' => $request->interventions_done,
            'notes' => $request->notes,
            'review_status' => 'pending',
        ]);

        ActivityLog::log('create', "Recorded maternal morbidity near-miss event ({$request->complication_type}) for {$morbidity->patient_name}", $morbidity);

        return redirect()->route('rhu.morbidities.index')
            ->with('success', 'Maternal morbidity event recorded successfully.');
    }

    public function showMorbidity($id)
    {
        $morbidity = MaternalMorbidity::with(['user', 'walkInPatient', 'pregnancy', 'recordedBy', 'reviewedBy', 'purok', 'maternalDeath'])->findOrFail($id);
        return view('rhu.morbidities.show', compact('morbidity'));
    }

    public function editMorbidity($id)
    {
        $morbidity = MaternalMorbidity::findOrFail($id);
        $puroks = Purok::orderBy('name')->get();
        $deaths = MaternalDeath::orderBy('death_date', 'desc')->get();
        return view('rhu.morbidities.edit', compact('morbidity', 'puroks', 'deaths'));
    }

    public function updateMorbidity(Request $request, $id)
    {
        $morbidity = MaternalMorbidity::findOrFail($id);

        $request->validate([
            'purok_id' => 'required|exists:puroks,id',
            'event_date' => 'required|date|before_or_equal:today',
            'event_time' => 'nullable',
            'complication_type' => 'required|in:severe_hemorrhage,eclampsia,severe_preeclampsia,sepsis,ruptured_uterus,severe_anemia,obstructed_labor,placenta_previa,placental_abruption,other',
            'place_of_event' => 'required|in:home,barangay_health_station,rhu,city_hospital,provincial_hospital,private_hospital,in_transit,other',
            'outcome' => 'required|in:survived_no_intervention,survived_with_intervention,transferred_to_higher_facility,died',
            'maternal_death_id' => 'required_if:outcome,died|nullable|exists:maternal_deaths,id',
            'description' => 'nullable|string|max:1000',
            'interventions_done' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $selectedPurok = Purok::findOrFail($request->purok_id);

        $morbidity->update([
            'purok_id' => $selectedPurok->id,
            'barangay' => $selectedPurok->barangay,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'complication_type' => $request->complication_type,
            'place_of_event' => $request->place_of_event,
            'outcome' => $request->outcome,
            'maternal_death_id' => $request->outcome === 'died' ? $request->maternal_death_id : null,
            'description' => $request->description,
            'interventions_done' => $request->interventions_done,
            'notes' => $request->notes,
        ]);

        ActivityLog::log('update', "Updated maternal morbidity event details for {$morbidity->patient_name}", $morbidity);

        return redirect()->route('rhu.morbidities.show', $id)
            ->with('success', 'Morbidity details updated successfully.');
    }

    public function destroyMorbidity(Request $request, $id)
    {
        // Near-miss events feed FHSIS/MNCHN: archive only, never hard-delete.
        $request->validate(['reason' => 'nullable|string|max:1000']);

        $morbidity = MaternalMorbidity::findOrFail($id);
        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'Morbidity record archived by RHU via console';
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveRecord(
                $morbidity,
                $reason,
                auth()->user()
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()])->withInput();
        }

        return redirect()->route('rhu.morbidities.index')
            ->with('success', 'Morbidity record archived (retained for audit).');
    }

    // ─── Activity Logs (Feature 11) ───────────────────────────────────

    public function logs()
    {
        // RHU sees shared operational history including CHO actions on supply/deaths.
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(30);

        return view('rhu.logs.index', compact('logs'));
    }

    // ─── Reports (FHSIS/MNCHN) ───────────────────────────────────────

    public function reports()
    {
        // Aggregated MCH report statistics
        $totalPregnant = Pregnancy::active()->count();
        $totalPostpartum = Pregnancy::completed()->count();
        $totalBirths = Pregnancy::completed()->whereNotNull('ended_at')->count();
        $facilityDeliveries = Pregnancy::completed()
            ->whereIn('facility_delivery_place', ['barangay_health_station', 'rhu_birth_center', 'city_hospital', 'provincial_hospital', 'private_hospital'])
            ->count();
        
        $facilityBirthRate = $totalBirths > 0 ? round(($facilityDeliveries / $totalBirths) * 100, 1) : 0;

        // Get patients
        $search = request('search');
        $status = request('status');

        $query = User::where('role', 'user')
            ->where('status', 'approved')
            ->with(['pregnancies', 'healthRecords']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('barangay', 'like', '%' . $search . '%');
            });
        }

        $patients = $query->paginate(20);

        return view('rhu.reports.index', compact(
            'patients',
            'totalPregnant',
            'totalPostpartum',
            'totalBirths',
            'facilityBirthRate',
            'search',
            'status'
        ));
    }

    public function reportDetails($id)
    {
        $woman = User::where('role', 'user')->with(['pregnancies', 'checkups', 'healthRecords'])->findOrFail($id);
        return view('rhu.reports.details', compact('woman'));
    }

    public function exportCsv()
    {
        $patients = User::where('role', 'user')->where('status', 'approved')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rhu_patients_report_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($patients) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Patient Name', 'Age', 'Status', 'Email', 'Contact Number', 'Barangay']);

            foreach ($patients as $patient) {
                fputcsv($file, [
                    $patient->name,
                    $patient->age,
                    $patient->pregnancy_status,
                    $patient->email,
                    $patient->contact_number,
                    $patient->barangay ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $patients = User::where('role', 'user')->where('status', 'approved')->get();
        return view('rhu.reports.print', compact('patients'));
    }

    // ─── Women: Registration & Account Verification (RHU Admin only) ──

    public function createPatient()
    {
        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);

        return view('rhu.patients.create', compact('barangays'));
    }

    public function storePatient(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:2',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:255',
            'barangay' => 'required|string|max:255',
            'purok' => 'required|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before:today',
        ]);

        $purokId = \App\Models\Purok::resolveIdFromText($request->purok, $request->barangay);

        $woman = User::create([
            'first_name'     => $request->first_name,
            'middle_initial' => $request->middle_initial,
            'last_name'      => $request->last_name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'role'           => 'user',
            'address'        => $request->address ?: implode(', ', array_filter([$request->purok, $request->barangay, 'San Carlos City, Pangasinan'])),
            'barangay'       => $request->barangay,
            'purok_id'       => $purokId,
            'contact_number' => $request->contact_number,
            'date_of_birth'  => $request->date_of_birth,
            'status'         => 'approved',
        ]);

        ActivityLog::log('create', "Registered woman patient: {$woman->name}", $woman);

        return redirect()->route('rhu.pending-patients')
            ->with('success', 'Woman ' . $woman->name . ' has been successfully registered.');
    }

    public function pendingPatients()
    {
        $search = request('search');

        $query = User::where('role', 'user')->where('status', 'pending');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('barangay', 'like', '%' . $search . '%');
            });
        }

        $pending = $query->latest()->paginate(10)->withQueryString();

        // 1. Duplicate detection: fuzzy-match each queued registrant against
        // portal accounts + BHW field profiles so the reviewer can link
        // instead of creating a double record. Dismissed pairs stay hidden.
        $workflows = app(\App\Services\WorkflowService::class);
        $duplicates = [];
        foreach ($pending as $candidate) {
            $matches = collect($workflows->findPossibleDuplicates($candidate))
                ->reject(function ($m) use ($candidate) {
                    if ($m['kind'] === 'walk_in') {
                        return \App\Models\DuplicateReview::where('user_id', $candidate->id)
                            ->where('walk_in_patient_id', $m['id'])
                            ->where('status', '!=', \App\Models\DuplicateReview::STATUS_PENDING)
                            ->exists();
                    }

                    return \App\Models\DuplicateReview::where(function ($q) use ($candidate, $m) {
                        $q->where(function ($w) use ($candidate, $m) {
                            $w->where('user_id', $candidate->id)->where('matched_user_id', $m['id']);
                        })->orWhere(function ($w) use ($candidate, $m) {
                            $w->where('user_id', $m['id'])->where('matched_user_id', $candidate->id);
                        });
                    })->where('status', '!=', \App\Models\DuplicateReview::STATUS_PENDING)->exists();
                })->values()->all();
            if (!empty($matches)) {
                $duplicates[$candidate->id] = $matches;
            }
        }

        return view('rhu.pending-patients.index', compact('pending', 'duplicates'));
    }

    /**
     * Link a BHW field profile to the approved portal account (same person).
     * History on both sides is preserved — nothing is deleted or duplicated.
     */
    public function linkDuplicate(Request $request, $id)
    {
        $request->validate([
            'walk_in_patient_id' => 'required|exists:walk_in_patients,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $woman = User::where('role', 'user')->findOrFail($id);
        app(\App\Services\WorkflowService::class)->linkDuplicateWalkIn(
            (int) $request->input('walk_in_patient_id'),
            $woman->id,
            auth()->id(),
            $request->input('notes')
        );

        return redirect()->route('rhu.pending-patients')
            ->with('success', "Field profile linked to {$woman->name}. Histories merged without duplicates.");
    }

    /**
     * Mark a suggested match as reviewed (not the same person).
     */
    public function dismissDuplicate(Request $request, $id)
    {
        $request->validate([
            'kind' => 'required|in:user,walk_in',
            'match_id' => 'required|integer',
            'match_type' => 'required|string|max:50',
            'score' => 'required|integer|min:0|max:100',
        ]);

        $woman = User::where('role', 'user')->findOrFail($id);
        $kind = $request->input('kind');
        $matchId = (int) $request->input('match_id');

        // user_id is always the queued candidate; the matcher checks both
        // directions for user↔user pairs, so one row hides the pair.
        app(\App\Services\WorkflowService::class)->dismissDuplicate(
            $woman->id,
            $kind === 'walk_in' ? $matchId : null,
            $kind === 'walk_in' ? null : $matchId,
            null,
            auth()->id(),
            $request->input('match_type'),
            (int) $request->input('score')
        );

        return redirect()->route('rhu.pending-patients')
            ->with('success', 'Match dismissed — it will no longer be flagged.');
    }

    public function approvePatient($id)
    {
        $woman = User::where('role', 'user')->findOrFail($id);
        $woman->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        \App\Models\Notification::createNotification(
            $woman->id,
            'Your registration has been approved. You can now log in to ReproCare.',
            'Registration Approved',
            'success',
            route('user.dashboard')
        );

        ActivityLog::log('approve', "Approved woman registration: {$woman->name}", $woman);

        return redirect()->route('rhu.pending-patients')
            ->with('success', $woman->name . ' has been approved.');
    }

    public function rejectPatient(Request $request, $id)
    {
        // 1+5. Rejection Feedback Loop + Transparency: reason is mandatory,
        // the applicant is notified (in-app + SMS) BEFORE the account is removed.
        $request->validate(['reason' => 'required|string|max:500']);

        $woman = User::where('role', 'user')->findOrFail($id);
        $womanName = $woman->name;
        $reason = $request->input('reason');

        $woman->update(['status' => 'rejected', 'rejection_reason' => $reason]);

        app(\App\Services\WorkflowService::class)->notifyAction(
            $woman->id,
            '❌ Registration Not Approved',
            "Your ReproCare registration was not approved. Reason: {$reason} You may correct the details and register again.",
            'error',
            null,
            true
        );

        // Soft-delete only: the rejected application stays in archives for audit.
        $woman->delete();

        ActivityLog::log('reject', "Rejected woman registration: {$womanName}. Reason: {$reason}", $woman);

        return redirect()->route('rhu.pending-patients')
            ->with('success', $womanName . ' has been rejected, notified with the reason, and archived.');
    }

    // ─── BHW Monthly Reports Approval (Copied from MidwifeController) ──

    public function bhwReports()
    {
        $filter = request('filter', 'all');
        
        $query = BhwMonthlyReport::with(['bhw', 'submittedToPresidentBy', 'approvedByPresident', 'submittedToMidwifeBy']);
        
        if ($filter !== 'all') {
            $query->where('report_type', $filter);
        }
        
        $reports = $query->latest()->paginate(10);
        
        return view('rhu.bhw-reports.index', compact('reports', 'filter'));
    }

    public function bhwReportShow($id)
    {
        $report = BhwMonthlyReport::with(['bhw', 'submittedToPresidentBy', 'approvedByPresident', 'submittedToMidwifeBy', 'approvedByMidwife'])
            ->findOrFail($id);

        if ($report->report_type === 'health_records') {
            $baseQuery = HealthRecord::where('recorded_by_id', $report->bhw_id)
                ->whereMonth('created_at', $report->report_month)
                ->whereYear('created_at', $report->report_year)
                ->with('recordedBy', 'patient');

            $healthRecords = $baseQuery->paginate(20);
            $uniquePatients = $baseQuery->select('user_id')->distinct()->count();
            $riskDistribution = [
                'low' => HealthRecord::where('recorded_by_id', $report->bhw_id)->whereMonth('created_at', $report->report_month)->whereYear('created_at', $report->report_year)->where('risk_level', 'Low')->count(),
                'medium' => HealthRecord::where('recorded_by_id', $report->bhw_id)->whereMonth('created_at', $report->report_month)->whereYear('created_at', $report->report_year)->where('risk_level', 'Medium')->count(),
                'high' => HealthRecord::where('recorded_by_id', $report->bhw_id)->whereMonth('created_at', $report->report_month)->whereYear('created_at', $report->report_year)->where('risk_level', 'High')->count(),
            ];

            return view('rhu.bhw-reports.show', compact('report', 'healthRecords', 'uniquePatients', 'riskDistribution'));
        } else {
            $baseQuery = Pregnancy::whereMonth('created_at', $report->report_month)
                ->whereYear('created_at', $report->report_year)
                ->with('woman');

            $pregnancies = $baseQuery->paginate(20);
            $uniquePatients = $baseQuery->select('user_id')->distinct()->count();
            $riskDistribution = [
                'low' => Pregnancy::whereMonth('created_at', $report->report_month)->whereYear('created_at', $report->report_year)->where('is_high_risk', false)->count(),
                'high' => Pregnancy::whereMonth('created_at', $report->report_month)->whereYear('created_at', $report->report_year)->where('is_high_risk', true)->count(),
                'medium' => 0,
            ];

            return view('rhu.bhw-reports.show-pregnancies', compact('report', 'pregnancies', 'uniquePatients', 'riskDistribution'));
        }
    }

    public function bhwReportPrint($id)
    {
        $report = BhwMonthlyReport::findOrFail($id);
        return view('rhu.bhw-reports.print', compact('report'));
    }

    public function bhwReportApprove(Request $request, $id)
    {
        $report = BhwMonthlyReport::findOrFail($id);
        $report->approveByMidwife(auth()->id(), $request->input('notes'));

        // 5. Transparency: BHW + President learn the outcome.
        $workflows = app(\App\Services\WorkflowService::class);
        $workflows->notifyAction(
            (int) $report->bhw_id,
            '✅ Monthly Report Finally Approved',
            'Your report "' . ($report->title ?? "#{$report->id}") . '" was approved by the RHU Admin.',
            'success',
            route('bhw.reports.index')
        );
        if ($report->approved_by_president) {
            $workflows->notifyAction(
                (int) $report->approved_by_president,
                '✅ Report Approved by RHU',
                'Report "' . ($report->title ?? "#{$report->id}") . '" was approved by the RHU Admin.',
                'success',
                route('bhw-president.reports.index')
            );
        }

        ActivityLog::log('approve', "Approved BHW monthly report ID: {$report->id} submitted by BHW: {$report->bhw->name}", $report);

        return redirect()->route('rhu.bhw-reports.index')
            ->with('success', 'BHW Monthly Report approved successfully.');
    }

    public function bhwReportReject(Request $request, $id)
    {
        // 1+5. Mandatory reason → Needs Revision queue + notify BHW & President.
        $request->validate(['notes' => 'required|string|max:1000']);

        $report = BhwMonthlyReport::findOrFail($id);
        app(\App\Services\WorkflowService::class)->sendBackForRevision(
            'bhw_report',
            $report,
            auth()->id(),
            $request->input('notes')
        );

        ActivityLog::log('reject', "Rejected BHW monthly report ID: {$report->id} submitted by BHW: {$report->bhw->name}", $report);

        return redirect()->route('rhu.bhw-reports.index')
            ->with('success', 'Report sent back to the Needs Revision queue. The BHW was notified with your reason.');
    }

    public function bhwReportDestroy(Request $request, $id)
    {
        $report = BhwMonthlyReport::findOrFail($id);

        try {
            app(\App\Services\ArchiveService::class)->archiveRecord(
                $report,
                $request->input('reason', 'BHW monthly report removed by RHU'),
                auth()->user()
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()])->withInput();
        }

        return redirect()->route('rhu.bhw-reports.index')
            ->with('success', 'Report archived successfully (retained for audit).');
    }
}
