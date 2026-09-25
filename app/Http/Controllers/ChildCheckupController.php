<?php

namespace App\Http\Controllers;

use App\Models\ChildCheckup;
use App\Models\ChildRecord;
use Illuminate\Http\Request;

class ChildCheckupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($childId)
    {
        $child = ChildRecord::findOrFail($childId);
        $checkups = $child->checkups()->latest()->get();
        
        return view('midwife.child-checkups.index', compact('child', 'checkups'));
    }

    public function create($childId)
    {
        $child = ChildRecord::findOrFail($childId);
        return view('midwife.child-checkups.create', compact('child'));
    }

    public function store(Request $request, $childId)
    {
        $request->validate([
            'checkup_date' => 'required|date',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'head_circumference' => 'nullable|numeric',
            'developmental_milestones' => 'nullable|string',
            'vaccinations_given' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $child = ChildRecord::findOrFail($childId);
        
        $checkup = $child->checkups()->create([
            'checkup_date' => $request->checkup_date,
            'weight' => $request->weight,
            'height' => $request->height,
            'head_circumference' => $request->head_circumference,
            'developmental_milestones' => $request->developmental_milestones,
            'vaccinations_given' => $request->vaccinations_given,
            'notes' => $request->notes,
            'conducted_by_id' => auth()->id(),
        ]);

        return redirect()->route('midwife.child-checkups.index', $childId)
            ->with('success', 'Child checkup recorded successfully');
    }

    public function show($childId, $id)
    {
        $child = ChildRecord::findOrFail($childId);
        $checkup = $child->checkups()->findOrFail($id);
        
        return view('midwife.child-checkups.show', compact('child', 'checkup'));
    }

    public function edit($childId, $id)
    {
        $child = ChildRecord::findOrFail($childId);
        $checkup = $child->checkups()->findOrFail($id);
        
        return view('midwife.child-checkups.edit', compact('child', 'checkup'));
    }

    public function update(Request $request, $childId, $id)
    {
        $request->validate([
            'checkup_date' => 'required|date',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'head_circumference' => 'nullable|numeric',
            'developmental_milestones' => 'nullable|string',
            'vaccinations_given' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $child = ChildRecord::findOrFail($childId);
        $checkup = $child->checkups()->findOrFail($id);
        
        $checkup->update([
            'checkup_date' => $request->checkup_date,
            'weight' => $request->weight,
            'height' => $request->height,
            'head_circumference' => $request->head_circumference,
            'developmental_milestones' => $request->developmental_milestones,
            'vaccinations_given' => $request->vaccinations_given,
            'notes' => $request->notes,
        ]);

        return redirect()->route('midwife.child-checkups.index', $childId)
            ->with('success', 'Child checkup updated successfully');
    }

    public function destroy(\Illuminate\Http\Request $request, $childId, $id)
    {
        $child = ChildRecord::findOrFail($childId);
        $checkup = $child->checkups()->findOrFail($id);
        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'Child checkup archived via console';
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveRecord($checkup, $reason, auth()->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()])->withInput();
        }

        return redirect()->route('midwife.child-checkups.index', $childId)
            ->with('success', 'Child checkup archived successfully (retained for audit).');
    }
}
