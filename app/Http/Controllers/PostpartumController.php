<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Newborn;
use App\Models\NewbornImmunization;
use App\Models\PostpartumVisit;
use App\Models\Pregnancy;
use App\Models\User;
use Carbon\Carbon;
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
     *
     * Search, window and attention narrow every list at once so the tab counts,
     * the hero chips and the pagers all describe the same slice of the caseload.
     */
    public function index()
    {
        $search = trim((string) request('search', ''));
        $window = (string) request('window', 'all');
        $month = (string) request('month', '');
        $attention = (string) request('attention', 'all');
        $view = (string) request('view', 'all');
        // An unknown code would leave the select showing its first option while the
        // query still counted as filtered, so a typo can't stick around as a filter.
        if (! in_array($window, ['all', '6w', '12w', '6m', 'older'], true)) {
            $window = 'all';
        }
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = '';
        }
        if (! in_array($attention, ['all', 'vaccines', 'danger'], true)) {
            $attention = 'all';
        }
        if (! in_array($view, ['all', 'mothers', 'followups', 'newborns'], true)) {
            $view = 'all';
        }
        $filtering = $search !== '' || $window !== 'all' || $month !== '' || $attention !== 'all';

        // Flagged visits are decided in PHP (dangerSignals() reads a BP string), so the
        // set is built first and the mothers list reuses it instead of re-deriving it.
        $dangerVisits = PostpartumVisit::with('mother')
            ->when($search !== '', fn ($visits) => $visits->whereHas('mother', fn ($mother) => $this->searchMother($mother, $search)))
            ->when($window !== 'all', fn ($visits) => $this->applyWindow($visits, 'visit_date', $window))
            ->when($month !== '', fn ($visits) => $this->applyMonth($visits, 'visit_date', $month))
            ->latest()
            ->take(50)
            ->get()
            ->filter->has_danger_signs;
        $flaggedMotherIds = $dangerVisits->pluck('user_id')->unique();

        $delivered = Pregnancy::with(['woman', 'newborns'])
            ->whereNotNull('delivery_date')
            ->when($search !== '', function ($mothers) use ($search) {
                $mothers->where(function ($row) use ($search) {
                    $row->whereHas('woman', fn ($woman) => $this->searchMother($woman, $search))
                        ->orWhereHas('walkInPatient', fn ($patient) => $this->searchWalkIn($patient, $search));
                });
            })
            ->when($window !== 'all', fn ($mothers) => $this->applyWindow($mothers, 'delivery_date', $window))
            ->when($month !== '', fn ($mothers) => $this->applyMonth($mothers, 'delivery_date', $month))
            ->when($attention === 'vaccines', fn ($mothers) => $mothers->whereHas(
                'newborns.immunizations',
                fn ($doses) => $doses->where('status', 'scheduled')->whereDate('scheduled_date', '<=', Carbon::today())
            ))
            ->when($attention === 'danger', fn ($mothers) => $mothers->whereIn('user_id', $flaggedMotherIds))
            ->orderByDesc('delivery_date')
            ->paginate(12)
            ->withQueryString();

        $babies = Newborn::with(['mother', 'immunizations'])
            ->when($search !== '', function ($babies) use ($search) {
                $like = '%' . $search . '%';
                $babies->where(function ($row) use ($like, $search) {
                    $row->where('name', 'like', $like)
                        ->orWhereHas('mother', fn ($mother) => $this->searchMother($mother, $search));
                });
            })
            ->when($window !== 'all', fn ($babies) => $this->applyWindow($babies, 'birth_date', $window))
            ->when($month !== '', fn ($babies) => $this->applyMonth($babies, 'birth_date', $month))
            ->when($attention === 'vaccines', fn ($babies) => $babies->whereHas(
                'immunizations',
                fn ($doses) => $doses->where('status', 'scheduled')->whereDate('scheduled_date', '<=', Carbon::today())
            ))
            ->when($attention === 'danger', fn ($babies) => $babies->whereNotNull('danger_signs')->where('danger_signs', '!=', ''));

        // Doses due are counted inside the same slice, otherwise the chip would keep
        // advertising the whole caseload while every list around it had narrowed.
        $immunizationsDue = NewbornImmunization::query()
            ->where('status', 'scheduled')
            ->whereDate('scheduled_date', '<=', Carbon::today())
            ->whereIn('newborn_id', (clone $babies)->pluck('id'))
            ->count();

        $newborns = $babies->orderByDesc('birth_date')
            ->paginate(12, ['*'], 'newborns_page')
            ->withQueryString();

        // Only months that actually hold a delivery. Offering all twelve would
        // invite picks that can never return anything.
        $monthList = Pregnancy::whereNotNull('delivery_date')
            ->orderByDesc('delivery_date')
            ->pluck('delivery_date')
            ->mapWithKeys(fn ($date) => [$date->format('Y-m') => $date->format('F Y')])
            ->all();

        // A month typed straight into the URL still has to show itself as the
        // chosen option, or the select claims "All months" over a filtered list.
        if ($month !== '' && ! isset($monthList[$month])) {
            $monthList[$month] = Carbon::createFromFormat('Y-m', $month)->format('F Y');
            krsort($monthList);
        }

        return view('postpartum.index', compact(
            'delivered',
            'newborns',
            'dangerVisits',
            'immunizationsDue',
            'search',
            'window',
            'month',
            'monthList',
            'attention',
            'view',
            'filtering'
        ));
    }

    /** Name, e-mail or contact number on a user-backed mother. */
    private function searchMother($query, string $search): void
    {
        $like = '%' . $search . '%';
        $query->where(function ($row) use ($like) {
            $row->where('first_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('contact_number', 'like', $like);
        });
    }

    /** The same search for a mother interviewed but not yet registered. */
    private function searchWalkIn($query, string $search): void
    {
        $like = '%' . $search . '%';
        $query->where(function ($row) use ($like) {
            $row->where('first_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhere('contact_number', 'like', $like);
        });
    }

    /**
     * How long ago the delivery happened. The same span is measured on each list's
     * own date column (delivery, birth, visit) so a window never compares two clocks.
     */
    private function applyWindow($query, string $column, string $window): void
    {
        $now = Carbon::now();
        [$from, $to] = match ($window) {
            '6w' => [$now->copy()->subWeeks(6), null],
            '12w' => [$now->copy()->subWeeks(12), $now->copy()->subWeeks(6)],
            '6m' => [$now->copy()->subMonths(6), $now->copy()->subWeeks(12)],
            'older' => [null, $now->copy()->subMonths(6)],
            default => [null, null],
        };
        if ($from) {
            $query->whereDate($column, '>=', $from->toDateString());
        }
        if ($to) {
            $query->whereDate($column, '<', $to->toDateString());
        }
    }

    /**
     * A calendar month, measured on the same per-list column as the window so
     * the two date filters never compare different clocks.
     */
    private function applyMonth($query, string $column, string $month): void
    {
        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();

        $query->whereDate($column, '>=', $start->toDateString())
            ->whereDate($column, '<', $start->copy()->addMonth()->toDateString());
    }

    public function showMother($id)
    {
        $mother = User::where('role', 'user')
            ->with(['newborns.immunizations', 'postpartumVisits.recordedBy', 'pregnancies', 'familyPlanningRecords.pregnancy'])
            ->findOrFail($id);

        $postpartumPlans = $mother->familyPlanningRecords
            ->where('is_postpartum_plan', true)
            ->filter(fn ($plan) => $plan->pregnancy?->delivery_date !== null);

        return view('postpartum.show', compact('mother', 'postpartumPlans'));
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
