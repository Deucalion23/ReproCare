<?php

namespace App\Http\Controllers;

use App\Models\FamilyPlanningRecord;
use App\Models\Pregnancy;
use App\Models\User;
use App\Services\BhwPresidentAssignmentService;
use Illuminate\Http\Request;

class FamilyPlanningController extends Controller
{
    private const METHODS = ['condom', 'pill', 'injectable', 'implant', 'iud', 'natural_method', 'other'];

    public function patientIndex()
    {
        $records = auth()->user()->familyPlanningRecords()->latest()->paginate(10);
        return view('user.family-planning.index', compact('records'));
    }

    public function patientCreate()
    {
        $activePregnancy = auth()->user()->pregnancyBlockingMenstrualLogging();
        return view('user.family-planning.create', compact('activePregnancy'));
    }

    public function patientStore(Request $request)
    {
        $data = $this->validated($request, false);
        $data['user_id'] = auth()->id();
        $data['status'] = 'requested';
        $this->attachPostpartumPlan($data, $request);
        FamilyPlanningRecord::create($data);
        return redirect()->route('user.family-planning.index')->with('success', 'Your counseling request was saved. A health worker will discuss options with you privately.');
    }

    public function staffIndex()
    {
        $query = FamilyPlanningRecord::with(['client:id,first_name,last_name,barangay', 'recordedBy:id,first_name,last_name'])
            ->latest('next_follow_up');
        $this->applyBhwScope($query);
        $records = $query->paginate(20);
        $dueCount = (clone $query)->whereNotNull('next_follow_up')->whereDate('next_follow_up', '<=', today())->count();
        $pendingCount = (clone $query)->where('status', 'requested')->count();
        return view('family-planning.staff-index', compact('records', 'dueCount', 'pendingCount'));
    }

    public function staffCreate()
    {
        $clients = $this->clients()->get(['id', 'first_name', 'last_name', 'barangay']);
        return view('family-planning.staff-form', ['record' => new FamilyPlanningRecord(), 'clients' => $clients]);
    }

    public function staffStore(Request $request)
    {
        $data = $this->validated($request, true);
        $this->ensureClientInScope((int) $data['user_id']);
        $data['recorded_by_id'] = auth()->id();
        $data['informed_choice_confirmed'] = $request->boolean('informed_choice_confirmed');
        $this->attachPostpartumPlan($data, $request);
        FamilyPlanningRecord::create($data);
        return redirect()->route($this->staffRoute('index'))->with('success', 'Family-planning service record saved.');
    }

    public function staffEdit(FamilyPlanningRecord $record)
    {
        $this->ensureClientInScope($record->user_id);
        $clients = $this->clients()->get(['id', 'first_name', 'last_name', 'barangay']);
        return view('family-planning.staff-form', compact('record', 'clients'));
    }

    public function staffUpdate(Request $request, FamilyPlanningRecord $record)
    {
        $this->ensureClientInScope($record->user_id);
        $data = $this->validated($request, true);
        // Lock the client: never allow changing user_id on an existing record
        unset($data['user_id']);
        $data['informed_choice_confirmed'] = $request->boolean('informed_choice_confirmed');
        $this->attachPostpartumPlan($data, $request, $record);
        $record->update($data);
        return redirect()->route($this->staffRoute('index'))->with('success', 'Family-planning service record updated.');
    }

    private function validated(Request $request, bool $staff): array
    {
        $rules = [
            'preferred_method' => 'nullable|in:'.implode(',', self::METHODS),
            'pregnancy_intention' => 'nullable|in:trying,avoiding,undecided,spacing',
            'is_postpartum_plan' => 'nullable|boolean',
            'postpartum_start_timing' => 'nullable|in:immediately_after_delivery,before_discharge,six_week_visit,undecided',
            'notes' => 'nullable|string|max:1000',
        ];
        if ($staff) {
            $rules += [
                'user_id' => 'required|exists:users,id', 'method_given' => 'nullable|in:'.implode(',', self::METHODS),
                'counseling_date' => 'nullable|date', 'start_date' => 'nullable|date', 'stop_date' => 'nullable|date|after_or_equal:start_date',
                'next_follow_up' => 'nullable|date', 'side_effects' => 'nullable|string|max:1000',
                'status' => 'required|in:requested,active,stopped,referred',
            ];
        }
        return $request->validate($rules);
    }

    /** Attach a plan only to the client's current pregnancy; never trust a pregnancy ID from the browser. */
    private function attachPostpartumPlan(array &$data, Request $request, ?FamilyPlanningRecord $existing = null): void
    {
        $isPlan = $request->boolean('is_postpartum_plan');
        $data['is_postpartum_plan'] = $isPlan;
        $data['postpartum_start_timing'] = $isPlan ? ($data['postpartum_start_timing'] ?? 'undecided') : null;
        $currentPregnancyId = Pregnancy::where('user_id', $data['user_id'] ?? ($existing?->user_id ?? null))->whereNull('ended_at')->latest('lmp')->value('id');
        // An existing plan must remain connected after delivery, when there
        // is no longer an open pregnancy record to find.
        $data['pregnancy_id'] = $isPlan ? ($currentPregnancyId ?: $existing?->pregnancy_id) : null;
    }

    private function clients()
    {
        $query = User::where('role', 'user')->where('status', 'approved')->orderBy('last_name')->orderBy('first_name');
        if (auth()->user()->role === 'bhw') {
            BhwPresidentAssignmentService::applyJurisdictionFilter($query, auth()->user()->assigned_barangay ?: auth()->user()->barangay);
        }
        return $query;
    }

    private function applyBhwScope($query): void
    {
        if (auth()->user()->role === 'bhw') {
            $area = auth()->user()->assigned_barangay ?: auth()->user()->barangay;
            $query->whereHas('client', fn ($q) => BhwPresidentAssignmentService::applyJurisdictionFilter($q, $area));
        }
    }

    private function ensureClientInScope(int $id): void
    {
        abort_unless(User::whereKey($id)->where('role', 'user')->where('status', 'approved')->exists(), 404);
    }

    private function staffRoute(string $name): string
    {
        return auth()->user()->role === 'midwife' ? 'midwife.family-planning.'.$name : 'bhw.family-planning.'.$name;
    }
}
