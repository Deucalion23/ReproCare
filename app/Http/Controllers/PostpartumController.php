<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Newborn;
use App\Models\NewbornImmunization;
use App\Models\PostpartumVisit;
use App\Models\Pregnancy;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Postpartum & Newborn Monitoring module.
 *
 * Activated once a delivery date is recorded on a pregnancy. BHWs log
 * births, maternal postpartum checks and newborn vitals during
 * house-to-house visits; midwives review and track immunizations.
 */
class PostpartumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function routeBase(): string
    {
        return match (auth()->user()->role) {
            'midwife' => 'midwife',
            'bhw' => 'bhw',
            default => 'bhw',
        };
    }

    /**
     * Delivered mothers (delivery date recorded) + their newborns.
     */
    public function index()
    {
        $delivered = Pregnancy::with(['woman', 'newborns'])
            ->whereNotNull('delivery_date')
            ->orderByDesc('delivery_date')
            ->paginate(12);

        $newborns = Newborn::with(['mother', 'immunizations'])
            ->orderByDesc('birth_date')
            ->paginate(12, ['*'], 'newborns_page');

        $dangerVisits = PostpartumVisit::with('mother')
            ->latest()
            ->take(50)
            ->get()
            ->filter->has_danger_signs;

        return view('postpartum.index', compact('delivered', 'newborns', 'dangerVisits'));
    }

    public function showMother($id)
    {
        $mother = User::where('role', 'user')
            ->with(['newborns.immunizations', 'postpartumVisits.recordedBy', 'pregnancies'])
            ->findOrFail($id);

        return view('postpartum.show', compact('mother'));
    }

    public function createNewborn(Request $request)
    {
        $this->authorizeStaff();
        $mother = User::where('role', 'user')->findOrFail($request->query('mother_id', $request->input('mother_id')));
        $pregnancy = $request->filled('pregnancy_id')
            ? Pregnancy::where('user_id', $mother->id)->findOrFail($request->input('pregnancy_id'))
            : Pregnancy::where('user_id', $mother->id)->whereNotNull('delivery_date')->latest()->first();

        return view('postpartum.newborn-create', compact('mother', 'pregnancy'));
    }

    public function storeNewborn(Request $request)
    {
        $this->authorizeStaff();

        $data = $request->validate([
            'mother_id' => 'required|exists:users,id',
            'pregnancy_id' => 'nullable|exists:pregnancies,id',
            'name' => 'nullable|string|max:255',
            'sex' => 'nullable|in:male,female',
            'birth_date' => 'required|date|before_or_equal:today',
            'birth_weight_kg' => 'nullable|numeric|min:0.3|max:8',
            'feeding_type' => 'required|in:exclusive_breast,mixed,formula',
            'danger_signs' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $data['recorded_by_id'] = auth()->id();
        $newborn = Newborn::create($data);
        $newborn->seedImmunizationSchedule();

        ActivityLog::log('create', "Logged newborn record for mother ID {$newborn->mother_id} ({$newborn->display_name})", $newborn);

        return redirect()->route($this->routeBase() . '.postpartum.show-mother', $newborn->mother_id)
            ->with('success', 'Birth recorded. Immunization schedule generated.');
    }

    public function createVisit(Request $request)
    {
        $this->authorizeStaff();
        $mother = User::where('role', 'user')
            ->with('newborns')
            ->findOrFail($request->query('mother_id', $request->input('mother_id')));

        return view('postpartum.visit-create', compact('mother'));
    }

    public function storeVisit(Request $request)
    {
        $this->authorizeStaff();

        $data = $request->validate([
            'mother_id' => 'required|exists:users,id',
            'pregnancy_id' => 'nullable|exists:pregnancies,id',
            'newborn_id' => 'nullable|exists:newborns,id',
            'visit_date' => 'required|date|before_or_equal:today',
            'visit_week' => 'required|integer|min:0|max:52',
            'bp' => 'nullable|string|max:12',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'bleeding' => 'required|in:none,spotting,heavy',
            'infection_signs' => 'nullable|string|max:1000',
            'depression_score' => 'nullable|integer|min:0|max:9',
            'breastfeeding' => 'required|in:exclusive,partial,none',
            'notes' => 'nullable|string|max:2000',
        ]);

        $visit = PostpartumVisit::create([
            ...$data,
            'user_id' => $data['mother_id'],
            'recorded_by_id' => auth()->id(),
        ]);

        ActivityLog::log('create', "Logged postpartum week-{$visit->visit_week} visit for mother ID {$visit->user_id}", $visit);

        $message = 'Postpartum visit recorded.';
        if ($visit->has_danger_signs) {
            $message .= ' DANGER SIGNS detected — clinical follow-up required.';
        }

        return redirect()->route($this->routeBase() . '.postpartum.show-mother', $visit->user_id)
            ->with($visit->has_danger_signs ? 'error' : 'success', $message);
    }

    /**
     * Mark a scheduled immunization as given / missed.
     */
    public function markImmunization(Request $request, $id)
    {
        $this->authorizeStaff();

        $request->validate(['status' => 'required|in:given,missed,scheduled']);

        $record = NewbornImmunization::findOrFail($id);
        $record->update([
            'status' => $request->status,
            'given_date' => $request->status === 'given' ? now()->toDateString() : null,
            'recorded_by_id' => auth()->id(),
        ]);

        ActivityLog::log('update', "Marked {$record->vaccine} as {$record->status} for newborn ID {$record->newborn_id}", $record);

        return back()->with('success', "{$record->vaccine} marked as {$record->status}.");
    }

    private function authorizeStaff(): void
    {
        abort_unless(in_array(auth()->user()->role, ['bhw', 'midwife'], true), 403);
    }
}
