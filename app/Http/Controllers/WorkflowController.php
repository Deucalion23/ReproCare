<?php

namespace App\Http\Controllers;

use App\Models\BhwMonthlyReport;
use App\Models\EmergencyAlert;
use App\Models\HealthRecord;
use App\Models\PatientTransfer;
use App\Models\Pregnancy;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

/**
 * Thin HTTP layer over WorkflowService for the five gap-closing flows:
 *  1. Correction & resubmission   2. Patient transfers
 *  3. Emergency fast-lane         4. Delivery auto-transition
 */
class WorkflowController extends Controller
{
    public function __construct(private WorkflowService $workflows)
    {
        $this->middleware('auth');
    }

    // ── 1. Correction & Resubmission ─────────────────────────────────────

    /** BHW edits vitals/typos then resubmits a bounced-back health record. */
    public function resubmitHealthRecord(Request $request, $id)
    {
        $record = HealthRecord::where('recorded_by_id', auth()->id())->findOrFail($id);

        $corrections = $request->validate([
            'bp_systolic' => 'sometimes|integer|min:50|max:300',
            'bp_diastolic' => 'sometimes|integer|min:30|max:200',
            'weight' => 'sometimes|numeric|min:0|max:300',
            'heart_rate' => 'sometimes|integer|min:0|max:250',
            'temperature' => 'sometimes|numeric|min:30|max:45',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (isset($corrections['bp_systolic'], $corrections['bp_diastolic'])) {
            $corrections['bp'] = $corrections['bp_systolic'] . '/' . $corrections['bp_diastolic'];
            unset($corrections['bp_systolic'], $corrections['bp_diastolic']);
        }

        $this->workflows->resubmitAfterRevision('health_record', $record, auth()->id(), $corrections);

        return back()->with('success', 'Corrections submitted. The record is back in the review queue with your fixes.');
    }

    /** BHW resubmits a bounced-back monthly report. */
    public function resubmitReport($id)
    {
        $report = BhwMonthlyReport::where('bhw_id', auth()->id())->findOrFail($id);
        $this->workflows->resubmitAfterRevision('bhw_report', $report, auth()->id());

        return redirect()->route('bhw.reports.index')
            ->with('success', 'Report corrected and resubmitted to the BHW President.');
    }

    /** BHW "Draft / Needs Revision" queue with reviewer notes highlighted. */
    public function revisionQueue()
    {
        $records = $this->workflows->revisionQueueForBhw(auth()->id(), 'health_record');
        $reports = $this->workflows->revisionQueueForBhw(auth()->id(), 'bhw_report');

        return view('bhw.revision-queue', compact('records', 'reports'));
    }

    /** Midwife sends a record back past the President straight to the BHW. */
    public function midwifeSendBack(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $record = HealthRecord::findOrFail($id);
        $this->workflows->sendBackForRevision('health_record', $record, auth()->id(), $request->input('reason'));

        return back()->with('success', 'Record sent back to the BHW Needs Revision queue with your note.');
    }

    // ── 2. Patient Relocation ────────────────────────────────────────────

    public function requestTransfer(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'walk_in_patient_id' => 'nullable|exists:walk_in_patients,id',
            'to_bhw_id' => 'nullable|exists:users,id',
            'to_purok_id' => 'nullable|exists:puroks,id',
            'to_barangay' => 'nullable|string|max:255',
            'reason' => 'required|string|max:1000',
        ]);

        $transfer = $this->workflows->requestPatientTransfer([
            ...$request->only(['user_id', 'walk_in_patient_id', 'to_bhw_id', 'to_purok_id', 'to_barangay', 'reason']),
            'requested_by_id' => auth()->id(),
        ]);

        return back()->with('success', "Transfer request #{$transfer->id} sent for approval. History is preserved; no duplicate created.");
    }

    public function approveTransfer(Request $request, $id)
    {
        $request->validate(['notes' => 'nullable|string|max:1000']);
        $this->authorizeApprover();
        $this->workflows->approvePatientTransfer((int) $id, auth()->id(), $request->input('notes'));
        \App\Models\ActivityLog::log('update', 'Transfer #'.$id.' approved by '.auth()->user()->role);

        return back()->with('success', 'Transfer approved. Patient tracking reassigned with full history intact.');
    }

