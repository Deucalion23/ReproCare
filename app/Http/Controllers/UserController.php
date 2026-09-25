<?php

namespace App\Http\Controllers;

use App\Models\Pregnancy;
use App\Models\Cycle;
use App\Models\MenstruationDaily;
use App\Models\Checkup;
use App\Models\HealthRecord;
use App\Models\Notification;
use App\Services\CyclePredictionService;
use App\Services\MaternalRiskService;
use App\Services\RiskAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Dashboard
    public function dashboard()
    {
        $user = Auth::user();
        
        // Auto-run risk analysis and alerts when user visits dashboard
        try {
            // Check for overdue checkups and send alerts
            Checkup::markOverdueCheckups();
            
            // Re-evaluate user's risk level
            $riskService = new RiskAnalysisService();
            $riskService->evaluate($user->id);
        } catch (\Exception $e) {
            // Silently fail - don't break dashboard if alerts fail
            \Log::error('Dashboard alert check failed: ' . $e->getMessage());
        }
        
        $activePregnancy = $user->pregnancies()->active()->first();
        $upcomingCheckups = $user->checkups()->scheduled()->upcoming()->count();
        $unreadNotifications = $user->notifications()->unread()->count();
        $nextPeriod = Cycle::predictNextPeriod($user->id);

        return view('user.dashboard', compact(
            'activePregnancy',
            'upcomingCheckups',
            'unreadNotifications',
            'nextPeriod'
        ));
    }

    // Profile Management - Redirect to unified profile system
    public function profile()
    {
        return redirect()->route('profile.show');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
        ]);

        $user->update($request->only(['name', 'address', 'barangay']));

        return redirect()->route('user.dashboard')
            ->with('success', 'Profile updated successfully');
    }

    // Pregnancy Tracking
    public function pregnancies()
    {
        $pregnancies = Auth::user()->pregnancies()
            ->with([
                'healthRecords' => fn($q) => $q->orderBy('created_at', 'desc'),
                'maternalCareTargetClient',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $activePregnancy = $pregnancies->firstWhere('is_active', true);
        return view('user.pregnancies.index', compact('pregnancies', 'activePregnancy'));
    }

    public function createPregnancy()
    {
        if (Auth::user()->pregnancies()->active()->exists()) {
            return redirect()->route('user.pregnancies.index')
                ->withErrors(['pregnancy' => 'You already have an active pregnancy record.']);
        }

        return view('user.pregnancies.create');
    }

    public function storePregnancy(Request $request)
    {
        $this->mergePregnancyLifestyleInputs($request);

        $request->validate([
            'lmp' => 'required|date|before:today',
            'gravida' => 'nullable|integer|min:1',
            'para' => 'nullable|integer|min:0',
            'bp_systolic' => 'nullable|integer|min:50|max:300',
            'bp_diastolic' => 'nullable|integer|min:30|max:200',
            'weight' => 'nullable|numeric|min:20|max:300',
            'height' => 'nullable|numeric|min:100|max:250',
            'smoking_status' => 'nullable|in:none,former,current',
            'alcohol_status' => 'nullable|in:none,former,current',
            'drug_use_status' => 'nullable|in:none,former,current',
            'obstetric_history' => 'nullable|string|max:2000',
            'lifestyle_notes' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (Auth::user()->pregnancies()->active()->exists()) {
            return back()
                ->withErrors(['pregnancy' => 'You already have an active pregnancy record.'])
                ->withInput();
        }

        $assessment = app(MaternalRiskService::class)->assess([
            'age' => Auth::user()->age,
            'blood_pressure' => $this->combineBloodPressure($request),
            'weight' => $request->weight,
            'height' => $request->height,
            'smoking_status' => $request->smoking_status,
            'alcohol_status' => $request->alcohol_status,
            'drug_use_status' => $request->drug_use_status,
            'obstetric_history' => $request->obstetric_history,
        ]);

        $pregnancy = Pregnancy::create([
            'user_id' => Auth::id(),
            'lmp' => $request->lmp,
            'gravida' => $request->gravida,
            'para' => $request->para,
            'risk_assessment_mode' => 'automatic',
            'risk_level' => $assessment['risk_level'],
            'risk_notes' => $assessment['reasons'] ? implode(' ', $assessment['reasons']) : null,
            'is_high_risk' => $assessment['is_high_risk'],
            'notes' => $request->notes,
            // Self-reported: enters the BHW President → Midwife validation queue,
            // never treated as clinically validated on creation.
            'workflow_status' => 'submitted_to_bhw_president',
        ]);

        HealthRecord::create([
            'user_id' => Auth::id(),
            'pregnancy_id' => $pregnancy->id,
            'bp' => $this->combineBloodPressure($request),
            'weight' => $request->weight,
            'height' => $request->height,
            'bmi' => $assessment['bmi'],
            'gestational_age' => $pregnancy->aog,
            'smoking_status' => $request->smoking_status,
            'alcohol_use' => $request->alcohol_status,
            'drug_use' => $request->drug_use_status,
            'lifestyle_notes' => $request->lifestyle_notes,
            'obstetric_history' => $request->obstetric_history,
            'notes' => $request->notes,
            'risk_level' => $assessment['risk_level'],
            'risk_assessment_mode' => 'automatic',
            'risk_notes' => $assessment['reasons'] ? implode(' ', $assessment['reasons']) : null,
            // Self-reported: enters the BHW President → Midwife validation queue.
            'workflow_status' => 'submitted_to_bhw_president',
        ]);

        // Create maternal care target client with obstetric data
        if ($request->filled('gravida') || $request->filled('para')) {
            \App\Models\MaternalCareTargetClient::updateOrCreate(
                ['user_id' => Auth::id(), 'pregnancy_id' => $pregnancy->id],
                [
                    'gravida' => $request->gravida,
                    'parity' => $request->para,
                ]
            );
        }

        // Self-report loop: alert the care team so the report doesn't sit unseen.
        $reporter = Auth::user();
        $reportDetail = "{$reporter->name} self-reported a pregnancy (LMP {$pregnancy->lmp->format('M d, Y')})."
            . ($reporter->contact_number ? " Patient contact: {$reporter->contact_number}." : '');
        $careTeam = \App\Models\User::whereIn('role', ['midwife', 'bhw_president'])
            ->where('status', 'approved')
            ->when($reporter->barangay, fn ($query) => $query->where(fn ($w) => $w->whereNull('barangay')->orWhere('barangay', $reporter->barangay)))
            ->get();
        foreach ($careTeam as $member) {
            \App\Models\Notification::createNotification(
                $member->id,
                $reportDetail,
                '🤰 Patient Self-Reported Pregnancy',
                'info',
                $member->role === 'midwife' ? route('midwife.pregnancies.index') : route('bhw-president.pregnancies.index'),
                $member->role
            );
        }
        if ($reporter->created_by_bhw_id) {
            \App\Models\Notification::createNotification(
                (int) $reporter->created_by_bhw_id,
                $reportDetail,
                '🤰 Your patient self-reported a pregnancy',
                'info',
                route('bhw.pregnancies.index'),
                'bhw'
            );
        }

        return redirect()->route('user.pregnancies.index')
            ->with('success', 'Pregnancy reported successfully. Your midwife and health worker have been notified.');
    }

    // Show single pregnancy details
    public function showPregnancy($id)
    {
        $pregnancy = Pregnancy::with([
            'healthRecords' => fn($q) => $q->orderBy('created_at', 'desc'),
            'maternalCareTargetClient',
        ])->findOrFail($id);

        if ($pregnancy->user_id !== Auth::id()) {
            abort(404);
        }

        $maternalCare = $pregnancy->maternalCareTargetClient;
        $checkups = Auth::user()->checkups()
            ->where('pregnancy_id', $pregnancy->id)
            ->orderBy('scheduled_date', 'desc')
            ->limit(10)
            ->get();
        $nextCheckup = Auth::user()->checkups()->scheduled()->upcoming()->orderBy('scheduled_date')->first();

        return view('user.pregnancies.show', compact('pregnancy', 'maternalCare', 'checkups', 'nextCheckup'));
    }

    // Menstruation Tracking
    public function menstruation()
    {
        $records = Auth::user()->cycles()
            ->orderBy('period_start_date', 'desc')
            ->paginate(10);

        // Use Cycle model for predictions
        $nextPeriod = Cycle::predictNextPeriod(Auth::id());
        $averageCycle = Cycle::getAverageCycleLength(Auth::id());
        $averagePeriod = Cycle::getAveragePeriodLength(Auth::id());

        $predictionDetail = app(CyclePredictionService::class)->getPredictionDetail(Auth::id());
        return view('user.menstruation.index', compact('records', 'nextPeriod', 'averageCycle', 'averagePeriod', 'predictionDetail'));
    }

    public function createMenstruationRecord()
    {
        return view('user.menstruation.create');
    }

    public function storeMenstruationRecord(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|before_or_equal:today|unique:cycles,period_start_date,NULL,id,user_id,'.Auth::id(),
            'end_date' => 'nullable|date|after_or_equal:start_date|before_or_equal:today',
            'notes' => 'nullable|string',
        ], ['start_date.unique' => 'That period start date is already logged.']);

        $record = Cycle::create([
            'user_id' => Auth::id(),
            'period_start_date' => $validated['start_date'],
            'period_end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('user.menstruation.index')
            ->with('success', 'Period record added successfully');
    }

    public function destroyMenstruationRecord($id)
    {
        $record = Cycle::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $record->delete();

        return redirect()->route('user.menstruation.index')
            ->with('success', 'Period record archived successfully');
    }

    // Menstruation Calendar with Cycle Prediction
    public function menstruationCalendar()
    {
        $userId = Auth::id();
        $validated = request()->validate(['date' => 'nullable|date']);
        // Get current month/year from request or use current
        try {
            $currentDate = !empty($validated['date']) ? \Carbon\Carbon::parse($validated['date']) : now();
        } catch (\Throwable $e) {
            $currentDate = now();
        }
        $month = $currentDate->month;
        $year = $currentDate->year;
        
        // Get calendar data using CyclePredictionService
        $cyclePredictionService = new CyclePredictionService();
        $calendarData = $cyclePredictionService->getCalendarData($userId, $year, $month);
        
        $calendarDays = $calendarData['calendar_days'];
        $cycles = $calendarData['cycles'];
        $predictions = $calendarData['predictions'];
        $currentCyclePrediction = $calendarData['current_cycle_prediction'] ?? null;
        $averageCycle = $calendarData['average_cycle_length'];
        $averagePeriod = $calendarData['average_period_length'];
        $predictionDetail = $calendarData['prediction_detail'];
        
        // Get last period for reference
        $lastPeriod = Cycle::where('user_id', $userId)
            ->orderBy('period_start_date', 'desc')
            ->first();
        
        return view('user.menstruation.calendar', compact(
            'calendarDays',
            'month',
            'year',
            'currentDate',
            'cycles',
            'predictions',
            'currentCyclePrediction',
            'averageCycle',
            'averagePeriod',
            'predictionDetail',
            'lastPeriod'
        ));
    }

    // View Checkups (Read-only)
    public function checkups()
    {
        $base = Auth::user()->checkups();
        $totals = [
            'all' => (clone $base)->count(),
            'scheduled' => (clone $base)->where('status', 'Scheduled')->count(),
            'completed' => (clone $base)->where('status', 'Completed')->count(),
            'missed' => (clone $base)->where('status', 'Missed')->count(),
        ];
        $checkups = Auth::user()->checkups()
            ->with('midwife')
            ->orderBy('scheduled_date', 'desc')
            ->paginate(10);

        return view('user.checkups', compact('checkups', 'totals'));
    }

    // View Health Records (Read-only)
    public function healthRecords()
    {
        $healthRecords = Auth::user()->healthRecords()
            ->with('recordedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.health-records', compact('healthRecords'));
    }

    // Create Health Record (Self-record)
    public function createHealthRecord()
    {
        return view('user.health-records.create');
    }

    // Store Health Record (Self-record)
    public function storeHealthRecord(Request $request)
    {
        $request->validate([
            'bp_systolic' => 'required|integer|min:50|max:300',
            'bp_diastolic' => 'required|integer|min:30|max:200',
            'weight' => 'required|numeric|min:0|max:300',
            'heart_rate' => 'required|integer|min:0|max:250',
            'temperature' => 'required|numeric|min:30|max:45',
            'notes' => 'nullable|string|max:1000',
        ]);

        HealthRecord::create([
            'user_id' => Auth::id(),
            'pregnancy_id' => HealthRecord::resolvePregnancyIdForWoman(Auth::id(), now()),
            'bp' => $request->bp_systolic.'/'.$request->bp_diastolic,
            'weight' => $request->weight,
            'heart_rate' => $request->heart_rate,
            'temperature' => $request->temperature,
            'notes' => $request->notes,
            'recorded_by_user_id' => Auth::id(),
            'risk_level' => 'Low', // Default risk level, can be updated by health worker
            // Self-reported: enters the BHW President → Midwife validation queue.
            'workflow_status' => 'submitted_to_bhw_president',
        ]);

        return redirect()->route('user.health-records')
            ->with('success', 'Health record submitted successfully. A health worker will review and validate it.');
    }

    // Notifications
    public function notifications()
    {
        $notifications = Auth::user()->notifications()
            ->orderByRaw('COALESCE(last_reminded_at, created_at) DESC')
            ->paginate(20);

        return view('user.notifications', compact('notifications'));
    }

    // Settings
    public function settings()
    {
        $user = Auth::user();
        $barangays = \App\Models\Barangay::allNames();
        return view('user.settings', compact('user', 'barangays'));
    }

    // Menstruation Statistics
    public function menstruationStatistics()
    {
        $userId = Auth::id();
        
        // Get all period records
        $records = Cycle::where('user_id', $userId)
            ->orderBy('period_start_date', 'desc')
            ->get();
        
        // Calculate statistics using Cycle model
        $averageCycle = Cycle::getAverageCycleLength($userId);
        $averagePeriod = Cycle::getAveragePeriodLength($userId);
        
        // Calculate cycle length for each period
        $cycleHistory = [];
        $cycleLengths = [];
        
        for ($i = 0; $i < $records->count() - 1; $i++) {
            $current = $records[$i];
            $next = $records[$i + 1];
            
            $cycleLength = $next->period_start_date->diffInDays($current->period_start_date, false);
            $cycleLengths[] = $cycleLength;
            
            $isNormal = $this->isCycleLengthNormal($cycleLength);
            
            $cycleHistory[] = [
                'id' => $current->id,
                'cycle_length' => $cycleLength,
                'period_length' => $current->period_length,
                'period_start_date' => $current->period_start_date,
                'period_end_date' => $current->period_end_date,
                'is_normal' => $isNormal,
            ];
        }
        
        return view('user.menstruation.statistics', compact(
            'records',
            'averageCycle',
            'averagePeriod',
            'cycleHistory',
            'cycleLengths'
        ));
    }

    private function isCycleLengthNormal($days)
    {
        // Normal cycle length: 21-35 days
        return $days >= 21 && $days <= 35;
    }

    // Generate PDF Report for Doctor
    public function generateMenstruationReport()
    {
        $userId = Auth::id();
        $user = Auth::user();
        
        // Get period records
        $records = Cycle::where('user_id', $userId)
            ->orderBy('period_start_date', 'desc')
            ->take(6)
            ->get();
        
        // Get statistics using Cycle model
        $averageCycle = Cycle::getAverageCycleLength($userId);
        $averagePeriod = Cycle::getAveragePeriodLength($userId);
        
        // Get daily tracking data
        $dailyData = MenstruationDaily::where('user_id', $userId)
            ->where('date', '>=', now()->subMonths(3))
            ->orderBy('date', 'desc')
            ->get();
        
        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('user.menstruation.report', compact(
            'user',
            'records',
            'averageCycle',
            'averagePeriod',
            'dailyData'
        ));
        
        return $pdf->download('menstrual-cycle-report-' . now()->format('Y-m-d') . '.pdf');
    }

    // ============================================
    // NEW CYCLE TRACKING API (Task 1/5)
    // ============================================

    // GET /cycles - List all cycles for the user
    public function getCycles()
    {
        $cycles = Auth::user()->cycles()
            ->orderBy('period_start_date', 'desc')
            ->get();

        return response()->json([
            'cycles' => $cycles,
            'average_cycle_length' => Cycle::getAverageCycleLength(Auth::id()),
            'average_period_length' => Cycle::getAveragePeriodLength(Auth::id()),
        ]);
    }

    // POST /cycles - Store a new cycle
    public function storeCycle(Request $request)
    {
        $validated = $request->validate([
            'period_start_date' => 'required|date|before_or_equal:today|unique:cycles,period_start_date,NULL,id,user_id,'.Auth::id(),
            'period_end_date' => 'nullable|date|after_or_equal:period_start_date|before_or_equal:today',
            'notes' => 'nullable|string',
        ], ['period_start_date.unique' => 'That period start date is already logged.']);

        Auth::user()->cycles()->create($validated);

        // Recalculate cycle lengths
        $this->recalculateCycleLengths(Auth::id());

        return redirect()->route('user.menstruation.index')
            ->with('success', 'Cycle recorded successfully');
    }

    // DELETE /cycles/{id} - Delete a cycle
    public function destroyCycle($id)
    {
        $cycle = Cycle::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $cycle->delete();

        // Recalculate cycle lengths
        $this->recalculateCycleLengths(Auth::id());

        return redirect()->route('user.menstruation.index')
            ->with('success', 'Cycle archived successfully');
    }

    // GET /cycles/prediction - Get next period prediction
    public function getCyclePrediction()
    {
        $userId = Auth::id();
        $nextPeriod = Cycle::predictNextPeriod($userId);
        $averageCycle = Cycle::getAverageCycleLength($userId);
        $averagePeriod = Cycle::getAveragePeriodLength($userId);

        $cycles = Cycle::where('user_id', $userId)
            ->orderBy('period_start_date', 'desc')
            ->take(6)
            ->get();

        $detail = app(CyclePredictionService::class)->getPredictionDetail($userId);
        return response()->json([
            'next_period_date' => $nextPeriod?->format('Y-m-d'),
            'next_period_formatted' => $nextPeriod?->format('F j, Y'),
            'days_until' => $nextPeriod ? (int) today()->diffInDays($nextPeriod, false) : null,
            'average_cycle_length' => $averageCycle,
            'average_period_length' => $averagePeriod,
            'recent_cycles' => $cycles,
            'confidence' => $detail['confidence'],
            'regularity' => $detail['regularity'],
            'next_earliest' => $detail['next_earliest']?->toDateString(),
            'next_latest' => $detail['next_latest']?->toDateString(),
        ]);
    }

    // GET /cycles/calendar - Get calendar data with periods and predictions
    public function getCycleCalendar(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $calendarData = Cycle::getCalendarData(Auth::id(), $year, $month);

        return response()->json([
            'year' => $year,
            'month' => $month,
            'calendar' => $calendarData,
        ]);
    }

    // Helper method to recalculate cycle lengths using service
    private function recalculateCycleLengths($userId)
    {
        $service = new CyclePredictionService();
        $service->recalculateCycleLengths($userId);
    }

    // ============================================

    // ============================================
    // CONSOLIDATED STATUS ENDPOINT (Task 4/5)
    // ============================================

    // GET /cycle-status - Get consolidated cycle status
    public function getCycleStatus(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = isset($validated['date']) ? \Carbon\Carbon::parse($validated['date']) : now();
        $userId = Auth::id();

        // Get cycle prediction
        $cycleService = new CyclePredictionService();
        $nextPeriod = $cycleService->predictNextPeriod($userId);
        $averageCycleLength = $cycleService->getAverageCycleLength($userId) ?? 28;

        if (!$nextPeriod) {
            return response()->json([
                'message' => 'No cycle data available. Please log your period first.',
            ], 404);
        }

        return response()->json([
            'next_period' => $nextPeriod->format('Y-m-d'),
            'cycle_length_avg' => $averageCycleLength,
        ]);
    }

    private function combineBloodPressure(Request $request): ?string
    {
        $systolic = $request->input('bp_systolic');
        $diastolic = $request->input('bp_diastolic');

        if ($systolic !== null && $systolic !== '' && $diastolic !== null && $diastolic !== '') {
            return $systolic . '/' . $diastolic;
        }

        return null;
    }

    private function mergePregnancyLifestyleInputs(Request $request): void
    {
        $otherLifestyle = trim((string) $request->input('lifestyle_other', ''));
        $notes = trim((string) $request->input('lifestyle_notes', ''));

        if ($otherLifestyle !== '') {
            $notes = trim($notes !== '' ? $notes . "\nOther: " . $otherLifestyle : 'Other: ' . $otherLifestyle);
        }

        $request->merge([
            'smoking_status' => $request->boolean('lifestyle_smoking') ? 'current' : 'none',
            'alcohol_status' => $request->boolean('lifestyle_alcohol') ? 'current' : 'none',
            'drug_use_status' => $request->boolean('lifestyle_drugs') ? 'current' : 'none',
            'lifestyle_notes' => $notes !== '' ? $notes : null,
        ]);
    }
}
