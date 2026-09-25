<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use App\Models\Pregnancy;
use App\Models\User;
use App\Services\MaternalRiskService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PregnancyController extends Controller
{
    private const HEALTH_CONDITION_OPTIONS = [
        'pre_existing_hypertension' => 'Pre-existing hypertension',
        'diabetes' => 'Diabetes',
        'thyroid_disorders' => 'Thyroid disorders',
        'epilepsy' => 'Epilepsy',
        'renal_disease' => 'Renal disease',
        'blood_disorders' => 'Blood disorders',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    // Index
    public function index()
    {
        $search = request('search');
        $trimester = request('trimester', 'all');
        $riskLevel = request('risk_level', 'all');

        $query = app(\App\Services\PatientPresentation::class)->originalPatientsFirst(Pregnancy::with(['woman', 'walkInPatient']))
            ->orderByDesc('created_at');

        // Apply search if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('woman', function($womanQuery) use ($search) {
                    $womanQuery->where('first_name', 'like', '%' . $search . '%')
                             ->orWhere('last_name', 'like', '%' . $search . '%')
                             ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhereHas('walkInPatient', function($walkInQuery) use ($search) {
                    $walkInQuery->where('first_name', 'like', '%' . $search . '%')
                               ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            });
        }

        if ($riskLevel !== 'all') {
            $query->where('risk_level', $riskLevel);
        }

        if ($trimester !== 'all') {
            $query->whereNotNull('lmp');
            $now = now();

            if ($trimester === '1') {
                $query->whereDate('lmp', '>', $now->copy()->subWeeks(13)->toDateString());
            } elseif ($trimester === '2') {
                $query->whereBetween('lmp', [
                    $now->copy()->subWeeks(27)->toDateString(),
                    $now->copy()->subWeeks(13)->toDateString(),
                ]);
            } elseif ($trimester === '3') {
                $query->whereDate('lmp', '<', $now->copy()->subWeeks(27)->toDateString());
            }
        }

        $pregnancies = $query->paginate(10)->withQueryString();

        return view('midwife.pregnancies.index', compact('pregnancies', 'trimester', 'riskLevel'));
    }

    // Create
    public function create($userId = null)
    {
        $woman = null;
        if ($userId) {
            $woman = User::where('role', 'user')->findOrFail($userId);
            if ($woman->pregnancies()->active()->exists()) {
                return redirect()->route('midwife.pregnancies.index')
                    ->withErrors(['user_id' => $woman->name . ' already has an active pregnancy record. Complete or close it before adding another one.']);
            }
        }

        $women = User::where('role', 'user')->with(['pregnancies' => fn ($query) => $query->active()])
            ->orderBy('last_name')
            ->get();

        // Load walk-in patients
        $walkInPatients = \App\Models\WalkInPatient::with(['pregnancies' => fn ($query) => $query->active()])
            ->orderBy('last_name')
            ->get();

        $healthConditionOptions = self::HEALTH_CONDITION_OPTIONS;

        // Sequential-pregnancy carryover: suggest gravida/para from the
        // latest closed pregnancy so history is never retyped from zero.
        $suggested = ['gravida' => 1, 'para' => 0];
        if ($woman) {
            $suggested = app(\App\Services\WorkflowService::class)->suggestGravidaPara($woman->id);
        }

        return view('midwife.pregnancies.create', compact('women', 'woman', 'walkInPatients', 'healthConditionOptions', 'suggested'));
    }

    // Store
    public function store(Request $request)
    {
        $this->mergeLifestyleInputs($request);

        $request->validate([
            'patient_type' => 'required|in:registered,walk_in',
            'user_id' => [
                'nullable',
                'required_if:patient_type,registered',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'user')),
            ],
            'walk_in_patient_id' => [
                'nullable',
                'required_if:patient_type,walk_in',
                'exists:walk_in_patients,id',
            ],
            'lmp' => 'required|date|before:today',
            'gravida' => 'nullable|integer|min:1',
            'para' => 'nullable|integer|min:0',
            'gtpal_term' => 'nullable|integer|min:0',
            'gtpal_preterm' => 'nullable|integer|min:0',
            'gtpal_abortions' => 'nullable|integer|min:0',
            'gtpal_living_children' => 'nullable|integer|min:0',
            'health_conditions' => 'nullable|array',
            'health_conditions.*' => ['string', Rule::in(array_keys(self::HEALTH_CONDITION_OPTIONS))],
            'health_condition_other' => 'nullable|string|max:255',
            'bp_systolic' => 'nullable|integer|min:50|max:300',
            'bp_diastolic' => 'nullable|integer|min:30|max:200',
            'weight' => 'nullable|numeric|min:20|max:300',
            'height' => 'nullable|numeric|min:100|max:250',
            'smoking_status' => 'nullable|in:none,former,current',
            'alcohol_status' => 'nullable|in:none,former,current',
            'drug_use_status' => 'nullable|in:none,former,current',
            'lifestyle_notes' => 'nullable|string|max:1000',
            'risk_assessment_mode' => 'required|in:automatic,manual',
            'risk_level' => 'nullable|in:Low,Medium,High',
            'risk_notes' => 'nullable|string|max:1000',
            'notes' => 'nullable|string',
        ]);

        // Check for active pregnancy based on patient type
        if ($request->patient_type === 'registered') {
            $activePregnancyExists = Pregnancy::active()
                ->where('user_id', $request->user_id)
                ->exists();

            if ($activePregnancyExists) {
                return back()
                    ->withErrors(['user_id' => 'This patient already has an active pregnancy record.'])
                    ->withInput();
            }
        } elseif ($request->patient_type === 'walk_in') {
            $activePregnancyExists = Pregnancy::active()
                ->where('walk_in_patient_id', $request->walk_in_patient_id)
                ->exists();

            if ($activePregnancyExists) {
                return back()
                    ->withErrors(['walk_in_patient_id' => 'This walk-in patient already has an active pregnancy record.'])
                    ->withInput();
            }
        }

        $risk = $this->resolveRiskData($request);

        $pregnancyData = [
            'lmp' => $request->lmp,
            'risk_assessment_mode' => $request->risk_assessment_mode,
            'risk_level' => $risk['risk_level'],
            'risk_notes' => $request->risk_notes ?: ($risk['auto_notes'] ?? null),
            'is_high_risk' => $risk['is_high_risk'],
            'ended_at' => null,
            'notes' => $request->notes,
        ];

        // Add patient ID based on type
        if ($request->patient_type === 'registered') {
            $pregnancyData['user_id'] = $request->user_id;
        } elseif ($request->patient_type === 'walk_in') {
            $pregnancyData['walk_in_patient_id'] = $request->walk_in_patient_id;
        }

        $pregnancy = Pregnancy::create($pregnancyData);

        // Create or update maternal care target client with obstetric data
        if ($request->filled('gravida') || $request->filled('para') || $request->filled('gtpal_term')) {
            $maternalCareData = [
                'pregnancy_id' => $pregnancy->id,
                'gravida' => $request->gravida,
                'parity' => $request->para,
                'gtpal_term' => $request->input('gtpal_term', 0),
                'gtpal_preterm' => $request->input('gtpal_preterm', 0),
                'gtpal_abortions' => $request->input('gtpal_abortions', 0),
                'gtpal_living_children' => $request->input('gtpal_living_children', 0),
                'health_conditions' => $request->input('health_conditions', []),
                'health_condition_other' => $request->input('health_condition_other'),
                'pregnancy_outcome' => null,
                'outcome_details' => null,
            ];

            // Add user_id or walk_in_patient_id based on patient type
            if ($request->patient_type === 'registered') {
                $maternalCareData['user_id'] = $request->user_id;
            } elseif ($request->patient_type === 'walk_in') {
                $maternalCareData['user_id'] = null; // Walk-in patients don't have user_id
            }

            \App\Models\MaternalCareTargetClient::updateOrCreate(
                ['pregnancy_id' => $pregnancy->id],
                $maternalCareData
            );
        }

        $this->syncPregnancyHealthRecord($pregnancy, $request);

        return redirect()->route('midwife.pregnancies.index')
            ->with('success', 'Pregnancy record added successfully');
    }

    // Show
    public function show($id)
    {
        $pregnancy = Pregnancy::with([
            'woman',
            'walkInPatient',
            'healthRecords.recordedBy',
            'maternalCareTargetClient',
        ])->findOrFail($id);
        $healthConditionOptions = self::HEALTH_CONDITION_OPTIONS;

        $patient = $pregnancy->getPatientModel();
        $decisionSupport = $patient ? app(\App\Services\MaternalAnalyticsService::class)->patientSupport($patient, $pregnancy->id) : collect();
        return view('midwife.pregnancies.show', compact('pregnancy', 'healthConditionOptions', 'decisionSupport'));
    }

    // Edit
    public function edit($id)
    {
        $pregnancy = Pregnancy::findOrFail($id);
        $women = User::where('role', 'user')->with(['pregnancies' => fn ($query) => $query->active()])
            ->orderBy('last_name')
            ->get();
        $walkInPatients = \App\Models\WalkInPatient::with(['pregnancies' => fn ($query) => $query->active()])
            ->orderBy('last_name')
            ->get();

        $healthConditionOptions = self::HEALTH_CONDITION_OPTIONS;

        return view('midwife.pregnancies.edit', compact('pregnancy', 'women', 'walkInPatients', 'healthConditionOptions'));
    }

    // Update
    public function update(Request $request, $id)
    {
        $pregnancy = Pregnancy::findOrFail($id);

        // Archival lock: delivered pregnancies are read-only history.
        if ($pregnancy->is_locked) {
            return back()->withErrors([
                'locked' => 'This pregnancy is locked as historical record after delivery. Use Reopen with a reason to correct it, or start a new pregnancy for a new conception.',
            ])->withInput();
        }

        $this->mergeLifestyleInputs($request);

        $request->validate([
            'patient_type' => 'required|in:registered,walk_in',
            'user_id' => [
                'nullable',
                'required_if:patient_type,registered',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'user')),
            ],
            'walk_in_patient_id' => [
                'nullable',
                'required_if:patient_type,walk_in',
                'exists:walk_in_patients,id',
            ],
            'lmp' => 'required|date|before:today',
            'gravida' => 'nullable|integer|min:1',
            'para' => 'nullable|integer|min:0',
            'gtpal_term' => 'nullable|integer|min:0',
            'gtpal_preterm' => 'nullable|integer|min:0',
            'gtpal_abortions' => 'nullable|integer|min:0',
            'gtpal_living_children' => 'nullable|integer|min:0',
            'health_conditions' => 'nullable|array',
            'health_conditions.*' => ['string', Rule::in(array_keys(self::HEALTH_CONDITION_OPTIONS))],
            'health_condition_other' => 'nullable|string|max:255',
            'bp_systolic' => 'nullable|integer|min:50|max:300',
            'bp_diastolic' => 'nullable|integer|min:30|max:200',
            'weight' => 'nullable|numeric|min:20|max:300',
            'height' => 'nullable|numeric|min:100|max:250',
            'smoking_status' => 'nullable|in:none,former,current',
            'alcohol_status' => 'nullable|in:none,former,current',
            'drug_use_status' => 'nullable|in:none,former,current',
            'lifestyle_notes' => 'nullable|string|max:1000',
            'risk_assessment_mode' => 'required|in:automatic,manual',
            'risk_level' => 'nullable|in:Low,Medium,High',
            'risk_notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,completed',
            'outcome' => 'nullable|in:delivered,live_birth,miscarriage,stillbirth,terminated,other',
            'outcome_details' => 'nullable|string|max:1000',
            'notes' => 'nullable|string',
            // 4. Delivery hand-off fields (trigger postpartum auto-transition)
            'delivery_date' => 'nullable|date|before_or_equal:today',
            'delivery_time' => 'nullable|string|max:20',
            'facility_delivery_place' => 'nullable|string|max:255',
            'delivery_attendant' => 'nullable|string|max:255',
            'delivery_notes' => 'nullable|string|max:2000',
            'newborn_name' => 'nullable|string|max:255',
            'newborn_sex' => 'nullable|in:male,female',
            'birth_weight_kg' => 'nullable|numeric|min:0.3|max:8',
        ]);

        $duplicateActivePregnancy = Pregnancy::active()
            ->where('id', '!=', $pregnancy->id)
            ->when($request->patient_type === 'registered', fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->patient_type === 'walk_in', fn ($query) => $query->where('walk_in_patient_id', $request->walk_in_patient_id))
            ->exists();

        if ($duplicateActivePregnancy) {
            return back()
                ->withErrors([
                    $request->patient_type === 'registered' ? 'user_id' : 'walk_in_patient_id'
                        => 'This patient already has another active pregnancy record.',
                ])
                ->withInput();
        }

        $status = $request->input('status', 'active');
        $risk = $this->resolveRiskData($request);

        $pregnancy->update([
            'user_id' => $request->patient_type === 'registered' ? $request->user_id : null,
            'walk_in_patient_id' => $request->patient_type === 'walk_in' ? $request->walk_in_patient_id : null,
            'lmp' => $request->lmp,
            'risk_assessment_mode' => $request->risk_assessment_mode,
            'risk_level' => $risk['risk_level'],
            'risk_notes' => $request->risk_notes ?: ($risk['auto_notes'] ?? null),
            'is_high_risk' => $risk['is_high_risk'],
            'ended_at' => $status === 'completed' ? Carbon::today() : null,
            'notes' => $request->notes,
        ]);

        // Update maternal care target client with obstetric data
        if ($request->filled('gravida') || $request->filled('para') || $request->filled('gtpal_term')) {
            \App\Models\MaternalCareTargetClient::updateOrCreate(
                ['user_id' => $request->patient_type === 'registered' ? $request->user_id : null, 'pregnancy_id' => $pregnancy->id],
                [
                    'gravida' => $request->gravida,
                    'parity' => $request->para,
                    'gtpal_term' => $request->input('gtpal_term', 0),
                    'gtpal_preterm' => $request->input('gtpal_preterm', 0),
                    'gtpal_abortions' => $request->input('gtpal_abortions', 0),
                    'gtpal_living_children' => $request->input('gtpal_living_children', 0),
                    'health_conditions' => $request->input('health_conditions', []),
                    'health_condition_other' => $request->input('health_condition_other'),
                    'pregnancy_outcome' => $status === 'completed' ? $request->outcome : null,
                    'outcome_details' => $status === 'completed' ? $request->outcome_details : null,
                ]
            );
        }

        $pregnancy->refresh();
        $this->syncPregnancyHealthRecord($pregnancy, $request);

        // 4. Pregnancy→Postpartum auto-transition: delivered / live birth
        // closes the pregnancy and generates postpartum + newborn schedules.
        $outcome = strtolower((string) $request->input('outcome', ''));
        if ($status === 'completed' && in_array($outcome, ['delivered', 'live_birth'], true)) {
            app(\App\Services\WorkflowService::class)->handleDeliveryOutcome($pregnancy, [
                'delivery_date' => $request->input('delivery_date', now()->toDateString()),
                'delivery_time' => $request->input('delivery_time'),
                'facility_delivery_place' => $request->input('facility_delivery_place'),
                'delivery_attendant' => $request->input('delivery_attendant'),
                'delivery_notes' => $request->input('delivery_notes', $request->input('outcome_details')),
                'outcome' => $outcome,
                'newborn_name' => $request->input('newborn_name'),
                'newborn_sex' => $request->input('newborn_sex'),
                'birth_weight_kg' => $request->input('birth_weight_kg'),
                'recorded_by_id' => Auth::id(),
            ]);

            return redirect()->route('midwife.pregnancies.index')
                ->with('success', 'Delivery logged. Postpartum visits (24h / 1wk / 6wk) + newborn immunizations auto-created.');
        }

        return redirect()->route('midwife.pregnancies.index')
            ->with('success', 'Pregnancy record updated successfully');
    }

    // Delete
    public function destroy($id)
    {
        $pregnancy = Pregnancy::findOrFail($id);

        if ($pregnancy->is_locked) {
            return back()->withErrors([
                'locked' => 'Locked historical pregnancies cannot be archived. History is preserved permanently.',
            ]);
        }

        $pregnancy->delete();

        return redirect()->route('midwife.pregnancies.index')
            ->with('success', 'Pregnancy record archived successfully');
    }

    /**
     * Reopen a locked historical pregnancy for correction.
     * Reason is mandatory and the event is audit-logged.
     */
    public function reopen(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $pregnancy = Pregnancy::findOrFail($id);
        if (!$pregnancy->is_locked) {
            return back()->with('success', 'Pregnancy is not locked — no reopen needed.');
        }

        $pregnancy->update(['is_locked' => false]);

        \App\Models\ActivityLog::log(
            'update',
            "Reopened locked pregnancy #{$pregnancy->id} for {$pregnancy->patient_name}. Reason: {$request->input('reason')}",
            $pregnancy
        );

        return back()->with('success', 'Pregnancy reopened for correction. It will not re-lock automatically — lock it again after fixing.');
    }

    // Active Pregnancies
    public function active()
    {
        $search = request('search');

        $query = Pregnancy::active()
            ->with(['woman', 'walkInPatient'])
            ->orderBy('edd', 'asc');

        // Apply search if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('woman', function($womanQuery) use ($search) {
                    $womanQuery->where('first_name', 'like', '%' . $search . '%')
                             ->orWhere('last_name', 'like', '%' . $search . '%')
                             ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhereHas('walkInPatient', function($walkInQuery) use ($search) {
                    $walkInQuery->where('first_name', 'like', '%' . $search . '%')
                               ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            });
        }

        $pregnancies = $query->paginate(10);

        return view('midwife.pregnancies.active', compact('pregnancies'));
    }

    private function resolveRiskData(Request $request): array
    {
        $riskMode = $request->input('risk_assessment_mode', 'automatic');
        if ($riskMode === 'manual') {
            $manualLevel = $request->input('risk_level', 'Low');

            return [
                'risk_level' => $manualLevel,
                'is_high_risk' => $manualLevel === 'High',
                'bmi' => app(MaternalRiskService::class)->calculateBmi($request->weight, $request->height),
                'auto_notes' => null,
            ];
        }

        $woman = User::where('role', 'user')->find($request->user_id);
        $assessment = app(MaternalRiskService::class)->assess([
            'age' => $woman?->age,
            'blood_pressure' => $this->combineBloodPressure($request),
            'weight' => $request->weight,
            'height' => $request->height,
            'health_conditions' => $request->input('health_conditions', []),
            'health_condition_other' => $request->input('health_condition_other'),
            'smoking_status' => $request->smoking_status,
            'alcohol_status' => $request->alcohol_status,
            'drug_use_status' => $request->drug_use_status,
        ]);

        return [
            'risk_level' => $assessment['risk_level'],
            'is_high_risk' => $assessment['is_high_risk'],
            'bmi' => $assessment['bmi'],
            'auto_notes' => $assessment['reasons'] ? implode(' ', $assessment['reasons']) : null,
        ];
    }

    private function combineBloodPressure(Request $request): ?string
    {
        $systolic = $request->input('bp_systolic');
        $diastolic = $request->input('bp_diastolic');

        if ($systolic !== null && $systolic !== '' && $diastolic !== null && $diastolic !== '') {
            return $systolic . '/' . $diastolic;
        }

        return null;
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
            'alcohol_status' => $request->boolean('lifestyle_alcohol') ? 'current' : 'none',
            'drug_use_status' => $request->boolean('lifestyle_drugs') ? 'current' : 'none',
            'lifestyle_notes' => $notes !== '' ? $notes : null,
        ]);
    }

    private function syncPregnancyHealthRecord(Pregnancy $pregnancy, Request $request): void
    {
        $healthRecord = $pregnancy->healthRecords()
            ->where(function ($query) use ($pregnancy) {
                $query->whereBetween('created_at', [
                    $pregnancy->created_at->copy()->subMinutes(5),
                    $pregnancy->created_at->copy()->addMinutes(5),
                ])->orWhere(function ($generatedQuery) {
                    $generatedQuery->whereNull('heart_rate')
                        ->whereNull('temperature');
                });
            })
            ->where('recorded_by_id', Auth::id())
            ->oldest('created_at')
            ->first();

        // Calculate BMI from request data
        $bmi = app(MaternalRiskService::class)->calculateBmi($request->weight, $request->height);

        $payload = [
            'user_id' => $pregnancy->user_id,
            'pregnancy_id' => $pregnancy->id,
            'bp' => $this->combineBloodPressure($request),
            'weight' => $request->weight,
            'height' => $request->height,
            'bmi' => $bmi,
            'heart_rate' => $healthRecord?->heart_rate,
            'temperature' => $healthRecord?->temperature,
            'gestational_age' => $pregnancy->aog,
            'smoking_status' => $request->smoking_status ?? 'none',
            'alcohol_use' => $request->alcohol_status ?? 'none',
            'drug_use' => $request->drug_use_status ?? 'none',
            'lifestyle_notes' => $request->lifestyle_notes,
            'notes' => $pregnancy->notes,
            'risk_level' => $pregnancy->risk_level ?? 'Low',
            'risk_assessment_mode' => $pregnancy->risk_assessment_mode ?? 'automatic',
            'risk_notes' => $pregnancy->risk_notes,
            'recorded_by_id' => $healthRecord?->recorded_by_id ?? Auth::id(),
        ];

        if ($healthRecord) {
            $healthRecord->update($payload);
            return;
        }

        HealthRecord::create($payload);
    }

    // Workflow Methods

    /**
     * Submit pregnancy record to BHW President for review
     */
    public function submitToBhwPresident($id)
    {
        $pregnancy = Pregnancy::findOrFail($id);
        
        // Check if user can submit (must be the creator)
        $user = Auth::user();
        if ($user->role !== 'midwife') {
            return back()->with('error', 'Only midwives can submit pregnancy records for review.');
        }

        $pregnancy->update([
            'workflow_status' => 'submitted_to_bhw_president',
            'submitted_to_bhw_president_at' => now(),
        ]);

        return back()->with('success', 'Pregnancy record submitted to BHW President for review.');
    }

    /**
     * BHW President review pregnancy record
     */
    public function bhwPresidentReview($id)
    {
        $pregnancy = Pregnancy::with(['woman', 'healthRecords'])->findOrFail($id);
        
        return view('bhw-president.pregnancies.review', compact('pregnancy'));
    }

    /**
     * BHW President approve pregnancy record
     */
    public function bhwPresidentApprove(Request $request, $id)
    {
        $pregnancy = Pregnancy::findOrFail($id);
        
        $request->validate([
            'bhw_president_notes' => 'nullable|string|max:1000',
        ]);

        $pregnancy->update([
            'workflow_status' => 'bhw_president_approved',
            'bhw_president_reviewed_at' => now(),
            'bhw_president_notes' => $request->bhw_president_notes,
        ]);

        // 5. Transparency: notify the originating BHW.
        try {
            $submitter = $pregnancy->healthRecords()->oldest()->first()?->recorded_by_id
                ?? $pregnancy->woman?->created_by_bhw_id;
            if ($submitter) {
                app(\App\Services\WorkflowService::class)->notifyAction(
                    (int) $submitter,
                    '✅ Pregnancy Record Approved',
                    'Pregnancy record for ' . $pregnancy->patient_name . ' was approved by the BHW President.',
                    'success',
                    route('bhw.pregnancies.index')
                );
            }
        } catch (\Throwable $e) {
        }

        return redirect()->route('bhw-president.pregnancies.index')
            ->with('success', 'Pregnancy record approved successfully.');
    }

    /**
     * BHW President reject pregnancy record → 1. Rejection Feedback Loop
     * (Needs Revision queue + mandatory note + submitter notification).
     */
    public function bhwPresidentReject(Request $request, $id)
    {
        $pregnancy = Pregnancy::findOrFail($id);

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        app(\App\Services\WorkflowService::class)->sendBackForRevision(
            'pregnancy',
            $pregnancy,
            Auth::id(),
            $request->input('rejection_reason')
        );

        return redirect()->route('bhw-president.pregnancies.index')
            ->with('success', 'Record sent back to the Needs Revision queue. The submitter was notified with your note.');
    }
}
