<?php

namespace App\Http\Controllers;

use App\Models\BhwMonthlyReport;
use App\Models\User;
use App\Models\Checkup;
use App\Models\HealthRecord;
use App\Models\CheckupReferral;
use App\Models\WalkInPatient;
use App\Models\Pregnancy;
use App\Models\Purok;
use App\Services\MaternalRiskService;
use App\Services\RiskAnalysisService;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BhwController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Dashboard
    public function dashboard()
    {
        // Auto-run alert checks when BHW visits dashboard
        try {
            // Check for overdue checkups
            Checkup::markOverdueCheckups();

            // Re-evaluate risk for women with missed checkups
            $missedWomen = Checkup::missed()->distinct('user_id')->pluck('user_id');
            $riskService = new RiskAnalysisService();
            foreach ($missedWomen as $womanId) {
                $riskService->evaluate($womanId);
            }
        } catch (\Exception $e) {
            \Log::error('BHW dashboard alert check failed: ' . $e->getMessage());
        }

        // Cache dashboard statistics for 5 minutes (300 seconds) for faster loading
        $cacheKey = 'bhw_dashboard_stats_' . auth()->id();
        $stats = Cache::remember($cacheKey, 300, function () {
            return [
                'totalPatients' => User::where('role', 'user')->count(),
                'scheduledCheckups' => Checkup::scheduled()->count(),
                'todayCheckups' => Checkup::whereDate('scheduled_date', today())
                    ->scheduled()
                    ->count(),
                'myHealthRecords' => HealthRecord::byBhw()
                    ->where('recorded_by_id', auth()->id())
                    ->count(),
            ];
        });

        // Only select needed columns for better performance
        $upcomingCheckups = Checkup::with(['woman:id,first_name,middle_initial,last_name', 'midwife:id,first_name,middle_initial,last_name'])
            ->select('id', 'user_id', 'midwife_id', 'scheduled_date', 'status', 'purpose')
            ->scheduled()
            ->upcoming()
            ->orderBy('scheduled_date', 'asc')
            ->limit(10)
            ->get();

        return view('bhw.dashboard', array_merge($stats, compact('upcomingCheckups')));
    }

    // Create Woman (registered or unregistered)
    public function createWoman()
    {
        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);
        return view('bhw.women.create', compact('barangays'));
    }

    // Store Woman
    public function storeWoman(Request $request)
    {
        // Merge checkbox value to boolean
        $request->merge([
            'is_pregnant' => $request->has('is_pregnant'),
        ]);

        // patient_type values: 'registered' = Enrolled Account (portal access),
        // 'unregistered' = Unlinked Profile / BHW-Managed field record (no login).
        $request->validate([
            'patient_type' => 'required|in:registered,unregistered',
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:10',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'barangay' => 'nullable|string|max:255',
            'purok' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'reason_for_visit' => 'nullable|string|max:500',
            'is_pregnant' => 'boolean',
            'lmp' => 'nullable|date|before:today|required_if:is_pregnant,true',
            'edd' => 'nullable|date|after:lmp',
            'gravida' => 'nullable|integer|min:1',
            'para' => 'nullable|integer|min:0',
            'gtpal' => 'nullable|string|max:20',
            'obstetric_history' => 'nullable|string',
            // Synced with self-registration needs (emergency contact)
            'emergency_name_1' => 'nullable|string|max:255',
            'emergency_relationship_1' => 'nullable|string|max:255',
            'emergency_contact_number_1' => 'nullable|string|max:255',
        ]);

        $userId = null;

        if ($request->patient_type === 'registered') {
            // Enrolled Account created by staff: same needs as self-registration
            // (contact + primary emergency contact) but NO verification queue —
            // staff verify identity in person, so the account is auto-approved.
            $request->validate([
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'barangay' => 'required|string|max:255',
                'contact_number' => 'required|string|max:20',
                'emergency_name_1' => 'required|string|max:255',
                'emergency_relationship_1' => 'required|string|max:255',
                'emergency_contact_number_1' => 'required|string|max:255',
            ]);

            $user = \App\Models\User::create([
                'first_name' => $request->first_name,
                'middle_initial' => $request->middle_initial,
                'last_name' => $request->last_name,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender ?? 'female',
                'contact_number' => $request->contact_number,
                'address' => $request->address ?: implode(', ', array_filter([
                    $request->filled('purok') ? $request->purok : null,
                    $request->barangay,
                    'San Carlos City, Pangasinan',
                ])),
                'barangay' => $request->barangay,
                'purok_id' => \App\Models\Purok::resolveIdFromText($request->purok, $request->barangay),
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => 'user',
                'status' => 'approved', // staff-added: verified in person, no approval needed
                'created_by_bhw_id' => auth()->id(),
            ]);

            $user->emergencyContacts()->create([
                'name' => $request->emergency_name_1,
                'relationship' => $request->emergency_relationship_1,
                'contact_number' => $request->emergency_contact_number_1,
                'contact_order' => 1,
                'is_primary' => true,
            ]);

            $userId = $user->id;

            // Create pregnancy record if pregnant
            if ($request->is_pregnant) {
                $edd = $request->edd;
                if (!$edd && $request->lmp) {
                    // Calculate EDD from LMP (add 280 days)
                    $edd = \Carbon\Carbon::parse($request->lmp)->addDays(280);
                }

                $pregnancy = \App\Models\Pregnancy::create([
                    'user_id' => $user->id,
                    'lmp' => $request->lmp ? \Carbon\Carbon::parse($request->lmp) : null,
                    'edd' => $edd ? \Carbon\Carbon::parse($edd) : null,
                    'risk_level' => 'Low',
                    'workflow_status' => 'draft',
                ]);

                // Create maternal care target client with obstetric data
                if ($request->filled('gravida') || $request->filled('para')) {
                    \App\Models\MaternalCareTargetClient::updateOrCreate(
                        ['user_id' => $user->id, 'pregnancy_id' => $pregnancy->id],
                        [
                            'gravida' => $request->gravida ?? 1,
                            'parity' => $request->para ?? 0,
                        ]
                    );
                }
            }

            return redirect()->route('bhw.patients')
                ->with('success', 'Enrolled Account created successfully — portal access is active, no approval needed.' . ($request->is_pregnant ? ' Pregnancy record added.' : ''));
        } else {
            // Unlinked Profile (BHW-Managed field entry): no login credentials.
            // A contact number is required so the record stays reachable like an
            // enrolled account; user_id stays NULL with has_portal_access = false.
            $request->validate([
                'contact_number' => 'required|string|max:20',
                'barangay' => 'required|string|max:255',
            ]);

            // BHW Field Entry: row with user_id = NULL, has_portal_access = false.
            $walkInPatient = \App\Models\WalkInPatient::create([
                'recorded_by_id' => auth()->id(),
                'user_id' => null,
                'has_portal_access' => false,
                'first_name' => $request->first_name,
                'middle_initial' => $request->middle_initial,
                'last_name' => $request->last_name,
                'date_of_birth' => $request->date_of_birth,
                'contact_number' => $request->contact_number,
                'address' => $request->address ?: implode(', ', array_filter([
                    $request->filled('purok') ? $request->purok : null,
                    $request->barangay,
                    'San Carlos City, Pangasinan',
                ])),
                'barangay' => $request->barangay ?: 'Barangay Burgos Padlan, San Carlos City, Pangasinan',
                'purok_id' => \App\Models\Purok::resolveIdFromText($request->purok, $request->barangay),
                'reason_for_visit' => $request->reason_for_visit,
                'notes' => $request->gravida ? "Gravida: {$request->gravida}, Para: {$request->para}" : null,
            ]);

            // Create pregnancy record if pregnant
            if ($request->is_pregnant) {
                $edd = $request->edd;
                if (!$edd && $request->lmp) {
                    // Calculate EDD from LMP (add 280 days)
                    $edd = \Carbon\Carbon::parse($request->lmp)->addDays(280);
                }

                $pregnancy = \App\Models\Pregnancy::create([
                    'walk_in_patient_id' => $walkInPatient->id,
                    'lmp' => $request->lmp ? \Carbon\Carbon::parse($request->lmp) : null,
                    'edd' => $edd ? \Carbon\Carbon::parse($edd) : null,
                    'risk_level' => 'Low',
                    'workflow_status' => 'draft',
                ]);

                // Create maternal care target client with obstetric data
                if ($request->filled('gravida') || $request->filled('para')) {
                    \App\Models\MaternalCareTargetClient::updateOrCreate(
                        ['pregnancy_id' => $pregnancy->id],
                        [
                            'gravida' => $request->gravida ?? 1,
                            'parity' => $request->para ?? 0,
                        ]
                    );
                }
            }

            return redirect()->route('bhw.patients', ['filter' => 'unregistered'])
                ->with('success', 'Unlinked Profile saved (BHW-Managed, Field Record Only — no portal account).' . ($request->is_pregnant ? ' Pregnancy record added.' : ''));
        }
    }

    // View Patients (all women combined like midwife)
    public function patients()
    {
        $search = request('search');
        $filter = request('filter', 'all');

        // Get registered patients
        $registeredQuery = User::where('role', 'user')
            ->with(['pregnancies' => function($query) {
                $query->active();
            }, 'purok']);

        if ($search) {
            $registeredQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('middle_initial', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('contact_number', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%')
                  ->orWhere('barangay', 'like', '%' . $search . '%');
            });
        }

        $registeredPatients = $registeredQuery->get()->map(function($patient) {
            $patient->type = 'registered';
            return $patient;
        });

        // Get unlinked profiles (BHW-Managed field records, no portal account)
        $unregisteredQuery = WalkInPatient::with(['recordedBy', 'purok', 'convertedToUser', 'user'])
            ->whereNull('converted_to_user_id')
            ->whereNull('user_id');

        if ($search) {
            $unregisteredQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('middle_initial', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%')
                    ->orWhere('barangay', 'like', '%' . $search . '%')
                    ->orWhere('contact_number', 'like', '%' . $search . '%');
            });
        }

        $unregisteredPatients = $unregisteredQuery->latest()->get()->map(function($patient) {
            $patient->type = 'unregistered';
            return $patient;
        });

        // Combine both collections
        $allPatients = app(\App\Services\PatientPresentation::class)->sortPatients($registeredPatients->concat($unregisteredPatients));

        // Apply filter
        if ($filter === 'registered') {
            $allPatients = $allPatients->where('type', 'registered');
        } elseif ($filter === 'unregistered') {
            $allPatients = $allPatients->where('type', 'unregistered');
        }

        // Paginate manually
        $page = request('page', 1);
        $perPage = 10;
        $patients = new \Illuminate\Pagination\LengthAwarePaginator(
            $allPatients->forPage($page, $perPage),
            $allPatients->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Get statistics
        $scheduledCheckups = Checkup::where('status', 'scheduled')->count();
        $missedCheckups = Checkup::where('status', 'missed')->count();
        $registeredCount = User::where('role', 'user')->count();
        $unregisteredCount = WalkInPatient::whereNull('converted_to_user_id')->whereNull('user_id')->count();

        $trashCount = WalkInPatient::onlyTrashed()->count();

        return view('bhw.patients', compact(
            'patients',
            'scheduledCheckups',
            'missedCheckups',
            'registeredCount',
            'unregisteredCount',
            'trashCount'
        ));
    }

    public function patientDetails($id)
    {
        $woman = User::where('role', 'user')->with([
            'purok',
            'emergencyContacts',
            'checkups' => function($q) {
                $q->with('midwife')->latest()->select('id', 'user_id', 'midwife_id', 'scheduled_date', 'status', 'purpose');
            },
            'healthRecords' => function($q) {
                $q->with('recordedBy')->latest()->select('id', 'user_id', 'recorded_by_id', 'bp', 'weight', 'heart_rate', 'temperature', 'risk_level', 'notes', 'created_at')->take(50);
            }
        ])->select('id', 'first_name', 'middle_initial', 'last_name', 'email', 'address', 'barangay', 'purok_id', 'date_of_birth', 'contact_number', 'partner_name', 'partner_contact', 'profile_image', 'gender', 'created_at')->findOrFail($id);

        // Patient-seen status for the latest risk alert so the managing
        // BHW can tell whether the patient opened it.
        $riskAlertStatus = \App\Services\SmartNotificationService::patientRiskAlertStatus($woman->id);

        return view('bhw.patient-details', [
            'woman' => $woman,
            'checkups' => $woman->checkups,
            'healthRecords' => $woman->healthRecords,
            'riskAlertStatus' => $riskAlertStatus,
        ]);
    }

    // View patient menstrual cycle records
    public function patientMenstruation($id)
    {
        $woman = User::where('role', 'user')->findOrFail($id);
        $records = $woman->cycles()
            ->orderBy('period_start_date', 'desc')
            ->get();

        // Get statistics using Cycle model
        $averageCycle = \App\Models\Cycle::getAverageCycleLength($id);
        $averagePeriod = \App\Models\Cycle::getAveragePeriodLength($id);

        return view('bhw.patient-menstruation', compact('woman', 'records', 'averageCycle', 'averagePeriod'));
    }

    public function patientMenstruationReport($id)
    {
        $woman = User::where('role', 'user')->findOrFail($id);
        $records = $woman->cycles()
            ->orderBy('period_start_date', 'desc')
            ->get();

        // Get statistics
        $averageCycle = \App\Models\Cycle::getAverageCycleLength($id);
        $averagePeriod = \App\Models\Cycle::getAveragePeriodLength($id);

        // Get predictions (simplified without fertility service)
        $predictions = [];
        foreach ($records as $record) {
            $prediction = [
                'start_date' => $record->period_start_date,
                'end_date' => $record->period_end_date,
                'ovulation' => $record->period_start_date ? $record->period_start_date->copy()->addDays($averageCycle - 14) : null,
                'fertile_window' => [
                    'start' => $record->period_start_date ? $record->period_start_date->copy()->addDays($averageCycle - 19) : null,
                    'end' => $record->period_start_date ? $record->period_start_date->copy()->addDays($averageCycle - 13) : null,
                ],
            ];
            $predictions[] = $prediction;
        }

        return view('bhw.patient-menstruation-report', compact('woman', 'records', 'averageCycle', 'averagePeriod', 'predictions'));
    }

    public function patientMenstruationExport($id)
    {
        $woman = User::where('role', 'user')->findOrFail($id);
        $records = $woman->cycles()
            ->orderBy('period_start_date', 'desc')
            ->get();

        $averageCycle = \App\Models\Cycle::getAverageCycleLength($id);
        $averagePeriod = \App\Models\Cycle::getAveragePeriodLength($id);

        $csv = fopen('php://temp', 'r+');
        
        fputcsv($csv, ['Woman Name', $woman->name]);
        fputcsv($csv, ['Average Cycle Length', $averageCycle ?? 'N/A']);
        fputcsv($csv, ['Average Period Duration', $averagePeriod ?? 'N/A']);
        fputcsv($csv, []);
        fputcsv($csv, ['Start Date', 'End Date', 'Duration (days)', 'Notes']);

        foreach ($records as $record) {
            fputcsv($csv, [
                optional($record->period_start_date)->format('Y-m-d') ?? 'N/A',
                $record->period_end_date ? $record->period_end_date->format('Y-m-d') : 'Ongoing',
                $record->period_length ?? 'N/A',
                $record->notes ?? 'N/A',
            ]);
        }

        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="menstrual_cycle_report_' . $woman->name . '.csv"');
    }

    // View Schedules - BHW can only view scheduled checkups
    public function schedules()
    {
        $checkups = Checkup::with(['woman', 'walkInPatient', 'midwife', 'scheduledBy'])
            ->scheduled()
            ->orderBy('scheduled_date', 'asc')
            ->paginate(10);

        return view('bhw.checkups.index', compact('checkups'));
    }

    // Health Records (Add Only - No Edit/Delete)
    public function healthRecords()
    {
        $healthRecords = HealthRecord::with(['woman', 'walkInPatient', 'recordedBy', 'bhwPresident'])
            ->where('recorded_by_id', auth()->id())
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('woman', function ($womanQuery) use ($search) {
                        $womanQuery->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('middle_initial', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })->orWhereHas('walkInPatient', function ($walkInQuery) use ($search) {
                        $walkInQuery->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('middle_initial', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('contact_number', 'like', '%' . $search . '%');
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bhw.health-records.index', compact('healthRecords'));
    }

    public function createHealthRecord($userId = null, $walkInPatientId = null)
    {
        if ($userId) {
            $woman = User::where('role', 'user')->findOrFail($userId);
            return view('bhw.health-records.create', compact('woman'));
        } elseif ($walkInPatientId) {
            $walkInPatient = WalkInPatient::findOrFail($walkInPatientId);
            return view('bhw.health-records.create-walk-in', compact('walkInPatient'));
        }
        $women = User::where('role', 'user')->where('status', 'approved')->orderBy('first_name')->limit(100)->get();
        $walkIns = WalkInPatient::notConverted()->latest()->limit(50)->get();
        return view('bhw.health-records.create-picker', compact('women', 'walkIns'));
    }

    public function storeHealthRecord(Request $request)
    {
        $this->mergeLifestyleInputs($request);

        $request->validate([
            'patient_type' => 'required|in:registered,walk_in',
            'user_id' => 'exclude_unless:patient_type,registered|required|exists:users,id',
            'walk_in_patient_id' => 'exclude_unless:patient_type,walk_in|required|exists:walk_in_patients,id',
            'bp_systolic'        => 'required|integer|min:50|max:300',
            'bp_diastolic'       => 'required|integer|min:30|max:200',
            'weight'             => 'required|numeric|min:0|max:300',
            'height'             => 'nullable|numeric|min:100|max:250',
            'heart_rate'         => 'required|integer|min:0|max:250',
            'temperature'        => 'required|numeric|min:30|max:45',
            'hemoglobin'         => 'nullable|numeric|min:1|max:25',
            'gestational_age'    => 'nullable|integer|min:1|max:45',
            'immunization_status'=> 'nullable|string|max:255',
            'contraceptive_use'  => 'nullable|string|max:255',
            'lab_results'        => 'nullable|string|max:500',
            'smoking_status'     => 'nullable|in:none,former,current',
            'alcohol_use'        => 'nullable|in:none,former,current',
            'drug_use'           => 'nullable|in:none,former,current',
            'lifestyle_notes'    => 'nullable|string|max:1000',
            'obstetric_history'  => 'nullable|string|max:2000',
            'risk_assessment_mode' => 'nullable|in:automatic,manual',
            'risk_level'         => 'nullable|in:Low,Medium,High',
            'risk_notes'         => 'nullable|string|max:1000',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $patientType = $request->patient_type;
        $userId = $request->user_id;
        $walkInPatientId = $request->walk_in_patient_id;

        // Get patient age for risk assessment
        $age = null;
        $barangay = null;

        if ($patientType === 'registered') {
            $patient = User::find($userId);
            $age = $patient?->age;
            $barangay = $patient?->barangay;
        } else {
            $patient = WalkInPatient::find($walkInPatientId);
            $age = $patient?->age;
            $barangay = $patient?->barangay;
        }

        $riskMode = $request->input('risk_assessment_mode', 'automatic');
        if ($riskMode === 'manual') {
            $riskLevel = $request->input('risk_level', 'Low');
            $bmi = app(MaternalRiskService::class)->calculateBmi($request->weight, $request->height);
        } else {
            $assessment = app(MaternalRiskService::class)->assess([
                'age' => $age,
                'bp' => $this->combineBloodPressure($request),
                'weight' => $request->weight,
                'height' => $request->height,
                'smoking_status' => $request->smoking_status,
                'alcohol_use' => $request->alcohol_use,
                'drug_use' => $request->drug_use,
                'obstetric_history' => $request->obstetric_history,
            ]);
            $riskLevel = $assessment['risk_level'];
            $bmi = $assessment['bmi'];
        }

        // Resolve pregnancy ID (only for registered users)
        $pregnancyId = null;
        if ($patientType === 'registered' && $userId) {
            $pregnancyId = \App\Models\HealthRecord::resolvePregnancyIdForWoman((int) $userId, now());
        }

        // Find BHW President
        $bhwPresidentId = User::where('role', 'bhw_president')
            ->where('status', 'approved')
            ->where(function ($query) use ($barangay) {
                $query->whereNull('barangay')
                    ->orWhere('barangay', $barangay);
            })
            ->value('id');

        \App\Models\HealthRecord::create([
            'user_id'            => $patientType === 'registered' ? $userId : null,
            'walk_in_patient_id' => $patientType === 'walk_in' ? $walkInPatientId : null,
            'pregnancy_id'        => $pregnancyId,
            'bp'                  => $this->combineBloodPressure($request),
            'weight'              => $request->weight,
            'height'              => $request->height,
            'bmi'                 => $bmi,
            'heart_rate'          => $request->heart_rate,
            'temperature'         => $request->temperature,
            'hemoglobin'          => $request->hemoglobin,
            'gestational_age'     => $request->gestational_age,
            'immunization_status' => $request->immunization_status,
            'contraceptive_use'   => $request->contraceptive_use,
            'lab_results'         => $request->lab_results,
            'smoking_status'      => $request->smoking_status,
            'alcohol_use'         => $request->alcohol_use,
            'drug_use'            => $request->drug_use,
            'lifestyle_notes'     => $request->lifestyle_notes,
            'obstetric_history'   => $request->obstetric_history,
            'notes'               => $request->notes,
            'risk_level'          => $riskLevel,
            'risk_assessment_mode'=> $riskMode,
            'risk_notes'          => $request->risk_notes,
            'recorded_by_id'      => auth()->id(),
            'bhw_president_id'    => $bhwPresidentId,
            'workflow_status'     => 'recorded_by_bhw',
        ]);

        // 🔴 Trigger automated risk analysis after every health record
        $detectedRisk = $riskLevel;
        if ($riskMode === 'automatic' && $patientType === 'registered') {
            $riskService = new \App\Services\RiskAnalysisService();
            $detectedRisk = $riskService->evaluate($userId);
        }
        if ($patientType === 'walk_in' && in_array($detectedRisk, ['Medium', 'High', 'Critical'], true)) {
            $walkIn = WalkInPatient::find($walkInPatientId);
            if ($walkIn) {
                try {
                    app(\App\Services\SmartNotificationService::class)->notifyWalkInRisk(
                        $walkIn,
                        "A {$detectedRisk} risk finding was recorded during your visit. Please follow your health worker's advice.",
                        $detectedRisk
                    );
                } catch (\Throwable $e) {
                }
            }
        }

        $successMsg = 'Health record added successfully.';
        if ($detectedRisk !== 'Low') {
            $successMsg .= " ⚠️ {$detectedRisk} risk level detected — notifications sent.";
        }

        // Redirect based on patient type
        if ($patientType === 'registered') {
            return redirect()->route('bhw.patient-details', $userId)
                ->with('success', $successMsg);
        } else {
            return redirect()->route('bhw.walk-in-patients.show', $walkInPatientId)
                ->with('success', $successMsg);
        }
    }

    private function combineBloodPressure(Request $request): string
    {
        return $request->input('bp_systolic') . '/' . $request->input('bp_diastolic');
    }

    public function submitHealthRecordToPresident($id)
    {
        $healthRecord = HealthRecord::where('recorded_by_id', auth()->id())->findOrFail($id);

        if ($healthRecord->workflow_status !== 'recorded_by_bhw') {
            return back()->with('error', 'This health record has already been submitted.');
        }

        $healthRecord->update([
            'workflow_status' => 'submitted_to_bhw_president',
            'submitted_to_bhw_president_at' => now(),
        ]);

        return back()->with('success', 'Health record submitted to BHW President for review.');
    }

    private function mergeLifestyleInputs(Request $request): void
    {
        $otherLifestyle = trim((string) $request->input('lifestyle_other', ''));
        $notes = trim((string) $request->input('lifestyle_notes', ''));

        if ($otherLifestyle !== '') {
            $notes = trim($notes !== '' ? $notes . "\nOther: " . $otherLifestyle : 'Other: ' . $otherLifestyle);
        }

        $request->merge([
            'smoking_status' => $request->boolean('lifestyle_smoking') ? 'current' : 'none',
            'alcohol_use' => $request->boolean('lifestyle_alcohol') ? 'current' : 'none',
            'drug_use' => $request->boolean('lifestyle_drugs') ? 'current' : 'none',
            'lifestyle_notes' => $notes !== '' ? $notes : null,
        ]);
    }

    // Archive Health Record (own draft records only — submitted records
    // go through the President → Midwife review chain instead)
    public function archiveHealthRecord(Request $request, $id)
    {
        $healthRecord = \App\Models\HealthRecord::where('recorded_by_id', auth()->id())->findOrFail($id);

        if ($healthRecord->workflow_status !== 'recorded_by_bhw') {
            return back()->with('error', 'Only draft records can be archived. Submitted records must go through review.');
        }

        $reason = trim((string) ($request->input('reason') ?? $request->input('archived_reason', '')));
        if ($reason === '') {
            $reason = 'Archived by BHW via console';
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveRecord($healthRecord, $reason, auth()->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()])->withInput();
        }

        return redirect()->route('bhw.health-records.index')
            ->with('success', 'Health record archived successfully (retained for audit).');
    }

    // Checkups - BHW can only view scheduled checkups (created by BHW or midwife)
    public function checkups()
    {
        $checkups = Checkup::with(['woman', 'walkInPatient', 'midwife', 'scheduledBy'])
            ->scheduled()
            ->orderBy('scheduled_date', 'asc')
            ->paginate(10);
        $women = User::where('role', 'user')->where('status', 'approved')
            ->orderBy('last_name')->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_initial', 'last_name']);

        return view('bhw.checkups.index', compact('checkups', 'women'));
    }

    public function createCheckup($userId)
    {
        $woman = User::where('role', 'user')->findOrFail($userId);
        return view('bhw.checkups.create', compact('woman'));
    }

    public function storeCheckup(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'scheduled_date'   => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'purpose'          => 'nullable|string|max:255',
            'notes'            => 'nullable|string|max:1000',
        ]);
        if ($request->scheduled_date === today()->toDateString() && $request->filled('scheduled_time')
            && $request->scheduled_time <= now()->format('H:i')) {
            return back()->withErrors(['scheduled_time' => 'That time today has already passed.'])->withInput();
        }
        $exists = Checkup::where('user_id', $request->user_id)
            ->whereDate('scheduled_date', $request->scheduled_date)
            ->where('status', 'Scheduled')->exists();
        if ($exists) {
            return back()->withErrors(['scheduled_date' => 'This patient already has a scheduled checkup on that date.'])->withInput();
        }

        // Auto-assign to the single midwife
        $midwife = User::where('role', 'midwife')->where('status', 'approved')->first();

        Checkup::create([
            'user_id'         => $request->user_id,
            'midwife_id'      => $midwife ? $midwife->id : null,
            'scheduled_by_id' => auth()->id(),
            'scheduled_date'     => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'purpose'            => $request->purpose,
            'notes'              => $request->notes,
            'status'             => 'Scheduled',
        ]);

        return redirect()->route('bhw.checkups.index')
            ->with('success', $midwife ? 'Checkup scheduled successfully' : 'Checkup scheduled (no midwife on file — RHU should assign one).');
    }

    // Archived checkups = completed checkups. A checkup moves here
    // automatically once the midwife marks it completed.
    public function archivedCheckups()
    {
        $checkups = Checkup::with(['woman', 'walkInPatient', 'midwife', 'scheduledBy'])
            ->where('status', 'Completed')
            ->orderBy('scheduled_date', 'desc')
            ->paginate(10);

        return view('bhw.checkups.archived', compact('checkups'));
    }

    public function reportsIndex(Request $request)
    {
        $filter = $request->input('filter', 'all');
        
        $reports = BhwMonthlyReport::with('bhw')
            ->where('bhw_id', auth()->id())
            ->when($request->filled('month'), function ($query) use ($request) {
                $query->where('report_month', $request->integer('month'));
            })
            ->when($request->filled('year'), function ($query) use ($request) {
                $query->where('report_year', $request->integer('year'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($filter !== 'all', function ($query) use ($filter) {
                $query->where('report_type', $filter);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('bhw.reports.index', compact('reports', 'filter'));
    }

    public function reportsCreate()
    {
        $women = User::where('role', 'user')->where('status', 'approved')->whereHas('healthRecords', function ($query) {
                $query->where('recorded_by_id', auth()->id());
            })
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'email']);

        $myPatientIds = User::where('role', 'user')->where('status', 'approved')->whereHas('healthRecords', function ($query) {
                $query->where('recorded_by_id', auth()->id());
            })->pluck('id');
        $pregnantWomen = User::where('role', 'user')->where('status', 'approved')->whereIn('id', $myPatientIds)->whereHas('pregnancies', function ($query) {
                $query->whereNull('ended_at');
            })
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'email']);

        return view('bhw.reports.create', [
            'women' => $women,
            'pregnantWomen' => $pregnantWomen,
            'currentMonth' => now()->month,
            'currentYear' => now()->year,
        ]);
    }

    public function reportsStore(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:health_records,pregnancies',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_month' => 'required|integer|between:1,12',
            'report_year' => 'required|integer|min:2020|max:2100',
            'patient_filter' => 'required|in:all,selected',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $patientIds = $validated['patient_filter'] === 'selected'
            ? array_values(array_unique($validated['user_ids'] ?? []))
            : [];

        $filters = [
            'patient_filter' => $validated['patient_filter'],
            'user_ids' => $patientIds,
        ];

        if ($validated['report_type'] === 'health_records') {
            $baseQuery = HealthRecord::where('recorded_by_id', auth()->id())
                ->whereMonth('created_at', $validated['report_month'])
                ->whereYear('created_at', $validated['report_year'])
                ->when($validated['patient_filter'] === 'selected', function ($query) use ($patientIds) {
                    $query->whereIn('user_id', $patientIds);
                });
            
            $totalRecords = $baseQuery->count();
        } else {
            $baseQuery = Pregnancy::whereNull('ended_at')
                ->whereMonth('created_at', $validated['report_month'])
                ->whereYear('created_at', $validated['report_year'])
                ->when($validated['patient_filter'] === 'selected', function ($query) use ($patientIds) {
                    $query->whereIn('user_id', $patientIds);
                });
            
            $totalRecords = $baseQuery->count();
        }

        $report = BhwMonthlyReport::create([
            'bhw_id' => auth()->id(),
            'report_type' => $validated['report_type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'report_month' => $validated['report_month'],
            'report_year' => $validated['report_year'],
            'filters' => $filters,
            'total_records' => $totalRecords,
            'status' => 'completed',
        ]);

        return redirect()
            ->route('bhw.reports.show', $report->id)
            ->with('success', 'Monthly report created successfully.');
    }

    public function reportsShow($id)
    {
        $report = BhwMonthlyReport::with('bhw')
            ->where('bhw_id', auth()->id())
            ->findOrFail($id);

        if ($report->report_type === 'health_records') {
            $baseQuery = $this->buildReportHealthRecordsQuery(
                $report->bhw_id,
                $report->report_month,
                $report->report_year,
                $report->filters ?? []
            );

            $healthRecords = (clone $baseQuery)->paginate(20);
            $uniquePatients = (clone $baseQuery)
                ->select('user_id')
                ->distinct()
                ->count();
            $riskDistribution = [
                'low' => (clone $baseQuery)->where('risk_level', 'Low')->count(),
                'medium' => (clone $baseQuery)->where('risk_level', 'Medium')->count(),
                'high' => (clone $baseQuery)->where('risk_level', 'High')->count(),
            ];

            return view('bhw.reports.show', compact('report', 'healthRecords', 'uniquePatients', 'riskDistribution'));
        } else {
            // Pregnancy reports
            $baseQuery = Pregnancy::whereNull('ended_at')
                ->whereMonth('created_at', $report->report_month)
                ->whereYear('created_at', $report->report_year)
                ->with('woman')
                ->when(($report->filters['patient_filter'] ?? 'all') === 'selected', function ($query) use ($report) {
                    $query->whereIn('user_id', $report->filters['user_ids'] ?? []);
                });

            $pregnancies = (clone $baseQuery)->paginate(20);
            $uniquePatients = (clone $baseQuery)
                ->select('user_id')
                ->distinct()
                ->count();
            $riskDistribution = [
                'low' => (clone $baseQuery)->where('is_high_risk', false)->count(),
                'high' => (clone $baseQuery)->where('is_high_risk', true)->count(),
                'medium' => 0,
            ];

            return view('bhw.reports.show-pregnancies', compact('report', 'pregnancies', 'uniquePatients', 'riskDistribution'));
        }
    }

    public function reportsPrint($id)
    {
        $report = BhwMonthlyReport::with('bhw')
            ->where('bhw_id', auth()->id())
            ->findOrFail($id);

        if ($report->report_type === 'health_records') {
            $baseQuery = $this->buildReportHealthRecordsQuery(
                $report->bhw_id,
                $report->report_month,
                $report->report_year,
                $report->filters ?? []
            );

            $healthRecords = (clone $baseQuery)->get();
            $uniquePatients = (clone $baseQuery)
                ->select('user_id')
                ->distinct()
                ->count();
            $riskDistribution = [
                'low' => (clone $baseQuery)->where('risk_level', 'Low')->count(),
                'medium' => (clone $baseQuery)->where('risk_level', 'Medium')->count(),
                'high' => (clone $baseQuery)->where('risk_level', 'High')->count(),
            ];

            if (!$report->printed_at) {
                $report->forceFill(['printed_at' => now()])->save();
                $report->refresh();
            }

            return view('bhw.reports.print', compact('report', 'healthRecords', 'uniquePatients', 'riskDistribution'));
        } else {
            // Pregnancy reports
            $baseQuery = Pregnancy::whereNull('ended_at')
                ->whereMonth('created_at', $report->report_month)
                ->whereYear('created_at', $report->report_year)
                ->with('woman')
                ->when(($report->filters['patient_filter'] ?? 'all') === 'selected', function ($query) use ($report) {
                    $query->whereIn('user_id', $report->filters['user_ids'] ?? []);
                });

            $pregnancies = (clone $baseQuery)->get();
            $uniquePatients = (clone $baseQuery)
                ->select('user_id')
                ->distinct()
                ->count();
            $riskDistribution = [
                'low' => (clone $baseQuery)->where('is_high_risk', false)->count(),
                'high' => (clone $baseQuery)->where('is_high_risk', true)->count(),
                'medium' => 0,
            ];

            if (!$report->printed_at) {
                $report->forceFill(['printed_at' => now()])->save();
                $report->refresh();
            }

            return view('bhw.reports.print-pregnancies', compact('report', 'pregnancies', 'uniquePatients', 'riskDistribution'));
        }
    }

    public function reportsDestroy(\Illuminate\Http\Request $request, $id)
    {
        $report = BhwMonthlyReport::where('bhw_id', auth()->id())->findOrFail($id);
        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'BHW monthly report archived via console';
        }

        try {
            app(\App\Services\ArchiveService::class)->archiveRecord($report, $reason, auth()->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('bhw.reports.index')
            ->with('success', 'Report archived successfully (retained for audit).');
    }

    public function reportsSubmitToPresident($id)
    {
        $report = BhwMonthlyReport::where('bhw_id', auth()->id())->findOrFail($id);
        
        if ($report->submission_status !== 'draft') {
            return redirect()
                ->route('bhw.reports.index')
                ->with('error', 'This report has already been submitted.');
        }

        $report->submitToPresident(auth()->id());

        return redirect()
            ->route('bhw.reports.index')
            ->with('success', 'Report submitted to BHW President successfully.');
    }

    private function buildReportHealthRecordsQuery(int $bhwId, int $month, int $year, array $filters = [])
    {
        return HealthRecord::with(['woman', 'recordedBy'])
            ->where('recorded_by_id', $bhwId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->when(($filters['patient_filter'] ?? 'all') === 'selected', function ($query) use ($filters) {
                $query->whereIn('user_id', $filters['user_ids'] ?? []);
            })
            ->latest();
    }

    // Profile - Redirect to unified profile system
    public function profile()
    {
        return redirect()->route('profile.show');
    }

    // Pregnancies Index
    public function pregnancies()
    {
        $search = request('search');
        $filter = request('filter', 'all');
        $authId = auth()->id();

        $query = Pregnancy::with(['woman', 'woman.createdByBhw', 'woman.createdByMidwife', 'walkInPatient', 'walkInPatient.recordedBy', 'healthRecords', 'healthRecords.recordedBy'])
            ->whereNull('ended_at');

        // Filter by creator
        if ($filter === 'mine') {
            // Pregnancies created by current BHW (through woman creation with pregnancy info)
            $query->where(function ($inner) use ($authId) {
                $inner->whereHas('woman', function ($q) use ($authId) {
                    $q->where('created_by_bhw_id', $authId);
                })->orWhereHas('walkInPatient', function ($q) use ($authId) {
                    $q->where('recorded_by_id', $authId);
                });
            });
        } elseif ($filter === 'midwife') {
            // Pregnancies created by midwife
            $query->whereHas('woman', function ($q) {
                $q->where('created_by_midwife_id', '!=', null);
            });
        } elseif ($filter === 'others') {
            // Pregnancies created by other BHWs
            $query->where(function ($inner) use ($authId) {
                $inner->whereHas('woman', function ($q) use ($authId) {
                    $q->where('created_by_bhw_id', '!=', $authId)
                      ->whereNotNull('created_by_bhw_id');
                })->orWhereHas('walkInPatient', function ($q) use ($authId) {
                    $q->whereNotNull('recorded_by_id')
                        ->where('recorded_by_id', '!=', $authId);
                });
            });
        }

        // Search by patient name
        if ($search) {
            $query->where(function ($outer) use ($search) {
                $outer->whereHas('woman', function ($q) use ($search) {
                    $q->where(function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_initial', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                })->orWhereHas('walkInPatient', function ($q) use ($search) {
                    $q->where(function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_initial', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                });
            });
        }

        $pregnancies = $query->latest()->paginate(20);

        // Mark which pregnancies are editable by current BHW
        foreach ($pregnancies as $pregnancy) {
            $pregnancy->is_mine = ($pregnancy->woman && $pregnancy->woman->created_by_bhw_id == $authId)
                || ($pregnancy->walkInPatient && $pregnancy->walkInPatient->recorded_by_id == $authId);
            $pregnancy->is_midwife_created = $pregnancy->woman && $pregnancy->woman->created_by_midwife_id != null;
            $pregnancy->created_by_name = $pregnancy->is_midwife_created
                ? ($pregnancy->woman?->createdByMidwife?->name ?? 'Midwife')
                : ($pregnancy->is_mine
                    ? 'You'
                    : ($pregnancy->woman?->createdByBhw?->name
                        ?? $pregnancy->walkInPatient?->recordedBy?->name
                        ?? $pregnancy->healthRecords->first()?->recordedBy?->name
                        ?? 'Other BHW'));
        }

        return view('bhw.pregnancies.index', compact('pregnancies', 'filter', 'search'));
    }

    // Report Pregnancy — BHW field report straight to the midwife queue.
    // New person? Register them first (Register Woman creates the pregnancy).
    // Already tracked here? Open the existing record instead of duplicating.
    public function createPregnancy()
    {
        $women = User::where('role', 'user')->where('status', 'approved')
            ->with(['pregnancies' => fn ($query) => $query->active()])
            ->orderBy('last_name')->orderBy('first_name')
            ->get();
        $walkInPatients = WalkInPatient::whereNull('converted_to_user_id')
            ->with(['pregnancies' => fn ($query) => $query->active()])
            ->orderBy('last_name')->orderBy('first_name')
            ->get();
        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);

        return view('bhw.pregnancies.create', compact('women', 'walkInPatients', 'barangays'));
    }

    public function storePregnancy(Request $request)
    {
        $request->validate([
            'patient_type' => 'required|in:registered,walk_in,new_walk_in',
            'user_id' => [
                'nullable',
                'required_if:patient_type,registered',
                \Illuminate\Validation\Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'user')->where('status', 'approved')),
            ],
            'walk_in_patient_id' => [
                'nullable',
                'required_if:patient_type,walk_in',
                'exists:walk_in_patients,id',
            ],
            // New unlinked woman captured inline with the report.
            'first_name' => 'nullable|required_if:patient_type,new_walk_in|string|max:255',
            'middle_initial' => 'nullable|string|max:10',
            'last_name' => 'nullable|required_if:patient_type,new_walk_in|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'contact_number' => 'nullable|required_if:patient_type,new_walk_in|string|max:20',
            'barangay' => 'nullable|required_if:patient_type,new_walk_in|string|max:255',
            'purok' => 'nullable|string|max:100',
            'lmp' => 'required|date|before:today',
            'edd' => 'nullable|date|after:lmp',
            'gravida' => 'nullable|integer|min:1',
            'para' => 'nullable|integer|min:0',
            'symptoms' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:1000',
        ]);

        // New unlinked woman: capture the field profile inline, then report.
        if ($request->patient_type === 'new_walk_in') {
            $walkInPatient = WalkInPatient::create([
                'recorded_by_id' => auth()->id(),
                'user_id' => null,
                'has_portal_access' => false,
                'first_name' => $request->first_name,
                'middle_initial' => $request->middle_initial,
                'last_name' => $request->last_name,
                'date_of_birth' => $request->date_of_birth,
                'contact_number' => $request->contact_number,
                'address' => implode(', ', array_filter([
                    $request->filled('purok') ? $request->purok : null,
                    $request->barangay,
                    'San Carlos City, Pangasinan',
                ])),
                'barangay' => $request->barangay,
                'purok_id' => Purok::resolveIdFromText($request->purok, $request->barangay),
            ]);
            $request->merge([
                'patient_type' => 'walk_in',
                'walk_in_patient_id' => $walkInPatient->id,
            ]);
        }

        // Proper duplicate solution: one active pregnancy per patient. Never
        // create a second record — point the BHW at the existing one instead.
        if ($request->patient_type === 'registered') {
            $existing = Pregnancy::active()->where('user_id', $request->user_id)->first();
            $patientName = User::where('role', 'user')->find($request->user_id)?->name ?? 'This patient';
        } else {
            $existing = Pregnancy::active()->where('walk_in_patient_id', $request->walk_in_patient_id)->first();
            $patientName = WalkInPatient::find($request->walk_in_patient_id)?->full_name ?? 'This patient';
        }

        if ($existing) {
            return back()
                ->withErrors(['patient' => $patientName . ' is already tracked (LMP ' . optional($existing->lmp)->format('M d, Y') . ', EDD ' . optional($existing->edd)->format('M d, Y') . '). Open Pregnancies and update that record instead of reporting a duplicate.'])
                ->withInput();
        }

        $edd = $request->edd
            ? \Carbon\Carbon::parse($request->edd)
            : \Carbon\Carbon::parse($request->lmp)->addDays(280);

        $pregnancy = Pregnancy::create([
            'user_id' => $request->patient_type === 'registered' ? $request->user_id : null,
            'walk_in_patient_id' => $request->patient_type === 'walk_in' ? $request->walk_in_patient_id : null,
            'lmp' => $request->lmp,
            'edd' => $edd,
            'gravida' => $request->gravida,
            'para' => $request->para,
            'risk_assessment_mode' => 'automatic',
            'risk_level' => 'Low',
            'risk_notes' => 'BHW field report — awaiting midwife validation.',
            'notes' => trim(collect([$request->symptoms, $request->notes])->filter()->implode("\n")),
            // Straight into the review queue AND the midwife is alerted below.
            'workflow_status' => 'submitted_to_bhw_president',
            'submitted_to_bhw_president_at' => now(),
        ]);

        if ($request->filled('gravida') || $request->filled('para')) {
            \App\Models\MaternalCareTargetClient::updateOrCreate(
                ['pregnancy_id' => $pregnancy->id],
                [
                    'user_id' => $pregnancy->user_id,
                    'gravida' => $request->gravida ?? 1,
                    'parity' => $request->para ?? 0,
                ]
            );
        }

        $reporter = auth()->user();
        $reporterContact = $reporter->contact_number
            ? " (contact {$reporter->contact_number})"
            : ' (no contact number on file)';
        $detail = "BHW field pregnancy report for {$patientName}: LMP " . $pregnancy->lmp->format('M d, Y')
            . ', EDD ' . $pregnancy->edd->format('M d, Y')
            . ". Reported by {$reporter->name}{$reporterContact}."
            . ($request->filled('symptoms') ? " Reported symptoms: {$request->symptoms}" : '');

        // Midwife inbox: full report + how to reach the reporting BHW.
        $midwives = User::where('role', 'midwife')->where('status', 'approved')->get();
        foreach ($midwives as $midwife) {
            \App\Models\Notification::createNotification(
                $midwife->id,
                $detail,
                '🤰 New Pregnancy Reported by BHW',
                'info',
                route('midwife.pregnancies.index'),
                'midwife'
            );
            try {
                if ($midwife->hasSmsEnabled()) {
                    (new \App\Services\SmsService())->sendCustom(
                        $midwife,
                        "🤰 REPROCARE: {$reporter->name}{$reporterContact} reported {$patientName} pregnant (EDD {$pregnancy->edd->format('M d, Y')}). Please review in ReproCare.",
                        "🤰 REPROCARE: Nag-report si {$reporter->name}{$reporterContact} na buntis si {$patientName} (EDD {$pregnancy->edd->format('M d, Y')}). Pakirebyu sa ReproCare."
                    );
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('BHW pregnancy report SMS failed: ' . $e->getMessage());
            }
        }

        // BHW President(s) of the reporter's barangay: review queue heads-up.
        $presidents = User::where('role', 'bhw_president')->where('status', 'approved')
            ->when($reporter->barangay, fn ($query) => $query->where('barangay', $reporter->barangay))
            ->get();
        foreach ($presidents as $president) {
            \App\Models\Notification::createNotification(
                $president->id,
                $detail,
                '🤰 BHW Pregnancy Report — Review',
                'info',
                route('bhw-president.pregnancies.index'),
                'bhw_president'
            );
        }

        try {
            ActivityLog::log('create', "BHW {$reporter->name} reported pregnancy for {$patientName} (EDD {$pregnancy->edd->format('M d, Y')})", $pregnancy);
        } catch (\Throwable $e) {
        }

        return redirect()->route('bhw.pregnancies.index')
            ->with('success', "Pregnancy reported for {$patientName} (EDD {$pregnancy->edd->format('M d, Y')}). The midwife has been notified with your contact details.");
    }

    public function notifications()
    {
        $notifications = auth()->user()->notifications()
            ->with('patientAlert')
            ->orderByRaw('is_read ASC, COALESCE(last_reminded_at, created_at) DESC')
            ->paginate(20);

        return view('bhw.notifications', compact('notifications'));
    }

    // Settings — Profile, field assignment, preferences, performance, security, activity
    public function settings()
    {
        $user = auth()->user()->loadMissing(['purok', 'activeBhwAssignment.purok', 'activeBhwAssignment.assignedBy']);

        // ── 2. Community field assignment (managed by RHU Admin / BHW President) ──
        $assignments = $user->bhwAssignments()->with('purok')->active()->get();
        $assignedPuroks = $assignments->map->purok->filter()->unique('id')->values();
        if ($user->purok && !$assignedPuroks->contains('id', $user->purok->id)) {
            $assignedPuroks->push($user->purok);
        }
        $assignedBy = optional($user->activeBhwAssignment)->assignedBy;
        $president = ($assignedBy && $assignedBy->role === 'bhw_president')
            ? $assignedBy
            : User::where('role', 'bhw_president')->where('status', 'approved')->latest()->first();

        // ── 4. Monthly performance summary ──
        $stats = [
            'registered' => User::where('created_by_bhw_id', $user->id)->count(),
            'recordsAdded' => HealthRecord::where('recorded_by_id', $user->id)->count(),
            'checkupsFacilitated' => Checkup::where('scheduled_by_id', $user->id)->count(),
            'highRiskFlags' => HealthRecord::where('recorded_by_id', $user->id)->where('risk_level', 'High')->count(),
        ];
        $now = now();
        $currentReport = BhwMonthlyReport::where('bhw_id', $user->id)
            ->where('report_month', $now->month)
            ->where('report_year', $now->year)
            ->latest()
            ->first();

        // ── 6. Recent activity ──
        $recentActivity = ActivityLog::where('user_id', $user->id)->latest()->limit(8)->get();

        return view('bhw.settings', compact(
            'assignments',
            'assignedPuroks',
            'president',
            'stats',
            'currentReport',
            'recentActivity'
        ));
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();
        $section = $request->input('section', 'preferences');

        // ── 5a. Change password ──
        if ($section === 'password') {
            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if (!Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }

            $user->password = $request->input('password');
            $user->save();
            ActivityLog::log('update', 'Changed account password');

            return back()->with('success', 'Password updated successfully.');
        }

        // ── 5b. Two-factor authentication enrollment flag ──
        if ($section === '2fa') {
            $user->pref_2fa_enabled = $request->boolean('pref_2fa_enabled');
            $user->save();
            ActivityLog::log('update', $user->pref_2fa_enabled ? 'Enabled two-factor authentication' : 'Disabled two-factor authentication');

            return back()->with('success', $user->pref_2fa_enabled ? 'Two-factor authentication enabled.' : 'Two-factor authentication disabled.');
        }

        // ── 1. Personal profile (identity & contact) ──
        if ($section === 'profile') {
            $request->validate([
                'contact_number' => 'nullable|string|max:20',
                'secondary_contact' => 'nullable|string|max:20',
                'secondary_email' => 'nullable|email|max:255',
                'address' => 'nullable|string|max:500',
            ]);

            $user->contact_number = $request->input('contact_number', $user->contact_number);
            $user->secondary_contact = $request->input('secondary_contact', $user->secondary_contact);
            $user->secondary_email = $request->input('secondary_email', $user->secondary_email);
            $user->address = $request->input('address', $user->address);
            $user->save();
            ActivityLog::log('update', 'Updated profile and contact information');

            return back()->with('success', 'Profile information updated.');
        }

        // ── 3. Operational task & notification preferences ──
        $request->validate([
            'pref_report_summary' => 'required|in:monthly,weekly,off',
        ]);

        $user->pref_registration_email = $request->boolean('pref_registration_email');
        $user->pref_registration_sms = $request->boolean('pref_registration_sms');
        $user->pref_registration_dashboard = $request->boolean('pref_registration_dashboard');
        $user->pref_high_risk_email = $request->boolean('pref_high_risk_email');
        $user->pref_high_risk_sms = $request->boolean('pref_high_risk_sms');
        $user->pref_high_risk_dashboard = $request->boolean('pref_high_risk_dashboard');
        $user->pref_checkup_reminders = $request->boolean('pref_checkup_reminders');
        $user->pref_report_summary = $request->input('pref_report_summary', 'monthly');
        $user->save();
        ActivityLog::log('update', 'Updated task and notification preferences');

        return back()->with('success', 'Preferences saved successfully.');
    }

    // Checkup Referrals
    public function referrals()
    {
        $referrals = CheckupReferral::with(['woman', 'walkInPatient', 'assignedMidwife', 'convertedCheckup'])
            ->where('referred_by_bhw_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('bhw.referrals.index', compact('referrals'));
    }

    public function createReferral()
    {
        $women = User::where('role', 'user')->where('status', 'approved')->with('purok')->get(['id', 'first_name', 'middle_initial', 'last_name', 'email', 'purok_id']);
        $walkInPatients = WalkInPatient::notConverted()->latest()->get();
        $barangays = \App\Models\Barangay::active()->orderBy('name')->get(['id', 'name']);
        $midwives = User::where('role', 'midwife')->where('status', 'approved')->orderBy('first_name')->get(['id', 'first_name', 'middle_initial', 'last_name']);

        return view('bhw.referrals.create', compact('women', 'walkInPatients', 'barangays', 'midwives'));
    }

    /**
     * Pregnancy-report form: report one identified pregnancy to a chosen
     * midwife, with the exact health records attached.
     */
    public function reportPregnancy($pregnancyId)
    {
        $pregnancy = \App\Models\Pregnancy::with(['woman', 'walkInPatient', 'healthRecords.recordedBy', 'referrals.assignedMidwife'])
            ->findOrFail($pregnancyId);

        $records = $pregnancy->healthRecords()->with('recordedBy')->orderByDesc('created_at')->limit(25)->get();
        $midwives = User::where('role', 'midwife')->where('status', 'approved')->orderBy('first_name')->get(['id', 'first_name', 'middle_initial', 'last_name']);
        $activeReferral = $pregnancy->referrals()->active()->with('assignedMidwife')->first();

        return view('bhw.referrals.report-pregnancy', compact('pregnancy', 'records', 'midwives', 'activeReferral'));
    }

    /**
     * Store a pregnancy report as a referral linked to the pregnancy and
     * the selected health-record snapshot, routed to the chosen midwife.
     */
    public function storePregnancyReport(Request $request)
    {
        $validated = $request->validate([
            'pregnancy_id' => 'required|exists:pregnancies,id',
            'assigned_midwife_id' => 'required|exists:users,id',
            'health_record_ids' => 'nullable|array|max:25',
            'health_record_ids.*' => 'integer|exists:health_records,id',
            'urgency' => 'required|in:routine,urgent,emergency',
            'reason' => 'required|string|max:255',
            'bhw_notes' => 'nullable|string|max:1000',
        ]);

        $pregnancy = \App\Models\Pregnancy::findOrFail($validated['pregnancy_id']);

        $midwife = User::where('role', 'midwife')->where('status', 'approved')->find($validated['assigned_midwife_id']);
        if (!$midwife) {
            return back()->withErrors(['assigned_midwife_id' => 'Select an approved midwife.'])->withInput();
        }

        // One active handoff per pregnancy — the midwife acts on the existing one.
        $existing = \App\Models\CheckupReferral::where('pregnancy_id', $pregnancy->id)->active()->first();
        if ($existing) {
            return redirect()->route('bhw.referrals.show', $existing->id)
                ->with('error', 'This pregnancy already has an active report with ' . ($existing->assignedMidwife?->name ?? 'the midwife') . '.');
        }

        // Attach only records that belong to this pregnancy (no cross-patient leaks).
        $recordIds = \App\Models\HealthRecord::where('pregnancy_id', $pregnancy->id)
            ->whereIn('id', $validated['health_record_ids'] ?? [])
            ->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        $referral = \App\Models\CheckupReferral::create([
            'referred_by_bhw_id' => auth()->id(),
            'user_id' => $pregnancy->user_id,
            'walk_in_patient_id' => $pregnancy->walk_in_patient_id,
            'pregnancy_id' => $pregnancy->id,
            'health_record_ids' => $recordIds,
            'assigned_midwife_id' => $midwife->id,
            'reason' => $validated['reason'],
            'urgency' => $validated['urgency'],
            'bhw_notes' => $validated['bhw_notes'] ?? null,
            'status' => 'pending',
        ]);

        \App\Models\Notification::createNotification(
            $midwife->id,
            "Pregnancy reported by BHW: {$pregnancy->patient_name} (LMP " . ($pregnancy->lmp?->format('M d, Y') ?? 'n/a') . ', ' . count($recordIds) . ' record(s) attached) - ' . $validated['reason'],
            '🤰 New Pregnancy Report',
            $validated['urgency'] === 'routine' ? 'info' : 'warning',
            route('midwife.referrals.show', $referral->id)
        );

        return redirect()->route('bhw.referrals.show', $referral->id)
            ->with('success', "Pregnancy reported to Midwife {$midwife->name} with " . count($recordIds) . ' attached record(s).');
    }

    public function storeReferral(Request $request)
    {
        $request->validate([
            'patient_type' => 'required|in:registered,walk_in,new_walk_in',
            'user_id' => 'exclude_unless:patient_type,registered|required|exists:users,id',
            'walk_in_patient_id' => 'exclude_unless:patient_type,walk_in|required|exists:walk_in_patients,id',
            'reason' => 'required|string|max:255',
            'urgency' => 'required|in:routine,urgent,emergency',
            'bhw_notes' => 'nullable|string|max:1000',
            // For new walk-in patient
            'first_name' => 'exclude_unless:patient_type,new_walk_in|required|string|max:255',
            'last_name' => 'exclude_unless:patient_type,new_walk_in|required|string|max:255',
            'middle_initial' => 'exclude_unless:patient_type,new_walk_in|nullable|string|max:10',
            'date_of_birth' => 'exclude_unless:patient_type,new_walk_in|nullable|date',
            'barangay' => 'exclude_unless:patient_type,new_walk_in|required|string|max:255',
            'purok' => 'exclude_unless:patient_type,new_walk_in|required|string|max:100',
            'contact_number' => 'exclude_unless:patient_type,new_walk_in|nullable|string|max:20|regex:/^(\+?63|0)?9\d{9}$/',
            'reason_for_visit' => 'exclude_unless:patient_type,new_walk_in|nullable|string|max:500',
            'assigned_midwife_id' => 'nullable|exists:users,id',
        ], [
            'contact_number.regex' => 'Enter a valid PH mobile number (e.g. 09171234567) so the midwife can send SMS alerts.',
        ]);

        $walkInPatientId = null;
        $womanId = null;

        if ($request->patient_type === 'new_walk_in') {
            $walkInPatient = WalkInPatient::create([
                'recorded_by_id' => auth()->id(),
                'first_name' => $request->first_name,
                'middle_initial' => $request->middle_initial,
                'last_name' => $request->last_name,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->filled('purok') ? implode(', ', array_filter([$request->purok, $request->barangay, 'San Carlos City, Pangasinan'])) : null,
                'barangay' => $request->barangay,
                'purok_id' => \App\Models\Purok::resolveIdFromText($request->purok, $request->barangay),
                'contact_number' => $request->contact_number,
                'reason_for_visit' => $request->reason_for_visit,
                'notes' => $request->bhw_notes,
            ]);
            $walkInPatientId = $walkInPatient->id;
        } elseif ($request->patient_type === 'walk_in') {
            $walkInPatientId = $request->walk_in_patient_id;
        } else {
            $womanId = $request->user_id;
        }

        // Route to the chosen midwife (falls back to the first approved one).
        $midwife = $request->filled('assigned_midwife_id')
            ? \App\Models\User::where('role', 'midwife')->where('status', 'approved')->find($request->assigned_midwife_id)
            : \App\Models\User::where('role', 'midwife')->where('status', 'approved')->first();

        if ($request->filled('assigned_midwife_id') && !$midwife) {
            return back()->withErrors(['assigned_midwife_id' => 'Select an approved midwife.'])->withInput();
        }

        $referral = CheckupReferral::create([
            'referred_by_bhw_id' => auth()->id(),
            'user_id' => $womanId,
            'walk_in_patient_id' => $walkInPatientId,
            'assigned_midwife_id' => $midwife ? $midwife->id : null,
            'reason' => $request->reason,
            'urgency' => $request->urgency,
            'bhw_notes' => $request->bhw_notes,
            'status' => 'pending',
        ]);

        // Notify the midwife if available
        if ($midwife) {
            \App\Models\Notification::createNotification(
                $midwife->id,
                "New checkup referral from BHW: {$referral->patient_name} - {$request->reason}",
                '📋 New Referral',
                'info',
                route('midwife.referrals.show', $referral->id)
            );
        }

        return redirect()->route('bhw.referrals.index')
            ->with('success', 'Checkup referral created successfully and sent to midwife.');
    }

    public function showReferral($id)
    {
        $referral = CheckupReferral::with(['woman', 'walkInPatient', 'assignedMidwife', 'referredByBhw', 'convertedCheckup'])
            ->where('referred_by_bhw_id', auth()->id())
            ->findOrFail($id);

        return view('bhw.referrals.show', compact('referral'));
    }

    // Walk-in Patients Management
    public function walkInPatients()
    {
        // Folded into Women: the All Women list already shows every unlinked
        // profile with an Unlinked filter, so the separate list is retired.
        // Kept as a redirect (not removed) for old bookmarks and back buttons.
        return redirect()->route('bhw.patients', ['filter' => 'unregistered']);
    }

    public function createWalkInPatient()
    {
        $puroks = \App\Models\Purok::all(['id', 'name']);
        return view('bhw.walk-in-patients.create', compact('puroks'));
    }

    public function storeWalkInPatient(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:10',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'barangay' => 'nullable|string|max:255',
            'purok' => 'nullable|string|max:100',
            'contact_number' => \App\Models\WalkInPatient::phoneRule(),
            'reason_for_visit' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ], [
            'contact_number.regex' => 'Enter a valid PH mobile number (e.g. 09171234567) so the midwife can send SMS alerts.',
        ]);

        WalkInPatient::create([
            'recorded_by_id' => auth()->id(),
            'user_id' => null,
            'has_portal_access' => false,
            'first_name' => $request->first_name,
            'middle_initial' => $request->middle_initial,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'barangay' => $request->barangay,
            'purok_id' => \App\Models\Purok::resolveIdFromText($request->purok, $request->barangay),
            'contact_number' => $request->contact_number,
            'reason_for_visit' => $request->reason_for_visit,
            'notes' => $request->notes,
        ]);

        return redirect()->route('bhw.patients', ['filter' => 'unregistered'])
            ->with('success', 'Field record saved as Unlinked Profile (BHW-Managed - no portal account).');
    }

    public function showWalkInPatient($id)
    {
        $patient = WalkInPatient::with(['recordedBy', 'purok', 'checkupReferrals'])
            ->findOrFail($id);
        $smsLogs = $patient->contact_number
            ? \App\Models\SmsLog::where('phone_number', $patient->contact_number)
                ->orWhere('phone_number', $patient->smsPhone())
                ->latest()->limit(10)->get()
            : collect();

        return view('bhw.walk-in-patients.show', compact('patient', 'smsLogs'));
    }

    public function editWalkInPatient($id)
    {
        $patient = WalkInPatient::with(['recordedBy', 'purok'])
            ->where('recorded_by_id', auth()->id())
            ->findOrFail($id);

        if ($patient->converted_to_user_id) {
            return back()->with('error', 'This walk-in patient has already been converted to a registered user and cannot be edited.');
        }

        $puroks = \App\Models\Purok::all();
        return view('bhw.walk-in-patients.edit', compact('patient', 'puroks'));
    }

    public function updateWalkInPatient(Request $request, $id)
    {
        $patient = WalkInPatient::where('recorded_by_id', auth()->id())->findOrFail($id);

        if ($patient->converted_to_user_id) {
            return back()->with('error', 'This walk-in patient has already been converted to a registered user and cannot be edited.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:10',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'contact_number' => \App\Models\WalkInPatient::phoneRule(),
            'address' => 'nullable|string|max:500',
            'barangay' => 'nullable|string|max:255',
            'purok' => 'nullable|string|max:100',
            'reason_for_visit' => 'nullable|string|max:500',
        ], [
            'contact_number.regex' => 'Enter a valid PH mobile number (e.g. 09171234567) so the midwife can send SMS alerts.',
        ]);

        $patient->update([
            'first_name' => $request->first_name,
            'middle_initial' => $request->middle_initial,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'contact_number' => $request->contact_number,
            'address' => $request->address,
            'barangay' => $request->barangay,
            'purok_id' => \App\Models\Purok::resolveIdFromText($request->purok, $request->barangay),
            'reason_for_visit' => $request->reason_for_visit,
        ]);

        $backTo = match ($request->input('from')) {
            'pregnancies' => redirect()->route('bhw.pregnancies.index'),
            'women' => redirect()->route('bhw.patients'),
            default => redirect()->route('bhw.walk-in-patients.show', $id),
        };

        return $backTo->with('success', 'Walk-in patient updated successfully.');
    }

    public function deleteWalkInPatient($id)
    {
        $patient = WalkInPatient::where('recorded_by_id', auth()->id())->findOrFail($id);

        if ($patient->converted_to_user_id) {
            return back()->with('error', 'This walk-in patient has already been converted to a registered user and cannot be archived.');
        }

        $patient->delete();

        return redirect()->route('bhw.patients', ['filter' => 'unregistered'])
            ->with('success', 'Walk-in patient moved to trash. You can restore it from the Trash button on this page.');
    }

    public function trashWalkInPatients()
    {
        $patients = WalkInPatient::onlyTrashed()
            ->where('recorded_by_id', auth()->id())
            ->latest()->paginate(10);
        return view('bhw.walk-in-patients.trash', compact('patients'));
    }

    public function restoreWalkInPatient($id)
    {
        $patient = WalkInPatient::onlyTrashed()
            ->where('recorded_by_id', auth()->id())->findOrFail($id);
        $patient->restore();
        return redirect()->route('bhw.walk-in-patients.show', $patient->id)
            ->with('success', 'Walk-in patient restored successfully.');
    }

    public function convertWalkInToUser($id)
    {
        $walkInPatient = WalkInPatient::findOrFail($id);

        if ($walkInPatient->converted_to_user_id) {
            return back()->with('error', 'This walk-in patient has already been converted to a registered user.');
        }

        return view('bhw.walk-in-patients.convert', compact('walkInPatient'));
    }

    public function storeConvertedUser(Request $request, $id)
    {
        $walkInPatient = WalkInPatient::findOrFail($id);

        if ($walkInPatient->converted_to_user_id) {
            return back()->with('error', 'This walk-in patient has already been converted to a registered user.');
        }

        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'required|string|max:500',
            'barangay' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
        ]);

        // Account Activation: generate portal credentials for a BHW-Managed woman,
        // creating the users row and linking it back (user_id + portal access).
        // Activated by staff, so the account is approved immediately.
        $user = User::create([
            'first_name' => $walkInPatient->first_name,
            'middle_initial' => $walkInPatient->middle_initial,
            'last_name' => $walkInPatient->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'status' => 'approved',
            'address' => $request->address,
            'barangay' => $request->barangay,
            'purok_id' => $walkInPatient->purok_id,
            'date_of_birth' => $walkInPatient->date_of_birth,
            'contact_number' => $request->contact_number,
            'created_by_bhw_id' => auth()->id(),
        ]);

        $walkInPatient->update([
            'user_id' => $user->id,
            'has_portal_access' => true,
            'converted_to_user_id' => $user->id,
            'converted_at' => now(),
        ]);

        return redirect()->route('bhw.walk-in-patients.show', $id)
            ->with('success', 'Portal account activated — Unlinked Profile is now an Enrolled Account (Portal-Active).');
    }

    // My Tasks (assigned by the BHW President — read + advance only)
    public function myTasks()
    {
        $tasks = \App\Models\Task::with('assignedBy')
            ->where('assigned_to_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('bhw.tasks.index', compact('tasks'));
    }

    public function startTask($id)
    {
        $task = \App\Models\Task::where('assigned_to_id', auth()->id())->findOrFail($id);
        $task->update(['status' => 'in_progress']);

        return back()->with('success', 'Task marked as in progress.');
    }

    public function completeTask($id)
    {
        $task = \App\Models\Task::where('assigned_to_id', auth()->id())->findOrFail($id);
        $task->update(['status' => 'completed', 'completed_at' => now()]);

        return back()->with('success', 'Task marked as completed.');
    }
}