    public function rejectTransfer(Request $request, $id)
    {
        $request->validate(['notes' => 'required|string|max:1000']);
        $this->authorizeApprover();
        $this->workflows->rejectPatientTransfer((int) $id, auth()->id(), $request->input('notes'));
        \App\Models\ActivityLog::log('update', 'Transfer #'.$id.' rejected by '.auth()->user()->role);

        return back()->with('success', 'Transfer rejected. The requesting BHW was notified with your reason.');
    }

    public function transfers()
    {
        $transfers = PatientTransfer::with(['fromBhw', 'toBhw', 'fromPurok', 'toPurok'])
            ->latest()->paginate(15);

        return view('workflow.transfers', compact('transfers'));
    }

    // ── 3. Emergency Fast-Lane ───────────────────────────────────────────

    /** "Panic button" — bypasses queues, alerts Midwife + RHU Admin + SMS. */
    public function emergency(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'walk_in_patient_id' => 'nullable|exists:walk_in_patients,id',
            'symptoms' => 'required|string|max:2000',
            'bp' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:2000',
            'location' => 'nullable|string|max:500',
            'barangay' => 'nullable|string|max:255',
            'purok_id' => 'nullable|exists:puroks,id',
        ]);

        $alert = $this->workflows->triggerEmergencyAlert([
            ...$request->only(['user_id', 'walk_in_patient_id', 'symptoms', 'bp', 'notes', 'location', 'barangay', 'purok_id']),
            'reported_by_id' => auth()->id(),
        ]);

        return back()->with('success', "🚨 Emergency alert #{$alert->id} sent instantly to the Midwife and RHU Admin (in-app + SMS). Referral #{$alert->referral_id} created.");
    }

    public function acknowledgeEmergency($id)
    {
        abort_unless(in_array(auth()->user()->role, ['midwife', 'rhu', 'cho', 'bhw_president'], true), 403);
        $alert = EmergencyAlert::findOrFail($id);
        $alert->acknowledge(auth()->id());
        \App\Models\ActivityLog::log('update', 'Emergency alert #'.$id.' acknowledged by '.auth()->user()->role);

        return back()->with('success', 'Emergency acknowledged. The reporting BHW was notified.');
    }

    // ── 4. Pregnancy → Postpartum Auto-Transition ────────────────────────

    public function logDelivery(Request $request, $pregnancyId)
    {
        $request->validate([
            'delivery_date' => 'required|date|before_or_equal:today',
            'delivery_time' => 'nullable|string|max:20',
            'facility_delivery_place' => 'nullable|string|max:255',
            'delivery_attendant' => 'nullable|string|max:255',
            'delivery_notes' => 'nullable|string|max:2000',
            'outcome' => 'nullable|string|max:50',
            'newborn_name' => 'nullable|string|max:255',
            'newborn_sex' => 'nullable|in:male,female',
            'birth_weight_kg' => 'nullable|numeric|min:0.3|max:8',
        ]);

        $pregnancy = Pregnancy::findOrFail($pregnancyId);
        $this->workflows->handleDeliveryOutcome($pregnancy, [
            ...$request->only(['delivery_date', 'delivery_time', 'facility_delivery_place', 'delivery_attendant', 'delivery_notes', 'outcome', 'newborn_name', 'newborn_sex', 'birth_weight_kg']),
            'recorded_by_id' => auth()->id(),
        ]);
        \App\Models\ActivityLog::log('create', 'Delivery logged for pregnancy #'.$pregnancyId.' by '.auth()->user()->role);

        return back()->with('success', 'Delivery logged. Postpartum visits (24h / 1wk / 6wk) + newborn immunizations auto-created.');
    }

    private function authorizeApprover(): void
    {
        abort_unless(in_array(auth()->user()->role, ['bhw_president', 'rhu', 'midwife'], true), 403);
    }
}
