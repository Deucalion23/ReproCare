<?php

namespace App\Http\Controllers;

use App\Models\ChildCareTargetClient;
use App\Models\ChildRecord;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class ChildCareTargetClientController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'page-1');
        $search = trim((string) $request->get('search', ''));

        $children = ChildRecord::query()
            ->with(['mother:id,name,address,barangay', 'targetClient', 'purok:id,name'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('barangay', 'like', '%' . $search . '%')
                        ->orWhereHas('mother', function ($mother) use ($search) {
                            $mother->where('name', 'like', '%' . $search . '%')
                                ->orWhere('address', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('date_of_birth')
            ->paginate(12)
            ->withQueryString();

        $rows = $children->through(function (ChildRecord $child, int $index) use ($children) {
            return [
                'number' => (($children->currentPage() - 1) * $children->perPage()) + $index + 1,
                'child' => $child,
                'profile' => $child->targetClient,
                'ageMonths' => $child->date_of_birth?->diffInMonths(now()) ?? null,
            ];
        });

        $stats = [
            'totalChildren' => ChildRecord::count(),
            'activeChildren' => ChildRecord::where('status', 'active')->count(),
            'completedProfiles' => ChildCareTargetClient::count(),
        ];

        return view('midwife.child-care-target-clients.index', [
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

        $children = ChildRecord::query()
            ->with(['mother:id,name,address,barangay', 'targetClient', 'purok:id,name'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('barangay', 'like', '%' . $search . '%')
                        ->orWhereHas('mother', function ($mother) use ($search) {
                            $mother->where('name', 'like', '%' . $search . '%')
                                ->orWhere('address', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('date_of_birth')
            ->get()
            ->values();

        $clientRows = $children->map(function (ChildRecord $child, int $index) {
            return [
                'number' => $index + 1,
                'child' => $child,
                'profile' => $child->targetClient,
                'ageMonths' => $child->date_of_birth?->diffInMonths(now()) ?? null,
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
            'totalChildren' => $clientRows->count(),
            'activeChildren' => $clientRows->count(),
            'completedProfiles' => $clientRows->filter(fn ($client) => !empty($client['profile']))->count(),
        ];

        return view('midwife.child-care-target-clients.index', compact('clients', 'tab', 'search', 'stats') + ['printMode' => true]);
    }

    public function edit(ChildRecord $child)
    {
        $child->load(['mother:id,name,address,barangay', 'targetClient', 'purok:id,name']);

        return view('midwife.child-care-target-clients.edit', [
            'child' => $child,
            'profile' => $child->targetClient,
        ]);
    }

    public function update(Request $request, ChildRecord $child)
    {
        $validated = $request->validate([
            'date_of_registration' => ['nullable', 'date'],
            'family_serial_number' => ['nullable', 'string', 'max:100'],
            'cpab_tt2_tt4' => ['nullable', 'boolean'],
            'cpab_tt3_tt5' => ['nullable', 'boolean'],
            'newborn_status' => ['nullable', Rule::in(['low', 'normal', 'unknown'])],
            'breastfeeding_initiated_date' => ['nullable', 'date'],
            'bcg_date' => ['nullable', 'date'],
            'hepa_b_bd_date' => ['nullable', 'date'],
            'assessment_1_3_age_months' => ['nullable', 'string', 'max:30'],
            'assessment_1_3_length_cm' => ['nullable', 'numeric', 'min:0'],
            'assessment_1_3_length_date' => ['nullable', 'date'],
            'assessment_1_3_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'assessment_1_3_weight_date' => ['nullable', 'date'],
            'assessment_1_3_status' => ['nullable', Rule::in(['S', 'W-MAM', 'W-SAM', 'O', 'N'])],
            'low_birth_weight_iron_1_month_date' => ['nullable', 'date'],
            'low_birth_weight_iron_2_month_date' => ['nullable', 'date'],
            'low_birth_weight_iron_3_month_date' => ['nullable', 'date'],
            'dpt_hepb_hib_1_date' => ['nullable', 'date'],
            'dpt_hepb_hib_2_date' => ['nullable', 'date'],
            'dpt_hepb_hib_3_date' => ['nullable', 'date'],
            'opv_1_date' => ['nullable', 'date'],
            'opv_2_date' => ['nullable', 'date'],
            'opv_3_date' => ['nullable', 'date'],
            'pcv_1_date' => ['nullable', 'date'],
            'pcv_2_date' => ['nullable', 'date'],
            'pcv_3_date' => ['nullable', 'date'],
            'ipv_1_date' => ['nullable', 'date'],
            'exclusive_breastfeeding_1_5_months' => ['nullable', 'boolean'],
            'exclusive_breastfeeding_2_5_months' => ['nullable', 'boolean'],
            'exclusive_breastfeeding_3_5_months' => ['nullable', 'boolean'],
            'exclusive_breastfeeding_4_5_months' => ['nullable', 'boolean'],
            'exclusive_breastfeeding_5_9_months' => ['nullable', 'boolean'],
            'assessment_6_11_age_months' => ['nullable', 'string', 'max:30'],
            'assessment_6_11_length_cm' => ['nullable', 'numeric', 'min:0'],
            'assessment_6_11_length_date' => ['nullable', 'date'],
            'assessment_6_11_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'assessment_6_11_weight_date' => ['nullable', 'date'],
            'assessment_6_11_status' => ['nullable', Rule::in(['S', 'W-MAM', 'W-SAM', 'O', 'N'])],
            'exclusive_breastfed_up_to_6_months' => ['nullable', 'boolean'],
            'complementary_feeding_introduced' => ['nullable', 'boolean'],
            'vitamin_a_date' => ['nullable', 'date'],
            'mnp_date' => ['nullable', 'date'],
            'mnp_sachets_given' => ['nullable', 'integer', 'min:0'],
            'mmr_1_date' => ['nullable', 'date'],
            'ipv_2_date' => ['nullable', 'date'],
            'assessment_12_age_months' => ['nullable', 'string', 'max:30'],
            'assessment_12_length_cm' => ['nullable', 'numeric', 'min:0'],
            'assessment_12_length_date' => ['nullable', 'date'],
            'assessment_12_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'assessment_12_weight_date' => ['nullable', 'date'],
            'assessment_12_status' => ['nullable', Rule::in(['S', 'W-MAM', 'W-SAM', 'O', 'N'])],
            'mmr_2_date' => ['nullable', 'date'],
            'fic_date' => ['nullable', 'date'],
            'cic_date' => ['nullable', 'date'],
            'man_admitted_sfp' => ['nullable', 'boolean'],
            'man_cured' => ['nullable', 'boolean'],
            'man_defaulted' => ['nullable', 'boolean'],
            'man_died' => ['nullable', 'boolean'],
            'sam_admitted_otc' => ['nullable', 'boolean'],
            'sam_cured' => ['nullable', 'boolean'],
            'sam_defaulted' => ['nullable', 'boolean'],
            'sam_died' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ([
            'cpab_tt2_tt4',
            'cpab_tt3_tt5',
            'exclusive_breastfeeding_1_5_months',
            'exclusive_breastfeeding_2_5_months',
            'exclusive_breastfeeding_3_5_months',
            'exclusive_breastfeeding_4_5_months',
            'exclusive_breastfeeding_5_9_months',
            'exclusive_breastfed_up_to_6_months',
            'complementary_feeding_introduced',
            'man_admitted_sfp',
            'man_cured',
            'man_defaulted',
            'man_died',
            'sam_admitted_otc',
            'sam_cured',
            'sam_defaulted',
            'sam_died',
        ] as $booleanField) {
            $validated[$booleanField] = $request->boolean($booleanField);
        }

        ChildCareTargetClient::updateOrCreate(
            ['child_id' => $child->id],
            $validated
        );

        return redirect()
            ->route('midwife.child-care-target-clients.index')
            ->with('success', 'Child care target client record updated successfully.');
    }

    public function create()
    {
        return view('midwife.child-care-target-clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'child_first_name' => ['required', 'string', 'max:255'],
            'child_middle_initial' => ['nullable', 'string', 'max:10'],
            'child_last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'birth_weight' => ['nullable', 'numeric', 'min:0'],
            'birth_length' => ['nullable', 'numeric', 'min:0'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'purok_id' => ['nullable', 'exists:puroks,id'],
            'mother_name' => ['required', 'string', 'max:255'],
            'mother_address' => ['nullable', 'string', 'max:500'],
            'mother_barangay' => ['nullable', 'string', 'max:255'],
            'date_of_registration' => ['nullable', 'date'],
            'family_serial_number' => ['nullable', 'string', 'max:100'],
            'cpab_tt2_tt4' => ['nullable', 'boolean'],
            'cpab_tt3_tt5' => ['nullable', 'boolean'],
            'newborn_status' => ['nullable', Rule::in(['low', 'normal', 'unknown'])],
            'breastfeeding_initiated_date' => ['nullable', 'date'],
            'bcg_date' => ['nullable', 'date'],
            'hepa_b_bd_date' => ['nullable', 'date'],
            'assessment_1_3_age_months' => ['nullable', 'string', 'max:30'],
            'assessment_1_3_length_cm' => ['nullable', 'numeric', 'min:0'],
            'assessment_1_3_length_date' => ['nullable', 'date'],
            'assessment_1_3_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'assessment_1_3_weight_date' => ['nullable', 'date'],
            'assessment_1_3_status' => ['nullable', Rule::in(['S', 'W-MAM', 'W-SAM', 'O', 'N'])],
            'low_birth_weight_iron_1_month_date' => ['nullable', 'date'],
            'low_birth_weight_iron_2_month_date' => ['nullable', 'date'],
            'low_birth_weight_iron_3_month_date' => ['nullable', 'date'],
            'dpt_hepb_hib_1_date' => ['nullable', 'date'],
            'dpt_hepb_hib_2_date' => ['nullable', 'date'],
            'dpt_hepb_hib_3_date' => ['nullable', 'date'],
            'opv_1_date' => ['nullable', 'date'],
            'opv_2_date' => ['nullable', 'date'],
            'opv_3_date' => ['nullable', 'date'],
            'pcv_1_date' => ['nullable', 'date'],
            'pcv_2_date' => ['nullable', 'date'],
            'pcv_3_date' => ['nullable', 'date'],
            'ipv_1_date' => ['nullable', 'date'],
            'exclusive_breastfeeding_1_5_months' => ['nullable', 'boolean'],
            'exclusive_breastfeeding_2_5_months' => ['nullable', 'boolean'],
            'exclusive_breastfeeding_3_5_months' => ['nullable', 'boolean'],
            'exclusive_breastfeeding_4_5_months' => ['nullable', 'boolean'],
            'exclusive_breastfeeding_5_9_months' => ['nullable', 'boolean'],
            'assessment_6_11_age_months' => ['nullable', 'string', 'max:30'],
            'assessment_6_11_length_cm' => ['nullable', 'numeric', 'min:0'],
            'assessment_6_11_length_date' => ['nullable', 'date'],
            'assessment_6_11_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'assessment_6_11_weight_date' => ['nullable', 'date'],
            'assessment_6_11_status' => ['nullable', Rule::in(['S', 'W-MAM', 'W-SAM', 'O', 'N'])],
            'exclusive_breastfed_up_to_6_months' => ['nullable', 'boolean'],
            'complementary_feeding_introduced' => ['nullable', 'boolean'],
            'vitamin_a_date' => ['nullable', 'date'],
            'mnp_date' => ['nullable', 'date'],
            'mnp_sachets_given' => ['nullable', 'integer', 'min:0'],
            'mmr_1_date' => ['nullable', 'date'],
            'ipv_2_date' => ['nullable', 'date'],
            'assessment_12_age_months' => ['nullable', 'string', 'max:30'],
            'assessment_12_length_cm' => ['nullable', 'numeric', 'min:0'],
            'assessment_12_length_date' => ['nullable', 'date'],
            'assessment_12_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'assessment_12_weight_date' => ['nullable', 'date'],
            'assessment_12_status' => ['nullable', Rule::in(['S', 'W-MAM', 'W-SAM', 'O', 'N'])],
            'mmr_2_date' => ['nullable', 'date'],
            'fic_date' => ['nullable', 'date'],
            'cic_date' => ['nullable', 'date'],
            'man_admitted_sfp' => ['nullable', 'boolean'],
            'man_cured' => ['nullable', 'boolean'],
            'man_defaulted' => ['nullable', 'boolean'],
            'man_died' => ['nullable', 'boolean'],
            'sam_admitted_otc' => ['nullable', 'boolean'],
            'sam_cured' => ['nullable', 'boolean'],
            'sam_defaulted' => ['nullable', 'boolean'],
            'sam_died' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        // Create or find the mother
        $mother = \App\Models\User::where('role', 'user')->firstOrCreate(
            ['name' => $validated['mother_name']],
            [
                'address' => $validated['mother_address'],
                'barangay' => $validated['mother_barangay'] ?? $validated['barangay'],
                'status' => 'approved',
            ]
        );

        // Create the child record
        $child = ChildRecord::create([
            'first_name' => $validated['child_first_name'],
            'middle_initial' => $validated['child_middle_initial'],
            'last_name' => $validated['child_last_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'birth_weight' => $validated['birth_weight'],
            'birth_length' => $validated['birth_length'],
            'barangay' => $validated['barangay'],
            'purok_id' => $validated['purok_id'],
            'mother_id' => $mother->id,
            'status' => 'active',
        ]);

        // Convert boolean fields
        foreach ([
            'cpab_tt2_tt4',
            'cpab_tt3_tt5',
            'exclusive_breastfeeding_1_5_months',
            'exclusive_breastfeeding_2_5_months',
            'exclusive_breastfeeding_3_5_months',
            'exclusive_breastfeeding_4_5_months',
            'exclusive_breastfeeding_5_9_months',
            'exclusive_breastfed_up_to_6_months',
            'complementary_feeding_introduced',
            'man_admitted_sfp',
            'man_cured',
            'man_defaulted',
            'man_died',
            'sam_admitted_otc',
            'sam_cured',
            'sam_defaulted',
            'sam_died',
        ] as $booleanField) {
            $validated[$booleanField] = $request->boolean($booleanField);
        }

        // Create the child care target client record
        ChildCareTargetClient::create([
            'child_id' => $child->id,
            'date_of_registration' => $validated['date_of_registration'],
            'family_serial_number' => $validated['family_serial_number'],
            'cpab_tt2_tt4' => $validated['cpab_tt2_tt4'],
            'cpab_tt3_tt5' => $validated['cpab_tt3_tt5'],
            'newborn_status' => $validated['newborn_status'],
            'breastfeeding_initiated_date' => $validated['breastfeeding_initiated_date'],
            'bcg_date' => $validated['bcg_date'],
            'hepa_b_bd_date' => $validated['hepa_b_bd_date'],
            'assessment_1_3_age_months' => $validated['assessment_1_3_age_months'],
            'assessment_1_3_length_cm' => $validated['assessment_1_3_length_cm'],
            'assessment_1_3_length_date' => $validated['assessment_1_3_length_date'],
            'assessment_1_3_weight_kg' => $validated['assessment_1_3_weight_kg'],
            'assessment_1_3_weight_date' => $validated['assessment_1_3_weight_date'],
            'assessment_1_3_status' => $validated['assessment_1_3_status'],
            'low_birth_weight_iron_1_month_date' => $validated['low_birth_weight_iron_1_month_date'],
            'low_birth_weight_iron_2_month_date' => $validated['low_birth_weight_iron_2_month_date'],
            'low_birth_weight_iron_3_month_date' => $validated['low_birth_weight_iron_3_month_date'],
            'dpt_hepb_hib_1_date' => $validated['dpt_hepb_hib_1_date'],
            'dpt_hepb_hib_2_date' => $validated['dpt_hepb_hib_2_date'],
            'dpt_hepb_hib_3_date' => $validated['dpt_hepb_hib_3_date'],
            'opv_1_date' => $validated['opv_1_date'],
            'opv_2_date' => $validated['opv_2_date'],
            'opv_3_date' => $validated['opv_3_date'],
            'pcv_1_date' => $validated['pcv_1_date'],
            'pcv_2_date' => $validated['pcv_2_date'],
            'pcv_3_date' => $validated['pcv_3_date'],
            'ipv_1_date' => $validated['ipv_1_date'],
            'exclusive_breastfeeding_1_5_months' => $validated['exclusive_breastfeeding_1_5_months'],
            'exclusive_breastfeeding_2_5_months' => $validated['exclusive_breastfeeding_2_5_months'],
            'exclusive_breastfeeding_3_5_months' => $validated['exclusive_breastfeeding_3_5_months'],
            'exclusive_breastfeeding_4_5_months' => $validated['exclusive_breastfeeding_4_5_months'],
            'exclusive_breastfeeding_5_9_months' => $validated['exclusive_breastfeeding_5_9_months'],
            'assessment_6_11_age_months' => $validated['assessment_6_11_age_months'],
            'assessment_6_11_length_cm' => $validated['assessment_6_11_length_cm'],
            'assessment_6_11_length_date' => $validated['assessment_6_11_length_date'],
            'assessment_6_11_weight_kg' => $validated['assessment_6_11_weight_kg'],
            'assessment_6_11_weight_date' => $validated['assessment_6_11_weight_date'],
            'assessment_6_11_status' => $validated['assessment_6_11_status'],
            'exclusive_breastfed_up_to_6_months' => $validated['exclusive_breastfed_up_to_6_months'],
            'complementary_feeding_introduced' => $validated['complementary_feeding_introduced'],
            'vitamin_a_date' => $validated['vitamin_a_date'],
            'mnp_date' => $validated['mnp_date'],
            'mnp_sachets_given' => $validated['mnp_sachets_given'],
            'mmr_1_date' => $validated['mmr_1_date'],
            'ipv_2_date' => $validated['ipv_2_date'],
            'assessment_12_age_months' => $validated['assessment_12_age_months'],
            'assessment_12_length_cm' => $validated['assessment_12_length_cm'],
            'assessment_12_length_date' => $validated['assessment_12_length_date'],
            'assessment_12_weight_kg' => $validated['assessment_12_weight_kg'],
            'assessment_12_weight_date' => $validated['assessment_12_weight_date'],
            'assessment_12_status' => $validated['assessment_12_status'],
            'mmr_2_date' => $validated['mmr_2_date'],
            'fic_date' => $validated['fic_date'],
            'cic_date' => $validated['cic_date'],
            'man_admitted_sfp' => $validated['man_admitted_sfp'],
            'man_cured' => $validated['man_cured'],
            'man_defaulted' => $validated['man_defaulted'],
            'man_died' => $validated['man_died'],
            'sam_admitted_otc' => $validated['sam_admitted_otc'],
            'sam_cured' => $validated['sam_cured'],
            'sam_defaulted' => $validated['sam_defaulted'],
            'sam_died' => $validated['sam_died'],
            'remarks' => $validated['remarks'],
        ]);

        return redirect()
            ->route('midwife.child-care-target-clients.index')
            ->with('success', 'New child care target client record created successfully.');
    }
}
