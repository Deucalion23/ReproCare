<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Checkup;
use App\Models\HealthRecord;
use App\Models\Notification;
use App\Models\StaffTransition;
use App\Models\User;
use App\Services\BhwPresidentAssignmentService;
use App\Services\SessionRevocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffTransitionController extends Controller
{
    /**
     * Transition console: pick a workflow, an outgoing account,
     * preview exactly what will move, then execute.
     */
    public function index(Request $request)
    {
        $type = $request->input('type', 'midwife_replace');
        if (!in_array($type, ['midwife_replace', 'president_replace', 'bhw_transfer'], true)) {
            $type = 'midwife_replace';
        }

        $outgoingRole = $this->outgoingRole($type);
        $outgoings = User::where('role', $outgoingRole)
            ->where('status', 'approved')
            ->where('id', '!=', auth()->id())
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'email', 'barangay']);

        $outgoing = null;
        $counts = null;
        $candidates = collect();
        $patients = collect();
        if ($request->filled('outgoing_id')) {
            $outgoing = User::where('role', $outgoingRole)
                ->where('status', 'approved')
                ->find($request->input('outgoing_id'));
        }
        if ($outgoing) {
            $counts = $this->previewCounts($type, $outgoing);
            $candidates = $this->incomingCandidates($type, $outgoing);
            if ($type === 'bhw_transfer') {
                $patients = User::where('role', 'user')
                    ->where('created_by_bhw_id', $outgoing->id)
                    ->orderBy('first_name')
                    ->get(['id', 'first_name', 'middle_initial', 'last_name', 'barangay']);
            }
        }

        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);
        $history = StaffTransition::with('outgoing')->latest()->take(10)->get();

        return view('rhu.staff-transitions.index', compact(
            'type', 'outgoings', 'outgoing', 'counts', 'candidates', 'patients', 'barangays', 'history'
        ));
    }

    public function execute(Request $request)
    {
        $type = $request->input('type');
        abort_unless(in_array($type, ['midwife_replace', 'president_replace', 'bhw_transfer'], true), 400);

        return match ($type) {
            'midwife_replace' => $this->executeMidwifeReplace($request),
            'president_replace' => $this->executePresidentReplace($request),
            default => $this->executeBhwTransfer($request),
        };
    }

    // ─── Midwife replacement ──────────────────────────────────────────

    protected function executeMidwifeReplace(Request $request)
    {
        $data = $request->validate([
            'outgoing_id' => 'required|exists:users,id',
            'incoming_mode' => 'required|in:existing,new',
            'incoming_user_id' => 'required_if:incoming_mode,existing|nullable|exists:users,id',
            'first_name' => 'required_if:incoming_mode,new|nullable|string|max:255',
            'middle_initial' => 'nullable|string|max:5',
            'last_name' => 'required_if:incoming_mode,new|nullable|string|max:255',
            'email' => 'required_if:incoming_mode,new|nullable|email|max:255|unique:users,email',
            'new_password' => 'required_if:incoming_mode,new|nullable|string|min:8',
            'date_of_birth' => 'required_if:incoming_mode,new|nullable|date|before:today',
            'gender' => 'required_if:incoming_mode,new|nullable|in:male,female',
            'contact_number' => 'required_if:incoming_mode,new|nullable|string|max:30',
            'license_number' => 'required_if:incoming_mode,new|nullable|string|max:60',
            'license_expiry' => 'nullable|date',
            'specialization' => 'nullable|string|max:150',
            'rhu_assignment' => 'nullable|string|max:255',
            'assigned_barangay' => 'nullable|string|max:255',
            'catchment_barangays' => 'nullable|array',
            'catchment_barangays.*' => 'string|max:255',
        ]);

        $outgoing = User::where('role', 'midwife')->where('status', 'approved')->findOrFail($data['outgoing_id']);
        abort_if($outgoing->id === auth()->id(), 422, 'You cannot replace your own account.');

        if (($data['incoming_mode'] ?? 'existing') === 'existing') {
            $incoming = User::where('role', 'midwife')->where('status', 'approved')->findOrFail($data['incoming_user_id']);
            abort_if($incoming->id === $outgoing->id, 422, 'Incoming and outgoing accounts must differ.');
        } else {
            $incoming = null;
        }

        $counts = [];
        DB::transaction(function () use ($data, $outgoing, &$incoming, &$counts) {
            if (!$incoming) {
                $incoming = User::create([
                    'first_name' => $data['first_name'],
                    'middle_initial' => $data['middle_initial'] ?? null,
                    'last_name' => $data['last_name'],
                    'email' => strtolower($data['email']),
                    'password' => Hash::make($data['new_password']),
                    'date_of_birth' => $data['date_of_birth'],
                    'gender' => $data['gender'],
                    'contact_number' => $data['contact_number'],
                    'license_number' => $data['license_number'],
                    'license_expiry' => $data['license_expiry'] ?? null,
                    'specialization' => $data['specialization'] ?? null,
                    'rhu_assignment' => $data['rhu_assignment'] ?? 'Rural Health Unit 1',
                    'assigned_barangay' => $data['assigned_barangay'] ?? $outgoing->assigned_barangay,
                    'catchment_barangays' => array_values($data['catchment_barangays'] ?? ($outgoing->catchment_barangays ?? [])),
                    'role' => 'midwife',
                    'status' => 'approved',
                    'registered_by_rhu_id' => auth()->id(),
                ]);
            }

            // Bulk clinical reassignment: only actionable items move.
            // Completed history and past sign-offs stay with the outgoing midwife.
            $counts['checkups'] = Checkup::where(function ($q) use ($outgoing) {
                    $q->where('scheduled_by_id', $outgoing->id)->orWhere('midwife_id', $outgoing->id);
                })
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->update(['scheduled_by_id' => $incoming->id, 'midwife_id' => $incoming->id]);
            $counts['health_records'] = HealthRecord::where('recorded_by_id', $outgoing->id)
                ->whereNotIn('workflow_status', ['completed', 'approved', 'closed'])
                ->update(['recorded_by_id' => $incoming->id]);
            $counts['patients'] = User::where('role', 'user')
                ->where('created_by_midwife_id', $outgoing->id)
                ->update(['created_by_midwife_id' => $incoming->id]);

            $outgoing->update(['status' => 'archived']);
            $outgoing->increment('pwa_cache_version');
            SessionRevocationService::terminateUserSessions((int) $outgoing->id);

            $this->recordTransition('midwife_replace', $outgoing, [$incoming], $counts,
                "Clinical reassignment: {$counts['checkups']} checkup(s), {$counts['health_records']} record review(s), {$counts['patients']} patient(s).");
            Notification::createNotification(
                $incoming->id,
                "You received {$counts['patients']} patient(s), {$counts['checkups']} pending checkup(s) and {$counts['health_records']} record review(s) from {$outgoing->name}.",
                'Clinical Cases Reassigned to You',
                'warning',
                route('midwife.dashboard')
            );
        });

        return redirect()->route('rhu.staff-transitions.index', ['type' => 'midwife_replace'])
            ->with('success', "Midwife replaced. {$incoming->name} now holds {$counts['patients']} patient(s) and {$counts['checkups']} pending checkup(s). {$outgoing->name} is archived with history intact.");
    }

    // ─── BHW President replacement ────────────────────────────────────

    protected function executePresidentReplace(Request $request)
    {
        $data = $request->validate([
            'outgoing_id' => 'required|exists:users,id',
            'disposition' => 'required|in:demote,inactive',
            'incoming_mode' => 'required|in:existing,new',
            'incoming_user_id' => 'required_if:incoming_mode,existing|nullable|exists:users,id',
            'first_name' => 'required_if:incoming_mode,new|nullable|string|max:255',
            'last_name' => 'required_if:incoming_mode,new|nullable|string|max:255',
            'email' => 'required_if:incoming_mode,new|nullable|email|max:255|unique:users,email',
            'new_password' => 'required_if:incoming_mode,new|nullable|string|min:8',
            'contact_number' => 'required_if:incoming_mode,new|nullable|string|max:30',
        ]);

        $outgoing = User::where('role', 'bhw_president')->where('status', 'approved')->findOrFail($data['outgoing_id']);
        $barangay = $outgoing->barangay;

        if (($data['incoming_mode'] ?? 'existing') === 'existing') {
            $incoming = User::where('role', 'bhw')->where('status', 'approved')->findOrFail($data['incoming_user_id']);
            abort_if($incoming->id === $outgoing->id, 422, 'Incoming and outgoing accounts must differ.');
            if (BhwPresidentAssignmentService::normalizeBarangay($incoming->barangay) !== BhwPresidentAssignmentService::normalizeBarangay($barangay)) {
                return back()->withErrors(['incoming_user_id' => 'Incoming BHW must serve the same barangay (' . $barangay . ').'])->withInput();
            }
        } else {
            $incoming = null;
        }

        $counts = [];
        DB::transaction(function () use ($data, $outgoing, $barangay, &$incoming, &$counts) {
            // Vacate the seat first so the one-president-per-barangay lock can re-validate.
            if (($data['disposition'] ?? 'demote') === 'demote') {
                $outgoing->update(['role' => 'bhw', 'status' => 'approved']);
            } else {
                $outgoing->update(['status' => 'inactive']);
                $outgoing->increment('pwa_cache_version');
            }

            if (!$incoming) {
                $incoming = User::create([
                    'first_name' => $data['first_name'],
                    'middle_initial' => $data['middle_initial'] ?? null,
                    'last_name' => $data['last_name'],
                    'email' => strtolower($data['email']),
                    'password' => Hash::make($data['new_password']),
                    'contact_number' => $data['contact_number'] ?? null,
                    'barangay' => BhwPresidentAssignmentService::displayBarangay($barangay),
                    'role' => 'bhw',
                    'status' => 'approved',
                ]);
            }
            // Enforce the lock on the way in (throws when the seat is taken).
            BhwPresidentAssignmentService::promoteToPresident($incoming->fresh(), $barangay);
            $incoming->refresh();

            // Route pending presidential reviews to the incoming president.
            $counts['reports'] = \App\Models\BhwMonthlyReport::where('submitted_to_president_by', $outgoing->id)
                ->where('submission_status', 'submitted_to_president')
                ->update(['submitted_to_president_by' => $incoming->id]);

            if (($data['disposition'] ?? 'demote') === 'inactive') {
                SessionRevocationService::terminateUserSessions((int) $outgoing->id);
            }

            $this->recordTransition('president_replace', $outgoing, [$incoming], $counts,
                "Disposition: {$data['disposition']}. {$counts['reports']} pending report review(s) routed.");
            Notification::createNotification(
                $incoming->id,
                "You are now BHW President of {$incoming->barangay}. {$counts['reports']} pending report review(s) were routed to you.",
                'Appointed BHW President',
                'success',
                route('bhw-president.dashboard')
            );
        });

        return redirect()->route('rhu.staff-transitions.index', ['type' => 'president_replace'])
            ->with('success', "Presidency transferred to {$incoming->name} for {$barangay}. {$counts['reports']} pending review(s) routed.");
    }

    // ─── BHW roster transfer ──────────────────────────────────────────

    protected function executeBhwTransfer(Request $request)
    {
        $data = $request->validate([
            'outgoing_id' => 'required|exists:users,id',
            'incoming_ids' => 'required|array|min:1',
            'incoming_ids.*' => 'exists:users,id',
            'scope' => 'required|in:all,selected',
            'patient_ids' => 'required_if:scope,selected|nullable|array',
            'patient_ids.*' => 'exists:users,id',
        ]);

        $outgoing = User::where('role', 'bhw')->where('status', 'approved')->findOrFail($data['outgoing_id']);
        $incoming = User::where('role', 'bhw')->where('status', 'approved')
            ->whereIn('id', $data['incoming_ids'])
            ->where('id', '!=', $outgoing->id)
            ->get();
        if ($incoming->isEmpty()) {
            return back()->withErrors(['incoming_ids' => 'Select at least one incoming BHW.'])->withInput();
        }
        $outKey = BhwPresidentAssignmentService::normalizeBarangay($outgoing->barangay);
        foreach ($incoming as $cand) {
            if (BhwPresidentAssignmentService::normalizeBarangay($cand->barangay) !== $outKey) {
                return back()->withErrors(['incoming_ids' => $cand->name . ' serves a different barangay. Transfers stay within ' . ($outgoing->barangay ?: 'the same barangay') . '.'])->withInput();
            }
        }

        $patientQuery = User::where('role', 'user')->where('created_by_bhw_id', $outgoing->id);
        if (($data['scope'] ?? 'all') === 'selected') {
            $patientQuery->whereIn('id', $data['patient_ids'] ?? []);
        }
        $patientIds = $patientQuery->pluck('id')->all();
        if (empty($patientIds)) {
            return back()->withErrors(['scope' => 'No patients match this transfer scope.'])->withInput();
        }

        $counts = ['patients' => 0, 'checkups' => 0, 'health_records' => 0, 'reports' => 0];
        DB::transaction(function () use ($data, $outgoing, $incoming, $patientIds, &$counts) {
            // Round-robin across incoming BHWs so no patient is left without a worker.
            $targets = $incoming->values();
            $buckets = [];
            foreach (array_values($patientIds) as $i => $pid) {
                $buckets[$targets[$i % $targets->count()]->id][] = $pid;
            }
            foreach ($buckets as $bhwId => $ids) {
                $counts['patients'] += User::whereIn('id', $ids)->update(['created_by_bhw_id' => $bhwId]);
                $counts['checkups'] += Checkup::whereIn('user_id', $ids)
                    ->where('scheduled_by_id', $outgoing->id)
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->update(['scheduled_by_id' => $bhwId]);
                $counts['health_records'] += HealthRecord::whereIn('user_id', $ids)
                    ->where('recorded_by_id', $outgoing->id)
                    ->whereNotIn('workflow_status', ['completed', 'approved', 'closed'])
                    ->update(['recorded_by_id' => $bhwId]);
            }
            // Pending authored reports follow the first incoming BHW.
            $counts['reports'] = \App\Models\BhwMonthlyReport::where('bhw_id', $outgoing->id)
                ->whereIn('submission_status', ['draft', 'submitted', 'submitted_to_midwife', 'submitted_to_president'])
                ->update(['bhw_id' => $targets[0]->id]);

            $outgoing->update(['status' => 'archived']);
            $outgoing->increment('pwa_cache_version');
            SessionRevocationService::terminateUserSessions((int) $outgoing->id);

            $names = $incoming->map->name->all();
            $this->recordTransition('bhw_transfer', $outgoing, $incoming->all(), $counts,
                "Roster: {$counts['patients']} patient(s), {$counts['checkups']} checkup(s), {$counts['health_records']} record(s), {$counts['reports']} report(s).");
            foreach ($incoming as $bhw) {
                Notification::createNotification(
                    $bhw->id,
                    "You received reassigned patients from {$outgoing->name}. Review your updated roster.",
                    'Patient Roster Transferred',
                    'warning',
                    route('bhw.patients')
                );
            }
        });

        return redirect()->route('rhu.staff-transitions.index', ['type' => 'bhw_transfer'])
            ->with('success', "Roster transferred: {$counts['patients']} patient(s) reassigned. {$outgoing->name} is archived; offline cache invalidated.");
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    protected function outgoingRole(string $type): string
    {
        return match ($type) {
            'president_replace' => 'bhw_president',
            'bhw_transfer' => 'bhw',
            default => 'midwife',
        };
    }

    protected function previewCounts(string $type, User $outgoing): array
    {
        return match ($type) {
            'president_replace' => [
                'reports' => \App\Models\BhwMonthlyReport::where('submitted_to_president_by', $outgoing->id)
                    ->where('submission_status', 'submitted_to_president')->count(),
            ],
            'bhw_transfer' => [
                'patients' => User::where('role', 'user')->where('created_by_bhw_id', $outgoing->id)->count(),
                'checkups' => Checkup::where('scheduled_by_id', $outgoing->id)->whereNotIn('status', ['completed', 'cancelled'])->count(),
                'health_records' => HealthRecord::where('recorded_by_id', $outgoing->id)->whereNotIn('workflow_status', ['completed', 'approved', 'closed'])->count(),
                'reports' => \App\Models\BhwMonthlyReport::where('bhw_id', $outgoing->id)->whereIn('submission_status', ['draft', 'submitted', 'submitted_to_midwife', 'submitted_to_president'])->count(),
            ],
            default => [
                'checkups' => Checkup::where(function ($q) use ($outgoing) {
                        $q->where('scheduled_by_id', $outgoing->id)->orWhere('midwife_id', $outgoing->id);
                    })->whereNotIn('status', ['completed', 'cancelled'])->count(),
                'health_records' => HealthRecord::where('recorded_by_id', $outgoing->id)->whereNotIn('workflow_status', ['completed', 'approved', 'closed'])->count(),
                'patients' => User::where('role', 'user')->where('created_by_midwife_id', $outgoing->id)->count(),
            ],
        };
    }

    protected function incomingCandidates(string $type, User $outgoing)
    {
        return match ($type) {
            'president_replace', 'bhw_transfer' => User::where('role', 'bhw')
                ->where('status', 'approved')
                ->where('id', '!=', $outgoing->id)
                ->where('barangay', $outgoing->barangay)
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'middle_initial', 'last_name', 'email', 'barangay', 'purok_id']),
            default => User::where('role', 'midwife')
                ->where('status', 'approved')
                ->where('id', '!=', $outgoing->id)
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'middle_initial', 'last_name', 'email']),
        };
    }

    /**
     * Immutable ROLE_CHANGE_EVENT: timestamp, executor, previous and new user ids.
     */
    protected function recordTransition(string $type, User $outgoing, array $incomingUsers, array $counts, string $note): StaffTransition
    {
        $incomingUsers = array_values($incomingUsers);
        $incomingIds = array_map(fn ($u) => $u->id, $incomingUsers);
        $incomingNames = implode(', ', array_map(fn ($u) => $u->name, $incomingUsers));

        $transition = StaffTransition::create([
            'type' => $type,
            'outgoing_user_id' => $outgoing->id,
            'incoming_user_ids' => $incomingIds,
            'performed_by_id' => auth()->id(),
            'outgoing_name' => $outgoing->name,
            'incoming_names' => $incomingNames,
            'counts' => $counts,
            'note' => $note,
        ]);

        ActivityLog::logProtected(
            'role_change',
            'ROLE_CHANGE_EVENT: executor_id=' . auth()->id()
                . ' previous_user_id=' . $outgoing->id . ' (' . $outgoing->name . ')'
                . ' newly_assigned_user_ids=[' . implode(',', $incomingIds) . '] (' . $incomingNames . ')'
                . ' on [' . now()->toDateTimeString() . ']. ' . $note,
            $transition
        );

        return $transition;
    }
}
