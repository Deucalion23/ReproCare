<?php

namespace App\Http\Controllers;

use App\Models\FamilyPlanningRecord;
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

    public function patientCreate() { return view('user.family-planning.create'); }

    public function patientStore(Request $request)
    {
        $data = $this->validated($request, false);
        $data['user_id'] = auth()->id();
        $data['status'] = 'requested';
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
        return view('family-planning.staff-index', compact('records', 'dueCount'));
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
        $this->ensureClientInScope((int) $data['user_id']);
        $data['informed_choice_confirmed'] = $request->boolean('informed_choice_confirmed');
        $record->update($data);
        return redirect()->route($this->staffRoute('index'))->with('success', 'Family-planning service record updated.');
    }

    private function validated(Request $request, bool $staff): array
    {
        $rules = [
            'preferred_method' => 'nullable|in:'.implode(',', self::METHODS),
            'pregnancy_intention' => 'nullable|in:trying,avoiding,undecided,spacing',
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
        abort_unless($this->clients()->whereKey($id)->exists(), 404);
    }

    private function staffRoute(string $name): string
    {
        return auth()->user()->role === 'midwife' ? 'midwife.family-planning.'.$name : 'bhw.family-planning.'.$name;
    }
}
