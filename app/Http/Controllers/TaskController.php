<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Presidents see only the tasks they dispatched to their BHWs.
        $tasks = Task::with(['assignedTo', 'assignedBy'])
            ->where('assigned_by_id', auth()->id())
            ->latest()
            ->get();
        return view('bhw-president.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $bhws = User::where('role', 'bhw')->where('status', 'approved')->get();
        return view('bhw-president.tasks.create', compact('bhws'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('users', 'id')->where(function ($q) {
                    $q->where('role', 'bhw')->where('status', 'approved');
                }),
            ],
            'task_type' => 'required|in:patient_visit,data_collection,follow_up,report_submission,other',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to_id' => $request->assigned_to_id,
            'assigned_by_id' => auth()->id(),
            'task_type' => $request->task_type,
            'status' => 'pending',
            'due_date' => $request->due_date,
        ]);

        // The BHW receives it in their bell + My Tasks — assignments are
        // never silent.
        $assignee = User::find($request->assigned_to_id);
        if ($assignee) {
            \App\Models\Notification::createNotification(
                $assignee->id,
                auth()->user()->name . ' assigned you a task: ' . $task->title
                    . ($task->due_date ? ' (due ' . $task->due_date->format('M d, Y') . ')' : ' (no due date)')
                    . '. Open My Tasks to start it.',
                '📋 New Task Assigned',
                'info',
                route('bhw.tasks.index'),
                'bhw'
            );
        }

        return redirect()->route('bhw-president.tasks.index')
            ->with('success', 'Task created successfully' . ($assignee ? ' and sent to ' . $assignee->name . '.' : '.'));
    }

    public function show($id)
    {
        $task = Task::with(['assignedTo', 'assignedBy'])
            ->where('assigned_by_id', auth()->id())
            ->findOrFail($id);
        return view('bhw-president.tasks.show', compact('task'));
    }

    public function edit($id)
    {
        $task = Task::where('assigned_by_id', auth()->id())->findOrFail($id);
        $bhws = User::where('role', 'bhw')->where('status', 'approved')->get();
        return view('bhw-president.tasks.edit', compact('task', 'bhws'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('users', 'id')->where(function ($q) {
                    $q->where('role', 'bhw')->where('status', 'approved');
                }),
            ],
            'task_type' => 'required|in:patient_visit,data_collection,follow_up,report_submission,other',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::where('assigned_by_id', auth()->id())->findOrFail($id);
        
        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to_id' => $request->assigned_to_id,
            'task_type' => $request->task_type,
            'status' => $request->status,
            'due_date' => $request->due_date,
        ];

        if ($request->status === 'completed' && !$task->completed_at) {
            $updateData['completed_at'] = now();
        }

        $task->update($updateData);

        return redirect()->route('bhw-president.tasks.index')
            ->with('success', 'Task updated successfully');
    }

    public function destroy($id)
    {
        $task = Task::where('assigned_by_id', auth()->id())->findOrFail($id);
        $task->delete();

        return redirect()->route('bhw-president.tasks.index')
            ->with('success', 'Task deleted successfully');
    }

    public function markComplete($id)
    {
        $task = Task::where('assigned_by_id', auth()->id())->findOrFail($id);
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('bhw-president.tasks.index')
            ->with('success', 'Task marked as completed');
    }

    public function markInProgress($id)
    {
        $task = Task::where('assigned_by_id', auth()->id())->findOrFail($id);
        $task->update(['status' => 'in_progress']);

        return redirect()->route('bhw-president.tasks.index')
            ->with('success', 'Task marked as in progress');
    }
}
