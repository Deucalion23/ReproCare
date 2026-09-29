<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\HealthRecord;
use App\Models\LearningMaterial;
use App\Models\MaternalDeath;
use App\Models\MaternalMorbidity;
use App\Models\Pregnancy;
use App\Models\SupplyRequest;
use App\Models\User;
use Illuminate\Http\Request;

class ArchivedRecordController extends Controller
{
    /**
     * Staff roles an RHU administrator may review and restore. Administrator
     * accounts (cho / fellow rhu) belong to the City Health Office alone —
     * letting RHU restore those would be a privilege escalation.
     */
    public const RHU_MANAGED_STAFF_ROLES = ['midwife', 'bhw_president', 'bhw'];

    public function __construct()
    {
        $this->middleware(['auth', 'account.active', 'role:cho,rhu']);
    }

    /**
     * Display archived records. CHO sees the city-wide hub; RHU sees the same
     * hub (this deployment serves RHU 1 alone) except administrator accounts,
     * which stay CHO-only.
     */
    public function index(Request $request)
    {
        $isRhu = ($request->user()->role ?? null) === 'rhu';
        $staffRoles = $isRhu
            ? self::RHU_MANAGED_STAFF_ROLES
            : ['rhu', 'midwife', 'bhw_president', 'bhw'];

        $activeTab = $request->input('tab', 'patients');
        $search = $request->input('search');

        // Archived Patients
        $patients = User::onlyTrashed()
            ->where('role', 'user')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('barangay', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(15, ['*'], 'patients_page')
            ->withQueryString();

        // Archived Staff Accounts (RHU: only the roles it manages — never
        // fellow administrators, which stay CHO-only).
        $staff = User::onlyTrashed()
            ->whereIn('role', $staffRoles)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(15, ['*'], 'staff_page')
            ->withQueryString();

        // Archived Health Records
        $healthRecords = HealthRecord::onlyTrashed()
            ->with(['user', 'recordedBy'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(15, ['*'], 'records_page')
            ->withQueryString();

        // Archived Learning Materials / Videos
        $learningMaterials = LearningMaterial::onlyTrashed()
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            })
            ->latest('deleted_at')
            ->paginate(15, ['*'], 'materials_page')
            ->withQueryString();

        // Archived Pregnancies
        $pregnancies = Pregnancy::onlyTrashed()
            ->with('user')
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(15, ['*'], 'pregnancies_page')
            ->withQueryString();

        // Archived Supply Requests
        $supplyRequests = SupplyRequest::onlyTrashed()
            ->with('requestedBy')
            ->when($search, function ($q) use ($search) {
                $q->where('supply_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            })
            ->latest('deleted_at')
            ->paginate(15, ['*'], 'supplies_page')
            ->withQueryString();

        $stats = [
            'patients' => User::onlyTrashed()->where('role', 'user')->count(),
            'staff' => User::onlyTrashed()->whereIn('role', $staffRoles)->count(),
            'health_records' => HealthRecord::onlyTrashed()->count(),
            'learning_materials' => LearningMaterial::onlyTrashed()->count(),
            'pregnancies' => Pregnancy::onlyTrashed()->count(),
            'supply_requests' => SupplyRequest::onlyTrashed()->count(),
        ];

        // One shared hub view; layout/section/restore-route switch by role so
        // RHU renders inside its own portal while CHO output stays identical.
        $archiveLayout = $isRhu ? 'rhu.layout' : 'cho.layout';
        $archiveSection = $isRhu ? 'rhu-content' : 'cho-content';
        $archiveRestoreRoute = $isRhu ? 'rhu.archived.restore' : 'cho.archived.restore';
        $archiveTitle = $isRhu
            ? 'Archived Records - RHU Portal | ReproCare'
            : 'Archived Records Hub - CHO Admin | ReproCare';

        return view('cho.archived.index', compact(
            'activeTab',
            'search',
            'patients',
            'staff',
            'healthRecords',
            'learningMaterials',
            'pregnancies',
            'supplyRequests',
            'stats',
            'archiveLayout',
            'archiveSection',
            'archiveRestoreRoute',
            'archiveTitle'
        ));
    }

    /**
     * Restore an archived record with a single action.
     * All restores flow through ArchiveService so status flags,
     * archive metadata, and audit logs stay consistent.
     */
    public function restore(string $type, int $id)
    {
        $service = app(\App\Services\ArchiveService::class);

        // RHU administrators may never restore administrator accounts
        // (cho / fellow rhu) — those belong to the City Health Office.
        if (in_array($type, ['patient', 'staff', 'user'], true)
            && (auth()->user()->role ?? null) === 'rhu') {
            $target = User::withTrashed()->find($id);
            if ($target && in_array($target->role ?? null, ['cho', 'rhu'], true)) {
                return back()->with('error', 'Only the City Health Office can restore administrator accounts.');
            }
        }

        try {
            $name = match ($type) {
                'patient', 'staff', 'user' => $service->restoreUser($id, auth()->user())->name,
                'health-record' => $service->restoreRecord(
                    HealthRecord::onlyTrashed()->findOrFail($id), auth()->user()
                )->patient_name ?? "Health Record #{$id}",
                'learning-material', 'video' => $service->restoreRecord(
                    LearningMaterial::onlyTrashed()->findOrFail($id), auth()->user()
                )->title,
                'pregnancy' => "Pregnancy Record #{$service->restoreRecord(
                    Pregnancy::onlyTrashed()->findOrFail($id), auth()->user()
                )->id}",
                'supply-request' => 'Supply Request: ' . $service->restoreRecord(
                    SupplyRequest::onlyTrashed()->findOrFail($id), auth()->user()
                )->supply_name,
                'referral' => 'Referral #' . $service->restoreRecord(
                    \App\Models\CheckupReferral::onlyTrashed()->findOrFail($id), auth()->user()
                )->id,
                'maternal-death' => 'Maternal death case #' . $service->restoreRecord(
                    MaternalDeath::onlyTrashed()->findOrFail($id), auth()->user()
                )->id,
                'maternal-morbidity' => 'Morbidity record #' . $service->restoreRecord(
                    MaternalMorbidity::onlyTrashed()->findOrFail($id), auth()->user()
                )->id,
                default => null,
            };
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($name === null) {
            return back()->with('error', 'Invalid record type specified for restoration.');
        }

        return back()->with('success', "Archived {$type} '{$name}' restored successfully.");
    }
}
