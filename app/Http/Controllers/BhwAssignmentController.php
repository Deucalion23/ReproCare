<?php

namespace App\Http\Controllers;

use App\Models\BhwAssignment;
use App\Models\User;
use App\Models\Purok;
use Illuminate\Http\Request;

class BhwAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $assignments = BhwAssignment::with(['bhw', 'purok', 'assignedBy'])->latest()->get();
        return view('bhw-president.assignments.index', compact('assignments'));
    }

    public function create()
    {
        $bhws = User::where('role', 'bhw')->where('status', 'approved')->get();
        $puroks = Purok::all();
        return view('bhw-president.assignments.create', compact('bhws', 'puroks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bhw_id' => 'required|exists:users,id',
            'purok_id' => 'required|exists:puroks,id',
            'notes' => 'nullable|string',
        ]);

        BhwAssignment::create([
            'bhw_id' => $request->bhw_id,
            'purok_id' => $request->purok_id,
            'assigned_by_id' => auth()->id(),
            'assigned_at' => now(),
            'is_active' => true,
            'notes' => $request->notes,
        ]);

        return redirect()->route('bhw-president.assignments.index')
            ->with('success', 'BHW assigned successfully');
    }

    public function show($id)
    {
        $assignment = BhwAssignment::with(['bhw', 'purok', 'assignedBy'])->findOrFail($id);
        return view('bhw-president.assignments.show', compact('assignment'));
    }

    public function edit($id)
    {
        $assignment = BhwAssignment::findOrFail($id);
        $bhws = User::where('role', 'bhw')->where('status', 'approved')->get();
        $puroks = Purok::all();
        return view('bhw-president.assignments.edit', compact('assignment', 'bhws', 'puroks'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bhw_id' => 'required|exists:users,id',
            'purok_id' => 'required|exists:puroks,id',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        $assignment = BhwAssignment::findOrFail($id);
        $assignment->update([
            'bhw_id' => $request->bhw_id,
            'purok_id' => $request->purok_id,
            'is_active' => $request->is_active,
            'notes' => $request->notes,
        ]);

        return redirect()->route('bhw-president.assignments.index')
            ->with('success', 'Assignment updated successfully');
    }

    public function destroy($id)
    {
        $assignment = BhwAssignment::findOrFail($id);
        $assignment->delete();

        return redirect()->route('bhw-president.assignments.index')
            ->with('success', 'Assignment deleted successfully');
    }

    public function toggleStatus($id)
    {
        $assignment = BhwAssignment::findOrFail($id);
        $assignment->update(['is_active' => !$assignment->is_active]);

        return redirect()->route('bhw-president.assignments.index')
            ->with('success', 'Assignment status updated');
    }
}
