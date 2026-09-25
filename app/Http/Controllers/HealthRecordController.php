<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use App\Models\WalkInPatient;
use App\Models\User;
use App\Models\Pregnancy;
use App\Services\MaternalRiskService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HealthRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Index
    public function index()
    {
        $search = request('search');
        $riskLevel = request('risk_level', 'all');

        $healthRecords = HealthRecord::with(['woman', 'walkInPatient', 'recordedBy', 'bhwPresident'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('woman', function ($womanQuery) use ($search) {
                        $womanQuery->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })->orWhereHas('walkInPatient', function ($walkInQuery) use ($search) {
                        $walkInQuery->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('contact_number', 'like', '%' . $search . '%');
                    })->orWhere('bp', 'like', '%' . $search . '%')
                        ->orWhere('risk_level', 'like', '%' . $search . '%');
                });
            })
            ->when($riskLevel !== 'all', fn ($query) => $query->where('risk_level', $riskLevel))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('midwife.health-records.index', compact('healthRecords', 'riskLevel'));
    }

    // Create
    public function create($userId = null)
    {
        $woman = null;
        $women = null;

        if ($userId) {
            $woman = User::where('role', 'user')->findOrFail($userId);
        } else {
            $women = User::where('role', 'user')->where('status', 'approved')->get();
        }

        $walkInPatients = WalkInPatient::notConverted()->latest()->get();

        return view('midwife.health-records.create', compact('woman', 'women', 'walkInPatients'));
    }

    // Store
    public function store(Request $request)
    {
        $this->mergeLifestyleInputs($request);

        $request->validate([
            'patient_type' => ['required', 'in:registered,walk_in'],
            'user_id' => [
                'exclude_unless:patient_type,registered',
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'user')),
            ],
            'walk_in_patient_id' => ['exclude_unless:patient_type,walk_in', 'required', 'exists:walk_in_patients,id'],
            'bp_systolic' => 'required|integer|min:50|max:300',
            'bp_diastolic' => 'required|integer|min:30|max:200',
            'weight' => 'required|numeric|min:0|max:300',
            'height' => 'nullable|numeric|min:100|max:250',
            'heart_rate' => 'required|integer|min:0|max:250',
            'temperature' => 'required|numeric|min:30|max:45',
            'hemoglobin' => 'nullable|numeric|min:1|max:25',
            'gestational_age' => 'nullable|integer|min:1|max:45',
            'immunization_status' => 'nullable|string|max:255',
            'contraceptive_use' => 'nullable|string|max:255',
            'lab_results' => 'nullable|string|max:500',
            'smoking_status' => 'nullable|in:none,former,current',
            'alcohol_use' => 'nullable|in:none,former,current',
            'drug_use' => 'nullable|in:none,former,current',
            'lifestyle_notes' => 'nullable|string|max:1000',
            'risk_assessment_mode' => 'nullable|in:automatic,manual',
            'risk_level' => 'nullable|in:Low,Medium,High',
            'risk_notes' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'recommendations' => 'nullable|string|max:2000',
        ]);

        $riskData = $this->resolveRiskData($request);
        $womanId = $request->patient_type === 'registered' ? (int) $request->user_id : null;
        $walkInPatientId = $request->patient_type === 'walk_in' ? (int) $request->walk_in_patient_id : null;

        HealthRecord::create([
            'user_id' => $womanId,
            'walk_in_patient_id' => $walkInPatientId,
            'pregnancy_id' => HealthRecord::resolvePregnancyIdForWoman($womanId, Carbon::now()),
            'bp' => $this->combineBloodPressure($request),
            'weight' => $request->weight,
            'height' => $request->height,
            'bmi' => $riskData['bmi'],
            'heart_rate' => $request->heart_rate,
            'temperature' => $request->temperature,
            'hemoglobin' => $request->hemoglobin,
            'gestational_age' => $request->gestational_age,
            'immunization_status' => $request->immunization_status,
            'contraceptive_use' => $request->contraceptive_use,
            'lab_results' => $request->lab_results,
            'smoking_status' => $request->smoking_status,
            'alcohol_use' => $request->alcohol_use,
            'drug_use' => $request->drug_use,
            'lifestyle_notes' => $request->lifestyle_notes,
            'notes' => $request->notes,
            'risk_level' => $riskData['risk_level'],
            'risk_assessment_mode' => $request->input('risk_assessment_mode', 'automatic'),
            'risk_notes' => $request->risk_notes ?: ($riskData['auto_notes'] ?? null),
            'recommendations' => $request->recommendations,
            'recorded_by_id' => Auth::id(),
            'workflow_status' => 'accepted_by_midwife',
            'midwife_accepted_at' => now(),
        ]);

        $detectedRisk = $riskData['risk_level'];
        if ($womanId && $request->input('risk_assessment_mode', 'automatic') === 'automatic') {
            $riskService = new \App\Services\RiskAnalysisService();
            $detectedRisk = $riskService->evaluate($womanId);
        }
        if ($walkInPatientId && in_array($detectedRisk, ['Medium', 'High', 'Critical'], true)) {
            $walkIn = WalkInPatient::find($walkInPatientId);
            if ($walkIn) {
                try {
                    app(\App\Services\SmartNotificationService::class)->notifyWalkInRisk(
                        $walkIn,
                        "A {$detectedRisk} risk finding was recorded during your visit. Please follow your health worker's advice.",
                        $detectedRisk
                    );
                } catch (\Throwable $e) {
                }
            }
        }

        if ($womanId) {
            $this->syncPregnancyFromHealthRecord($womanId, $request->gestational_age, null, now());
        }

        $successMsg = 'Health record added successfully.';
        if ($detectedRisk !== 'Low') {
            $successMsg .= " ⚠️ {$detectedRisk} risk level detected — notifications sent.";
        }

        return redirect()->route('midwife.health-records.index')
            ->with('success', $successMsg);
    }

    // Show
    public function show($id)
    {
        $healthRecord = HealthRecord::with(['woman', 'walkInPatient', 'recordedBy', 'bhwPresident', 'pregnancy'])->findOrFail($id);
        return view('midwife.health-records.show', compact('healthRecord'));
    }

    // Edit
    public function edit($id)
    {
        $healthRecord = HealthRecord::with('woman')->findOrFail($id);
        $women = User::where('role', 'user')->where('status', 'approved')->orderBy('last_name')->get();
        $walkInPatients = WalkInPatient::notConverted()->latest()->get();

        return view('midwife.health-records.edit', compact('healthRecord', 'women', 'walkInPatients'));
    }

    // Update
    public function update(Request $request, $id)
    {
        $healthRecord = HealthRecord::findOrFail($id);
        if (!$request->filled('patient_type')) {
            $request->merge([
                'patient_type' => $healthRecord->walk_in_patient_id ? 'walk_in' : 'registered',
            ]);
        }

        $this->mergeLifestyleInputs($request);

        $request->validate([
            'patient_type' => ['required', 'in:registered,walk_in'],
            'user_id' => [
                'exclude_unless:patient_type,registered',
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'user')),
            ],
            'walk_in_patient_id' => ['exclude_unless:patient_type,walk_in', 'required', 'exists:walk_in_patients,id'],
            'bp_systolic' => 'required|integer|min:50|max:300',
            'bp_diastolic' => 'required|integer|min:30|max:200',
            'weight' => 'required|numeric|min:0|max:300',
            'height' => 'nullable|numeric|min:100|max:250',
            'heart_rate' => 'required|integer|min:0|max:250',
            'temperature' => 'required|numeric|min:30|max:45',
            'hemoglobin' => 'nullable|numeric|min:1|max:25',
            'gestational_age' => 'nullable|integer|min:1|max:45',
            'immunization_status' => 'nullable|string|max:255',
            'contraceptive_use' => 'nullable|string|max:255',
            'lab_results' => 'nullable|string|max:500',
            'smoking_status' => 'nullable|in:none,former,current',
            'alcohol_use' => 'nullable|in:none,former,current',
            'drug_use' => 'nullable|in:none,former,current',
            'lifestyle_notes' => 'nullable|string|max:1000',
            'risk_assessment_mode' => 'nullable|in:automatic,manual',
            'risk_level' => 'nullable|in:Low,Medium,High',
            'risk_notes' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'recommendations' => 'nullable|string|max:2000',
        ]);

        $riskData = $this->resolveRiskData($request);
        $womanId = $request->patient_type === 'registered' ? (int) $request->user_id : null;
        $walkInPatientId = $request->patient_type === 'walk_in' ? (int) $request->walk_in_patient_id : null;

        $healthRecord->update($request->only([
            'weight',
            'height',
            'heart_rate',
            'temperature',
            'hemoglobin',
            'gestational_age',
            'immunization_status',
            'contraceptive_use',
            'lab_results',
            'smoking_status',
            'alcohol_use',
            'drug_use',
            'lifestyle_notes',
            'notes',
            'recommendations',
        ]) + [
            'user_id' => $womanId,
            'walk_in_patient_id' => $walkInPatientId,
            'pregnancy_id' => HealthRecord::resolvePregnancyIdForWoman($womanId, $healthRecord->created_at),
            'bp' => $this->combineBloodPressure($request),
            'bmi' => $riskData['bmi'],
            'risk_level' => $riskData['risk_level'],
            'risk_assessment_mode' => $request->input('risk_assessment_mode', 'automatic'),
            'risk_notes' => $request->risk_notes ?: ($riskData['auto_notes'] ?? null),
        ]);

        if ($womanId && $request->input('risk_assessment_mode', 'automatic') === 'automatic') {
            $riskService = new \App\Services\RiskAnalysisService();
            $riskService->evaluate($womanId);
        }

        if ($healthRecord->workflow_status === 'submitted_to_midwife' && $womanId) {
            $healthRecord->update([
                'workflow_status' => 'accepted_by_midwife',
                'midwife_accepted_at' => now(),
            ]);
            $this->syncPregnancyFromHealthRecord($womanId, $request->gestational_age, null, $healthRecord->created_at);
        }

        return redirect()->route('midwife.health-records.index')
            ->with('success', 'Health record updated successfully. Risk level re-evaluated.');
    }

    // Delete
    public function destroy($id)
    {
        $healthRecord = HealthRecord::findOrFail($id);
        $healthRecord->delete();

        return redirect()->route('midwife.health-records.index')
            ->with('success', 'Health record archived successfully');
    }

    // Archive (soft-delete only — snapshot + audit reason are mandatory)
    public function archive(Request $request, $id)
    {
        $healthRecord = HealthRecord::findOrFail($id);
        $reason = trim((string) ($request->input('reason') ?? $request->input('archived_reason', '')));
        if ($reason === '') {
            $reason = 'Archived by midwife via console';
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveRecord($healthRecord, $reason, auth()->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()])->withInput();
        }

        return redirect()->route('midwife.health-records.index')
            ->with('success', 'Health record archived successfully (retained for audit).');
    }

    // Archived Records
    public function archived()
    {
        $archivedRecords = \Illuminate\Support\Facades\DB::table('health_records_archived')
            ->leftJoin('users as women', 'health_records_archived.user_id', '=', 'women.id')
            ->leftJoin('walk_in_patients', 'health_records_archived.walk_in_patient_id', '=', 'walk_in_patients.id')
            ->leftJoin('users as recorded_by', 'health_records_archived.recorded_by_id', '=', 'recorded_by.id')
            ->select(
                'health_records_archived.*',
                'women.first_name as woman_first_name',
                'women.middle_initial as woman_middle_initial',
                'women.last_name as woman_last_name',
                'walk_in_patients.first_name as walkin_first_name',
                'walk_in_patients.middle_initial as walkin_middle_initial',
                'walk_in_patients.last_name as walkin_last_name',
                'recorded_by.first_name as recorded_by_first_name',
                'recorded_by.middle_initial as recorded_by_middle_initial',
                'recorded_by.last_name as recorded_by_last_name'
            )
            ->orderBy('health_records_archived.archived_at', 'desc')
            ->paginate(15);

        return view('midwife.health-records.archived', compact('archivedRecords'));
    }

    public function acceptFromPresident($id)
    {
        $healthRecord = HealthRecord::with(['woman', 'walkInPatient', 'recordedBy', 'bhwPresident'])->findOrFail($id);

        $originalRecorderId = $healthRecord->recorded_by_id;
        $healthRecord->update([
            'workflow_status' => 'accepted_by_midwife',
            'midwife_accepted_at' => now(),
        ]);

        if ($healthRecord->user_id) {
            $this->syncPregnancyFromHealthRecord(
                $healthRecord->user_id,
                $healthRecord->gestational_age,
                null,
                $healthRecord->created_at
            );
        }

        if ($healthRecord->bhw_president_id) {
            \App\Models\Notification::createNotification(
                $healthRecord->bhw_president_id,
                'The health record for ' . $healthRecord->patient_name . ' was accepted by the midwife.',
                'Health Record Accepted',
                'success',
                route('bhw-president.health-records.index')
            );
        }

        if ($originalRecorderId) {
            \App\Models\Notification::createNotification(
                $originalRecorderId,
                'Your health record for ' . $healthRecord->patient_name . ' was accepted by the midwife.',
                'Health Record Accepted',
                'success',
                route('bhw.health-records.index')
            );
        }

        return redirect()->route('midwife.health-records.show', $healthRecord->id)
            ->with('success', 'Health record accepted and synced with pregnancy data.');
    }

    // Patient Health Records
    public function patientRecords($userId)
    {
        $woman = User::where('role', 'user')->findOrFail($userId);
        $healthRecords = $woman->healthRecords()
            ->with('recordedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('midwife.health-records.patient', compact('woman', 'healthRecords'));
    }

    private function resolveRiskData(Request $request): array
    {
        if ($request->input('patient_type', 'registered') !== 'registered') {
            return [
                'risk_level' => $request->input('risk_level', 'Low'),
                'bmi' => app(MaternalRiskService::class)->calculateBmi($request->weight, $request->height),
                'auto_notes' => null,
            ];
        }

        if ($request->input('risk_assessment_mode', 'automatic') === 'manual') {
            return [
                'risk_level' => $request->input('risk_level', 'Low'),
                'bmi' => app(MaternalRiskService::class)->calculateBmi($request->weight, $request->height),
                'auto_notes' => null,
            ];
        }

        $woman = User::where('role', 'user')->find($request->user_id);
        $assessment = app(MaternalRiskService::class)->assess([
            'age' => $woman?->age,
            'bp' => $this->combineBloodPressure($request),
            'weight' => $request->weight,
            'height' => $request->height,
            'smoking_status' => $request->smoking_status,
            'alcohol_use' => $request->alcohol_use,
            'drug_use' => $request->drug_use,
        ]);

        return [
            'risk_level' => $assessment['risk_level'],
            'bmi' => $assessment['bmi'],
            'auto_notes' => $assessment['reasons'] ? implode(' ', $assessment['reasons']) : null,
        ];
    }

    private function combineBloodPressure(Request $request): string
    {
        return $request->input('bp_systolic') . '/' . $request->input('bp_diastolic');
    }

    private function mergeLifestyleInputs(Request $request): void
    {
        $otherLifestyle = trim((string) $request->input('lifestyle_other', ''));
        $notes = trim((string) $request->input('lifestyle_notes', ''));

        if ($otherLifestyle !== '') {
            $notes = trim($notes !== '' ? $notes . "\nOther: " . $otherLifestyle : 'Other: ' . $otherLifestyle);
        }

        $request->merge([
            'smoking_status' => $request->boolean('lifestyle_smoking') ? 'current' : 'none',
            'alcohol_use' => $request->boolean('lifestyle_alcohol') ? 'current' : 'none',
            'drug_use' => $request->boolean('lifestyle_drugs') ? 'current' : 'none',
            'lifestyle_notes' => $notes !== '' ? $notes : null,
        ]);
    }

    private function syncPregnancyFromHealthRecord(int $womanId, $gestationalAge, ?string $obstetricHistory, $recordedAt): void
    {
        $hasPregnancyIndicators = !empty($gestationalAge)
            || str_contains(strtolower((string) $obstetricHistory), 'pregnan')
            || str_contains(strtolower((string) $obstetricHistory), 'gravida');

        if (!$hasPregnancyIndicators) {
            return;
        }

        $recordedAt = Carbon::parse($recordedAt);
        $weeks = max((int) $gestationalAge, 1);
        $estimatedLmp = $recordedAt->copy()->subWeeks($weeks);
        $estimatedEdd = $estimatedLmp->copy()->addDays(280);

        $pregnancy = Pregnancy::where('user_id', $womanId)->whereNull('ended_at')->latest('lmp')->first();

        if ($pregnancy) {
            $pregnancy->update([
                'lmp' => $pregnancy->lmp ?? $estimatedLmp,
                'edd' => $pregnancy->edd ?? $estimatedEdd,
                'aog' => $weeks,
            ]);
            return;
        }

        Pregnancy::create([
            'user_id' => $womanId,
            'lmp' => $estimatedLmp,
            'edd' => $estimatedEdd,
            'aog' => $weeks,
            'is_high_risk' => false,
        ]);
    }

    // Workflow Methods

    /**
     * Submit health record to BHW President for review
     */
    public function submitToBhwPresident($id)
    {
        $healthRecord = HealthRecord::findOrFail($id);
        
        // Check if user can submit (must be the recorder)
        $user = Auth::user();
        if ($healthRecord->recorded_by_id !== $user->id) {
            return back()->with('error', 'You can only submit records you created.');
        }

        $healthRecord->update([
            'workflow_status' => 'submitted_to_bhw_president',
            'submitted_to_bhw_president_at' => now(),
        ]);

        return back()->with('success', 'Health record submitted to BHW President for review.');
    }

    /**
     * BHW President review health record
     */
    public function bhwPresidentReview($id)
    {
        $healthRecord = HealthRecord::with(['woman', 'walkInPatient', 'recordedBy'])->findOrFail($id);
        
        return view('bhw-president.health-records.review', compact('healthRecord'));
    }

    /**
     * BHW President approve health record
     */
    public function bhwPresidentApprove(Request $request, $id)
    {
        $healthRecord = HealthRecord::findOrFail($id);
        
        $request->validate([
            'bhw_president_notes' => 'nullable|string|max:1000',
        ]);

        // Approved records join the midwife queue directly: the midwife
        // review screens filter on `submitted_to_midwife`, while the
        // approval itself stays recorded in the president metadata.
        $healthRecord->update([
            'workflow_status' => 'submitted_to_midwife',
            'submitted_to_midwife_at' => now(),
            'bhw_president_id' => Auth::id(),
            'bhw_president_reviewed_at' => now(),
            'bhw_president_notes' => $request->bhw_president_notes,
        ]);

        // 5. Transparency: tell the BHW their record moved forward.
        if ($healthRecord->recorded_by_id) {
            app(\App\Services\WorkflowService::class)->notifyAction(
                (int) $healthRecord->recorded_by_id,
                '✅ Health Record Approved',
                'Your health record for ' . $healthRecord->patient_name . ' was approved by the BHW President and moved to the midwife queue.',
                'success',
                route('bhw.health-records.index')
            );
        }

        return redirect()->route('bhw-president.health-records.index')
            ->with('success', 'Health record approved successfully.');
    }

    /**
     * BHW President reject health record → 1. Rejection Feedback Loop:
     * bounces to the BHW "Needs Revision" queue with a mandatory note
     * + 5. Transparency notification (in-app + SMS).
     */
    public function bhwPresidentReject(Request $request, $id)
    {
        $healthRecord = HealthRecord::findOrFail($id);

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        app(\App\Services\WorkflowService::class)->sendBackForRevision(
            'health_record',
            $healthRecord,
            Auth::id(),
            $request->input('rejection_reason')
        );

        return redirect()->route('bhw-president.health-records.index')
            ->with('success', 'Record sent back to the BHW Needs Revision queue. The BHW was notified with your note.');
    }
}
