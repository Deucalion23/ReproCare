<?php

namespace App\Http\Controllers;

use App\Models\PatientCondition;
use App\Services\WomenDirectoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientConditionController extends Controller
{
    public function store(Request $request, int $womanId, WomenDirectoryService $directory)
    {
        $woman = $directory->findForUser($request->user(), 'registered', $womanId)['woman'];
        $data = $this->validatedData($request);

        $woman->patientConditions()->create([
            ...$data,
            'recorded_by_id' => $request->user()->id,
        ]);

        return redirect()->route('rhu.women.show', ['type' => 'registered', 'id' => $womanId])
            ->with('success', 'Medical condition recorded.');
    }

    public function update(Request $request, int $womanId, int $conditionId, WomenDirectoryService $directory)
    {
        $woman = $directory->findForUser($request->user(), 'registered', $womanId)['woman'];
        $condition = PatientCondition::where('user_id', $woman->id)->findOrFail($conditionId);
        $condition->update([
            ...$this->validatedData($request),
            'updated_by_id' => $request->user()->id,
        ]);

        return redirect()->route('rhu.women.show', ['type' => 'registered', 'id' => $womanId])
            ->with('success', 'Medical condition updated.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'condition_name' => ['required', 'string', 'max:150'],
            'diagnosed_on' => ['nullable', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::in(['Active', 'Resolved', 'Under Review'])],
            'verification_status' => ['required', Rule::in(['Reported', 'Verified', 'Awaiting Verification'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
