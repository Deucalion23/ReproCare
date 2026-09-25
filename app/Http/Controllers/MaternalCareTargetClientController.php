<?php

namespace App\Http\Controllers;

use App\Models\MaternalCareTargetClient;
use App\Models\Pregnancy;
use App\Models\WalkInPatient;
use App\Models\Woman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class MaternalCareTargetClientController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'page-1');
        $search = trim((string) $request->get('search', ''));

        $women = Woman::where('status', 'approved')
            ->with([
                'purok:id,name',
                'maternalCareTargetClient.recordedBy',
                'pregnancies' => fn ($query) => $query->latest('created_at'),
                'checkups' => fn ($query) => $query->orderBy('scheduled_date'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('middle_initial', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%')
                        ->orWhere('barangay', 'like', '%' . $search . '%');
                });
            })
            ->orderBy(
                Woman::select('pregnancies.created_at')
                    ->join('pregnancies', 'pregnancies.user_id', '=', 'users.id')
                    ->whereColumn('pregnancies.user_id', 'users.id')
                    ->oldest('pregnancies.created_at')
                    ->limit(1)
            )
            ->paginate(12)
            ->withQueryString();

        $rows = $women->through(function (Woman $woman, int $index) use ($women) {
            $pregnancy = $woman->pregnancies->first();
            $checkups = $woman->checkups;
            $profile = $pregnancy
                ? \App\Models\MaternalCareTargetClient::where('user_id', $woman->id)->where('pregnancy_id', $pregnancy->id)->first()
                : $woman->maternalCareTargetClient;

            return [
                'number' => (($women->currentPage() - 1) * $women->perPage()) + $index + 1,
                'woman' => $woman,
                'profile' => $profile,
                'pregnancy' => $pregnancy,
                'age' => $woman->age,
                'prenatal' => $this->prenatalVisitGroups($checkups, $pregnancy, $profile),
            ];
        });

        $stats = [
            'totalClients' => Woman::where('status', 'approved')->count(),
            'activePregnancies' => Woman::where('status', 'approved')->whereHas('pregnancies', fn ($q) => $q->whereNull('ended_at'))->count(),
            'completedProfiles' => MaternalCareTargetClient::count(),
        ];

        return view('midwife.maternal-care-target-clients.index', [
            'clients' => $rows,
            'tab' => $tab,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    public function print(Request $request)
    {
        $tab = $request->get('tab', 'page-1');
        $search = trim((string) $request->get('search', ''));

        $women = Woman::where('status', 'approved')
            ->with([
                'purok:id,name',
                'maternalCareTargetClient.recordedBy',
                'pregnancies' => fn ($query) => $query->latest('created_at'),
                'checkups' => fn ($query) => $query->orderBy('scheduled_date'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('middle_initial', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%')
                        ->orWhere('barangay', 'like', '%' . $search . '%');
                });
            })
            ->orderBy(
                Woman::select('pregnancies.created_at')
                    ->join('pregnancies', 'pregnancies.user_id', '=', 'users.id')
                    ->whereColumn('pregnancies.user_id', 'users.id')
                    ->oldest('pregnancies.created_at')
                    ->limit(1)
            )
            ->get()
            ->values();

        $clientRows = $women->map(function (Woman $woman, int $index) {
            $pregnancy = $woman->pregnancies->first();
            $checkups = $woman->checkups;
            $profile = $pregnancy
                ? \App\Models\MaternalCareTargetClient::where('user_id', $woman->id)->where('pregnancy_id', $pregnancy->id)->first()
                : $woman->maternalCareTargetClient;

            return [
                'number' => $index + 1,
                'woman' => $woman,
                'profile' => $profile,
                'pregnancy' => $pregnancy,
                'age' => $woman->age,
                'prenatal' => $this->prenatalVisitGroups($checkups, $pregnancy, $profile),
            ];
        });

        $clients = new LengthAwarePaginator(
            $clientRows,
            $clientRows->count(),
            max($clientRows->count(), 1),
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $stats = [
            'totalClients' => $clientRows->count(),
            'activePregnancies' => $clientRows->filter(fn ($client) => $client['pregnancy'] && !$client['pregnancy']->ended_at)->count(),
            'completedProfiles' => $clientRows->filter(fn ($client) => !empty($client['profile']))->count(),
        ];

        return view('midwife.maternal-care-target-clients.index', compact('clients', 'tab', 'search', 'stats') + ['printMode' => true]);
    }

    public function edit(Woman $woman, $pregnancyId = null)
    {
        $woman->load([
            'purok:id,name',
            'pregnancies' => fn ($query) => $query->latest('created_at'),
            'maternalCareTargetClient',
        ]);

        $pregnancy = $pregnancyId
            ? $woman->pregnancies->where('id', $pregnancyId)->first()
            : $woman->pregnancies->first();

        $profile = $pregnancy
            ? \App\Models\MaternalCareTargetClient::where('user_id', $woman->id)->where('pregnancy_id', $pregnancy->id)->first()
            : $woman->maternalCareTargetClient;

        return view('midwife.maternal-care-target-clients.edit', [
            'woman' => $woman,
            'pregnancy' => $pregnancy,
            'profile' => $profile,
        ]);
    }

    public function update(Request $request, Woman $woman, $pregnancyId = null)
    {
        $validated = $request->validate([
            'pregnancy_id' => ['nullable', 'exists:pregnancies,id'],
            'date_of_registration' => ['nullable', 'date'],
            'family_serial_no' => ['nullable', 'string', 'max:100'],
            'gravida' => ['nullable', 'integer', 'min:0'],
            'parity' => ['nullable', 'integer', 'min:0'],
            'fim_status' => ['nullable', 'boolean'],
            'nutritional_assessment_status' => ['nullable', Rule::in(['low', 'normal', 'high'])],
            'deworming_date' => ['nullable', 'date'],
            'health_conditions' => ['nullable', 'string'],
            'health_condition_other' => ['nullable', 'string'],
            'pregnancy_terminated_date' => ['nullable', 'date'],
            'pregnancy_outcome' => ['nullable', Rule::in(['ft', 'pt', 'fd', 'ab'])],
            'outcome_details' => ['nullable', 'string'],
            'pregnancy_outcome_sex' => ['nullable', Rule::in(['M', 'F'])],
            'delivery_type' => ['nullable', Rule::in(['cs', 'vd'])],
            'birth_weight_category' => ['nullable', Rule::in(['low', 'normal', 'unknown'])],
            'facility_delivery_type' => ['nullable', 'string', 'max:255'],
            'facility_bemonc_capable' => ['nullable', 'string', 'max:255'],
            'facility_ownership' => ['nullable', Rule::in(['public', 'private'])],
            'non_health_facility_code' => ['nullable', 'string', 'max:255'],
            'birth_attendant_code' => ['nullable', 'string', 'max:50'],
            'delivery_remarks' => ['nullable', 'string', 'max:1000'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_time' => ['nullable', 'date_format:H:i'],
            'gtpal_term' => ['nullable', 'integer', 'min:0'],
            'gtpal_preterm' => ['nullable', 'integer', 'min:0'],
            'gtpal_abortions' => ['nullable', 'integer', 'min:0'],
            'gtpal_living_children' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['fim_status'] = $request->boolean('fim_status');

        // Convert health_conditions string to array if needed
        if (!empty($validated['health_conditions']) && is_string($validated['health_conditions'])) {
            $decoded = json_decode($validated['health_conditions'], true);
            $validated['health_conditions'] = $decoded !== null ? $decoded : [$validated['health_conditions']];
        }

        // Set default values for GTPAL fields to prevent null constraint violations
        $validated['gtpal_term'] = $validated['gtpal_term'] ?? 0;
        $validated['gtpal_preterm'] = $validated['gtpal_preterm'] ?? 0;
        $validated['gtpal_abortions'] = $validated['gtpal_abortions'] ?? 0;
        $validated['gtpal_living_children'] = $validated['gtpal_living_children'] ?? 0;

        // Use the provided pregnancy_id or get/create the most recent pregnancy.
        $targetPregnancyId = $validated['pregnancy_id'] ?? $pregnancyId ?? $woman->pregnancies()->latest()->first()?->id;

        if (!$targetPregnancyId) {
            $seedDate = !empty($validated['date_of_registration'])
                ? Carbon::parse($validated['date_of_registration'])
                : Carbon::today();

            if ($seedDate->isFuture()) {
                $seedDate = Carbon::today();
            }

            $pregnancy = Pregnancy::create([
                'user_id' => $woman->id,
                'lmp' => $seedDate->toDateString(),
                'notes' => 'Auto-generated from maternal care target client record.',
            ]);

            $targetPregnancyId = $pregnancy->id;
        }

        $validated['user_id'] = $woman->id;
        $validated['pregnancy_id'] = $targetPregnancyId;
        $validated['recorded_by_id'] = auth()->id();

        MaternalCareTargetClient::updateOrCreate(
            ['user_id' => $woman->id, 'pregnancy_id' => $targetPregnancyId],
            $validated
        );

        return redirect()
            ->route('midwife.maternal-care-target-clients.index')
            ->with('success', 'Maternal care target client record updated successfully.');
    }

    /**
     * Prenatal visit slots per FHSIS TCL distribution: 1 in the 1st
     * trimester, 2 in the 2nd, 5 in the 3rd. Slot 1 of each trimester keeps
     * its manually recorded date when present; the rest (and empty slot 1s)
     * fall back to actual checkups dated inside that trimester window.
     *
     * @return array{first: mixed, second: mixed, third: mixed, visits: array<string, array>}
     */
    private function prenatalVisitGroups($checkups, $pregnancy, $profile): array
    {
        $auto = [
            1 => $this->findTrimesterCheckupDates($checkups, $pregnancy, 1, 1),
            2 => $this->findTrimesterCheckupDates($checkups, $pregnancy, 2, 2),
            3 => $this->findTrimesterCheckupDates($checkups, $pregnancy, 3, 5),
        ];

        $first = $profile?->prenatal_first_trimester_date ?? ($auto[1][0] ?? null);
        $second = $profile?->prenatal_second_trimester_date ?? ($auto[2][0] ?? null);
        $third = $profile?->prenatal_third_trimester_date ?? ($auto[3][0] ?? null);

        return [
            'first' => $first,
            'second' => $second,
            'third' => $third,
            'visits' => [
                'first' => [$first],
                'second' => [$second, ($auto[2][1] ?? null)],
                'third' => [$third, ($auto[3][1] ?? null), ($auto[3][2] ?? null), ($auto[3][3] ?? null), ($auto[3][4] ?? null)],
            ],
        ];
    }

    private function findTrimesterCheckupDate($checkups, $pregnancy, int $trimester)
    {
        return $this->findTrimesterCheckupDates($checkups, $pregnancy, $trimester, 1)[0] ?? null;
    }

    /** All matching checkup dates in a trimester window, oldest first. */
    private function findTrimesterCheckupDates($checkups, $pregnancy, int $trimester, int $limit): array
    {
        if (!$pregnancy?->lmp) {
            return [];
        }

        $weeks = match ($trimester) {
            1 => [0, 13],
            2 => [14, 27],
            default => [28, 45],
        };

        return $checkups->filter(function ($checkup) use ($pregnancy, $weeks) {
            if (!$checkup->scheduled_date) {
                return false;
            }

            $gestationWeeks = $pregnancy->lmp->diffInWeeks($checkup->scheduled_date, false);
            return $gestationWeeks >= $weeks[0] && $gestationWeeks <= $weeks[1];
        })->sortBy('scheduled_date')->take($limit)->map->scheduled_date->values()->all();
    }

    public function create()
    {
        return view('midwife.maternal-care-target-clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'max:10'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'purok_id' => ['nullable', 'exists:puroks,id'],
            'date_of_registration' => ['nullable', 'date'],
            'family_serial_no' => ['nullable', 'string', 'max:100'],
            'lmp' => ['nullable', 'date'],
            'edd' => ['nullable', 'date'],
            'gravida' => ['nullable', 'integer', 'min:0'],
            'parity' => ['nullable', 'integer', 'min:0'],
            'prenatal_first_trimester_date' => ['nullable', 'date'],
            'prenatal_second_trimester_date_1' => ['nullable', 'date'],
            'prenatal_second_trimester_date_2' => ['nullable', 'date'],
            'prenatal_third_trimester_date_1' => ['nullable', 'date'],
            'prenatal_third_trimester_date_2' => ['nullable', 'date'],
            'prenatal_third_trimester_date_3' => ['nullable', 'date'],
            'prenatal_third_trimester_date_4' => ['nullable', 'date'],
            'prenatal_third_trimester_date_5' => ['nullable', 'date'],
            'td1_date' => ['nullable', 'date'],
            'td2_date' => ['nullable', 'date'],
            'td3_date' => ['nullable', 'date'],
            'td4_date' => ['nullable', 'date'],
            'td5_date' => ['nullable', 'date'],
            'fim_status' => ['nullable', 'boolean'],
            'iron_folic_first_visit_date' => ['nullable', 'date'],
            'iron_folic_first_visit_tablets' => ['nullable', 'integer', 'min:0'],
            'iron_folic_second_visit_date' => ['nullable', 'date'],
            'iron_folic_second_visit_tablets' => ['nullable', 'integer', 'min:0'],
            'iron_folic_third_visit_date' => ['nullable', 'date'],
            'iron_folic_third_visit_tablets' => ['nullable', 'integer', 'min:0'],
            'iron_folic_fourth_visit_date' => ['nullable', 'date'],
            'iron_folic_fourth_visit_tablets' => ['nullable', 'integer', 'min:0'],
            'calcium_second_visit_date' => ['nullable', 'date'],
            'calcium_second_visit_tablets' => ['nullable', 'integer', 'min:0'],
            'calcium_third_visit_date' => ['nullable', 'date'],
            'calcium_third_visit_tablets' => ['nullable', 'integer', 'min:0'],
            'calcium_fourth_visit_date' => ['nullable', 'date'],
            'calcium_fourth_visit_tablets' => ['nullable', 'integer', 'min:0'],
            'iodine_date' => ['nullable', 'date'],
            'iodine_capsules_given' => ['nullable', 'integer', 'min:0'],
            'nutritional_assessment_status' => ['nullable', Rule::in(['low', 'normal', 'high'])],
            'deworming_date' => ['nullable', 'date'],
            'syphilis_screening_date' => ['nullable', 'date'],
            'syphilis_screening_result' => ['nullable', Rule::in(['positive', 'negative'])],
            'hepatitis_b_screening_date' => ['nullable', 'date'],
            'hepatitis_b_screening_result' => ['nullable', Rule::in(['positive', 'negative'])],
            'hiv_screening_date' => ['nullable', 'date'],
            'gestational_diabetes_screening_date' => ['nullable', 'date'],
            'gestational_diabetes_result' => ['nullable', Rule::in(['positive', 'negative'])],
            'cbc_screening_date' => ['nullable', 'date'],
            'cbc_anemia_status' => ['nullable', Rule::in(['with_anemia', 'without_anemia'])],
            'cbc_given_iron' => ['nullable', 'boolean'],
            'pregnancy_terminated_date' => ['nullable', 'date'],
            'pregnancy_outcome' => ['nullable', Rule::in(['ft', 'pt', 'fd', 'ab'])],
            'pregnancy_outcome_sex' => ['nullable', Rule::in(['M', 'F'])],
            'delivery_type' => ['nullable', Rule::in(['cs', 'vd'])],
            'birth_weight_category' => ['nullable', Rule::in(['low', 'normal', 'unknown'])],
            'facility_delivery_type' => ['nullable', 'string', 'max:255'],
            'facility_bemonc_capable' => ['nullable', 'string', 'max:255'],
            'facility_ownership' => ['nullable', Rule::in(['public', 'private'])],
            'non_health_facility_code' => ['nullable', 'string', 'max:255'],
            'birth_attendant_code' => ['nullable', 'string', 'max:50'],
            'delivery_remarks' => ['nullable', 'string', 'max:1000'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_time' => ['nullable', 'date_format:H:i'],
            'postpartum_within_24_hours_date' => ['nullable', 'date'],
            'postpartum_within_7_days_date' => ['nullable', 'date'],
            'postpartum_iron_first_month' => ['nullable', 'string', 'max:255'],
            'postpartum_iron_second_month' => ['nullable', 'string', 'max:255'],
            'postpartum_iron_third_month' => ['nullable', 'string', 'max:255'],
            'vitamin_a_date' => ['nullable', 'date'],
            'postpartum_remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $selectedPurok = \App\Models\Purok::find($validated['purok_id'] ?? null);
        $barangay = $selectedPurok?->barangay;

        // Create the woman first
        $woman = Woman::create([
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'],
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'address' => $validated['address'],
            'barangay' => $barangay,
            'purok_id' => $validated['purok_id'],
            'status' => 'approved',
        ]);

        WalkInPatient::create([
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'address' => $validated['address'] ?? null,
            'barangay' => $barangay,
            'purok_id' => $validated['purok_id'] ?? null,
            'contact_number' => $validated['contact_number'] ?? null,
            'notes' => 'Created from maternal care target client by midwife.',
        ]);

        // Create pregnancy if LMP is provided
        if ($validated['lmp']) {
            $pregnancy = $woman->pregnancies()->create([
                'lmp' => $validated['lmp'],
                'edd' => $validated['edd'],
                'is_active' => true,
            ]);
        }

        // Create the maternal care target client record
        $validated['fim_status'] = $request->boolean('fim_status');
        $validated['cbc_given_iron'] = $request->boolean('cbc_given_iron');

        // Get the pregnancy ID (use the one just created or the most recent)
        $pregnancyId = $pregnancy?->id ?? $woman->pregnancies()->latest()->first()?->id;

        if (!$pregnancyId) {
            return redirect()
                ->route('midwife.maternal-care-target-clients.index')
                ->with('error', 'No pregnancy found for this woman. Please create a pregnancy first.');
        }

        MaternalCareTargetClient::create([
            'user_id' => $woman->id,
            'pregnancy_id' => $pregnancyId,
            'date_of_registration' => $validated['date_of_registration'],
            'family_serial_no' => $validated['family_serial_no'],
            'gravida' => $validated['gravida'],
            'parity' => $validated['parity'],
            'td1_date' => $validated['td1_date'],
            'td2_date' => $validated['td2_date'],
            'td3_date' => $validated['td3_date'],
            'td4_date' => $validated['td4_date'],
            'td5_date' => $validated['td5_date'],
            'fim_status' => $validated['fim_status'],
            'iron_folic_first_visit_date' => $validated['iron_folic_first_visit_date'],
            'iron_folic_first_visit_tablets' => $validated['iron_folic_first_visit_tablets'],
            'iron_folic_second_visit_date' => $validated['iron_folic_second_visit_date'],
            'iron_folic_second_visit_tablets' => $validated['iron_folic_second_visit_tablets'],
            'iron_folic_third_visit_date' => $validated['iron_folic_third_visit_date'],
            'iron_folic_third_visit_tablets' => $validated['iron_folic_third_visit_tablets'],
            'iron_folic_fourth_visit_date' => $validated['iron_folic_fourth_visit_date'],
            'iron_folic_fourth_visit_tablets' => $validated['iron_folic_fourth_visit_tablets'],
            'calcium_second_visit_date' => $validated['calcium_second_visit_date'],
            'calcium_second_visit_tablets' => $validated['calcium_second_visit_tablets'],
            'calcium_third_visit_date' => $validated['calcium_third_visit_date'],
            'calcium_third_visit_tablets' => $validated['calcium_third_visit_tablets'],
            'calcium_fourth_visit_date' => $validated['calcium_fourth_visit_date'],
            'calcium_fourth_visit_tablets' => $validated['calcium_fourth_visit_tablets'],
            'iodine_date' => $validated['iodine_date'],
            'iodine_capsules_given' => $validated['iodine_capsules_given'],
            'nutritional_assessment_status' => $validated['nutritional_assessment_status'],
            'deworming_date' => $validated['deworming_date'],
            'syphilis_screening_date' => $validated['syphilis_screening_date'],
            'syphilis_screening_result' => $validated['syphilis_screening_result'],
            'hepatitis_b_screening_date' => $validated['hepatitis_b_screening_date'],
            'hepatitis_b_screening_result' => $validated['hepatitis_b_screening_result'],
            'hiv_screening_date' => $validated['hiv_screening_date'],
            'gestational_diabetes_screening_date' => $validated['gestational_diabetes_screening_date'],
            'gestational_diabetes_result' => $validated['gestational_diabetes_result'],
            'cbc_screening_date' => $validated['cbc_screening_date'],
            'cbc_anemia_status' => $validated['cbc_anemia_status'],
            'cbc_given_iron' => $validated['cbc_given_iron'],
            'pregnancy_terminated_date' => $validated['pregnancy_terminated_date'],
            'pregnancy_outcome' => $validated['pregnancy_outcome'],
            'pregnancy_outcome_sex' => $validated['pregnancy_outcome_sex'],
            'delivery_type' => $validated['delivery_type'],
            'birth_weight_category' => $validated['birth_weight_category'],
            'facility_delivery_type' => $validated['facility_delivery_type'],
            'facility_bemonc_capable' => $validated['facility_bemonc_capable'],
            'facility_ownership' => $validated['facility_ownership'],
            'non_health_facility_code' => $validated['non_health_facility_code'],
            'birth_attendant_code' => $validated['birth_attendant_code'],
            'delivery_remarks' => $validated['delivery_remarks'],
            'delivery_date' => $validated['delivery_date'],
            'delivery_time' => $validated['delivery_time'],
            'postpartum_within_24_hours_date' => $validated['postpartum_within_24_hours_date'],
            'postpartum_within_7_days_date' => $validated['postpartum_within_7_days_date'],
            'postpartum_iron_first_month' => $validated['postpartum_iron_first_month'],
            'postpartum_iron_second_month' => $validated['postpartum_iron_second_month'],
            'postpartum_iron_third_month' => $validated['postpartum_iron_third_month'],
            'vitamin_a_date' => $validated['vitamin_a_date'],
            'postpartum_remarks' => $validated['postpartum_remarks'],
            'prenatal_first_trimester_date' => $validated['prenatal_first_trimester_date'],
            'prenatal_second_trimester_date_1' => $validated['prenatal_second_trimester_date_1'],
            'prenatal_second_trimester_date_2' => $validated['prenatal_second_trimester_date_2'],
            'prenatal_third_trimester_date_1' => $validated['prenatal_third_trimester_date_1'],
            'prenatal_third_trimester_date_2' => $validated['prenatal_third_trimester_date_2'],
            'prenatal_third_trimester_date_3' => $validated['prenatal_third_trimester_date_3'],
            'prenatal_third_trimester_date_4' => $validated['prenatal_third_trimester_date_4'],
            'prenatal_third_trimester_date_5' => $validated['prenatal_third_trimester_date_5'],
        ]);

        return redirect()
            ->route('midwife.maternal-care-target-clients.index')
            ->with('success', 'New maternal care target client record created successfully.');
    }
}
