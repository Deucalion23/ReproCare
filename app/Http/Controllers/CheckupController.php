<?php

namespace App\Http\Controllers;

use App\Models\Checkup;
use App\Models\User;
use App\Models\Notification;
use App\Models\WalkInPatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Index
    public function index()
    {
        $search = request('search');
        $status = request('status', 'all');
        $startDate = request('start_date');
        $endDate = request('end_date');

        $checkups = Checkup::with(['woman', 'walkInPatient', 'midwife', 'scheduledBy'])
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
                    })->orWhere('purpose', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== 'all', fn ($query) => $query->where('status', ucfirst($status)))
            ->when($startDate, fn ($query) => $query->whereDate('scheduled_date', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('scheduled_date', '<=', $endDate))
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_time')
            ->paginate(10)
            ->withQueryString();

        // Get actual counts from database (not just current page)
        $stats = [
            'total' => Checkup::count(),
            'scheduled' => Checkup::where('status', 'Scheduled')->count(),
            'completed' => Checkup::where('status', 'Completed')->count(),
            'missed' => Checkup::where('status', 'Missed')->count(),
            'rescheduled' => Checkup::where('status', 'Rescheduled')->count(),
        ];

        return view('midwife.checkups.index', compact('checkups', 'stats', 'status', 'startDate', 'endDate'));
    }

    // Create
    public function create($userId = null)
    {
        $woman = null;
        $women = null;
        $midwives = null;
        $walkInPatients = null;

        if ($userId) {
            $woman = User::where('role', 'user')->findOrFail($userId);
        } else {
            $women = User::where('role', 'user')->where('status', 'approved')->get();
            $midwives = User::where('role', 'midwife')->get();
            $walkInPatients = WalkInPatient::notConverted()->latest()->get();
        }

        return view('midwife.checkups.create', compact('woman', 'women', 'midwives', 'walkInPatients'));
    }

    // Store
    public function store(Request $request)
    {
        $request->validate([
            'patient_type' => 'required|in:registered,walk_in',
            'user_id' => 'exclude_unless:patient_type,registered|required|exists:users,id',
            'walk_in_patient_id' => 'exclude_unless:patient_type,walk_in|required|exists:walk_in_patients,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required',
            'purpose' => 'required|string|max:255',
        ]);

        // Get the logged-in user
        $loggedInUser = Auth::user();

        try {
            $checkup = Checkup::create([
                'user_id' => $request->patient_type === 'registered' ? $request->user_id : null,
                'walk_in_patient_id' => $request->patient_type === 'walk_in' ? $request->walk_in_patient_id : null,
                'midwife_id' => $loggedInUser->id,
                'scheduled_by_id' => $loggedInUser->id,
                'scheduled_date' => $request->scheduled_date,
                'scheduled_time' => $request->scheduled_time,
                'purpose' => $request->purpose,
                'notes' => $request->notes ?? null,
            ]);

            // Create notification for woman
            if ($request->patient_type === 'registered') {
                Notification::createNotification($request->user_id,
                    "You have a checkup scheduled on " . Carbon::parse($request->scheduled_date . ' ' . $request->scheduled_time)->format('F j, Y \a\t g:i A') . " for: {$request->purpose}"
                );
            }

            return redirect()->route('midwife.checkups.index')
                ->with('success', 'Checkup scheduled successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to schedule checkup: ' . $e->getMessage())->withInput();
        }
    }

    // Show
    public function show($id)
    {
        $checkup = Checkup::with(['woman', 'walkInPatient', 'midwife'])->findOrFail($id);
        return view('midwife.checkups.show', compact('checkup'));
    }

    // Edit
    public function edit($id)
    {
        $checkup = Checkup::with(['woman', 'walkInPatient'])->findOrFail($id);
        $women = User::where('role', 'user')->where('status', 'approved')->get();
        $midwives = User::where('role', 'midwife')->get();
        $walkInPatients = WalkInPatient::notConverted()->latest()->get();

        return view('midwife.checkups.edit', compact('checkup', 'women', 'midwives', 'walkInPatients'));
    }

    // Update
    public function update(Request $request, $id)
    {
        $checkup = Checkup::findOrFail($id);
        if (!$request->filled('patient_type')) {
            $request->merge([
                'patient_type' => $checkup->walk_in_patient_id ? 'walk_in' : 'registered',
            ]);
        }

        $request->validate([
            'user_id' => 'exclude_unless:patient_type,registered|required|exists:users,id',
            'walk_in_patient_id' => 'exclude_unless:patient_type,walk_in|required|exists:walk_in_patients,id',
            'midwife_id' => 'required|exists:users,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required',
            'purpose' => 'required|string|max:255',
            'status' => 'required|in:Scheduled,Completed,Missed,Rescheduled,Cancelled',
        ]);

        // Check if date was changed
        $oldDate = $checkup->scheduled_date ? $checkup->scheduled_date->format('Y-m-d') : null;
        $newDate = $request->scheduled_date;
        $dateChanged = $oldDate !== $newDate;

        // If rescheduling a missed/cancelled checkup, set status to Rescheduled
        if (in_array($checkup->status, ['Missed', 'Cancelled']) && $request->status === 'Scheduled') {
            $request->merge(['status' => 'Rescheduled']);
        }

        // If date was changed and status is scheduled, set to Rescheduled
        if ($dateChanged && $request->status === 'Scheduled' && $checkup->status === 'Scheduled') {
            $request->merge(['status' => 'Rescheduled']);
        }

        $checkup->update([
            'user_id' => $request->patient_type === 'registered' ? $request->user_id : null,
            'walk_in_patient_id' => $request->patient_type === 'walk_in' ? $request->walk_in_patient_id : null,
            'midwife_id' => $request->midwife_id,
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'purpose' => $request->purpose,
            'status' => $request->status,
            'notes' => $request->notes ?? null,
        ]);

        // Create notification if status changed to completed
        if ($request->status === 'Completed' && $checkup->user_id) {
            Notification::createNotification($checkup->user_id,
                "Your checkup on {$checkup->scheduled_date->format('F j, Y')} at " . Carbon::createFromFormat('H:i:s', $checkup->scheduled_time)->format('g:i A') . " has been marked as completed"
            );
        }

        // Create notification if rescheduled
        if ($request->status === 'Rescheduled' && $dateChanged && $checkup->user_id) {
            Notification::createNotification($checkup->user_id,
                "Your checkup has been rescheduled to {$checkup->scheduled_date->format('F j, Y')} at " . Carbon::createFromFormat('H:i:s', $checkup->scheduled_time)->format('g:i A')
            );
        }

        $successMsg = 'Checkup updated successfully';
        if ($dateChanged && $request->status === 'Rescheduled') {
            $successMsg .= ' (Rescheduled)';
        }

        return redirect()->route('midwife.checkups.index')
            ->with('success', $successMsg);
    }

    // Destroy
    public function destroy($id)
    {
        $checkup = Checkup::findOrFail($id);
        $checkup->delete();

        return redirect()->route('midwife.checkups.index')
            ->with('success', 'Checkup archived successfully');
    }

    // Mark as Completed
    public function markCompleted($id)
    {
        $checkup = Checkup::findOrFail($id);
        $checkup->status = 'Completed';
        $checkup->save();

        if ($checkup->user_id) {
            Notification::createNotification($checkup->user_id,
                "Your checkup on {$checkup->scheduled_date->format('F j, Y')}" . ($checkup->scheduled_time ? ' at ' . Carbon::createFromFormat('H:i:s', $checkup->scheduled_time)->format('g:i A') : '') . " has been marked as completed"
            );
        }

        return redirect()->back()
            ->with('success', 'Checkup marked as completed');
    }

    // Mark as Missed
    public function markMissed($id)
    {
        $checkup = Checkup::findOrFail($id);
        $checkup->markAsMissed();

        return redirect()->back()
            ->with('success', 'Checkup marked as missed');
    }

    // Mark as Scheduled (Reschedule)
    public function markScheduled($id)
    {
        $checkup = Checkup::findOrFail($id);
        
        // Redirect to edit page to change the date
        return redirect()->route('midwife.checkups.edit', $id)
            ->with('info', 'Please select a new date for the rescheduled checkup');
    }

    // Mark as Cancelled
    public function markCancelled($id)
    {
        $checkup = Checkup::findOrFail($id);
        $checkup->status = 'Cancelled';
        $checkup->save();

        return redirect()->back()
            ->with('success', 'Checkup cancelled');
    }
}
