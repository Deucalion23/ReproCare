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
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        if ($dateFrom !== null && $dateFrom !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $dateFrom)) {
            $dateFrom = null;
        }
        if ($dateTo !== null && $dateTo !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $dateTo)) {
            $dateTo = null;
        }
        /** Apply the shared archived-date range filter to any tab query. */
        $applyDateRange = function ($query) use ($dateFrom, $dateTo) {
            if ($dateFrom) {
                $query->whereDate('deleted_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('deleted_at', '<=', $dateTo);
            }
        };

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
            ->tap($applyDateRange)
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
            ->tap($applyDateRange)
            ->latest('deleted_at')
            ->paginate(15, ['*'], 'staff_page')
            ->withQueryString();

        // Archived Health Records
        $healthRecords = HealthRecord::onlyTrashed()
            ->with(['user:id,first_name,middle_initial,last_name', 'recordedBy:id,first_name,last_name'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->tap($applyDateRange)
            ->latest('deleted_at')
            ->paginate(15, ['*'], 'records_page')
            ->withQueryString();

        // Archived Learning Materials / Videos
        $learningMaterials = LearningMaterial::onlyTrashed()
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            })
            ->tap($applyDateRange)
            ->latest('deleted_at')
            ->paginate(15, ['*'], 'materials_page')
            ->withQueryString();

        // Archived Pregnancies
        $pregnancies = Pregnancy::onlyTrashed()
            ->with('user:id,first_name,middle_initial,last_name')
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->tap($applyDateRange)
            ->latest('deleted_at')
            ->paginate(15, ['*'], 'pregnancies_page')
            ->withQueryString();

        // Archived Supply Requests
        $supplyRequests = SupplyRequest::onlyTrashed()
            ->with('requestedBy:id,first_name,last_name')
            ->when($search, function ($q) use ($search) {
                $q->where('supply_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            })
            ->tap($applyDateRange)
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

        $storageStats = $this->storageStats();

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
            'dateFrom',
            'dateTo',
            'storageStats',
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

    /**
     * Bulk restore selected archived records (same ArchiveService workflow as
     * single restore). IDs come from the tab checkboxes; type from the tab.
     */
    public function bulkRestore(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:patient,staff,user,health-record,learning-material,video,pregnancy,supply-request,referral,maternal-death,maternal-morbidity',
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'integer|min:1',
        ]);

        if (in_array($data['type'], ['patient', 'staff', 'user'], true)
            && ($request->user()->role ?? null) === 'rhu') {
            $blocked = User::withTrashed()->whereIn('id', $data['ids'])
                ->whereIn('role', ['cho', 'rhu'])->exists();
            if ($blocked) {
                return back()->with('error', 'Only the City Health Office can restore administrator accounts.');
            }
        }

        $service = app(\App\Services\ArchiveService::class);
        $restored = 0;
        $errors = [];
        foreach (array_unique($data['ids']) as $id) {
            try {
                match ($data['type']) {
                    'patient', 'staff', 'user' => $service->restoreUser((int) $id, $request->user()),
                    'health-record' => $service->restoreRecord(HealthRecord::onlyTrashed()->findOrFail($id), $request->user()),
                    'learning-material', 'video' => $service->restoreRecord(LearningMaterial::onlyTrashed()->findOrFail($id), $request->user()),
                    'pregnancy' => $service->restoreRecord(Pregnancy::onlyTrashed()->findOrFail($id), $request->user()),
                    'supply-request' => $service->restoreRecord(SupplyRequest::onlyTrashed()->findOrFail($id), $request->user()),
                    'referral' => $service->restoreRecord(\App\Models\CheckupReferral::onlyTrashed()->findOrFail($id), $request->user()),
                    'maternal-death' => $service->restoreRecord(MaternalDeath::onlyTrashed()->findOrFail($id), $request->user()),
                    'maternal-morbidity' => $service->restoreRecord(MaternalMorbidity::onlyTrashed()->findOrFail($id), $request->user()),
                };
                $restored++;
            } catch (\Throwable $e) {
                $errors[] = "#{$id}";
            }
        }

        ActivityLog::log('bulk_restore', "Bulk-restored {$restored} archived {$data['type']} record(s)", null);
        $message = "Restored {$restored} record(s).";
        if ($errors) {
            $message .= ' Skipped: ' . implode(', ', $errors) . '.';
        }

        return back()->with($restored ? 'success' : 'error', $message);
    }

    /**
     * Permanent deletion of selected archived records. CHO only, requires an
     * explicit typed confirmation, and is audit-logged. Never automatic:
     * storage pressure alone must not delete health records.
     */
    public function bulkDelete(Request $request)
    {
        abort_unless(($request->user()->role ?? null) === 'cho', 403, 'Only the City Health Office can permanently delete archived records.');

        $data = $request->validate([
            'type' => 'required|in:patient,staff,user,health-record,learning-material,video,pregnancy,supply-request,referral,maternal-death,maternal-morbidity',
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'integer|min:1',
            'confirm' => 'required|in:PERMANENTLY DELETE',
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $deleted = 0;
        foreach (array_unique($data['ids']) as $id) {
            try {
                $model = match ($data['type']) {
                    'patient', 'staff', 'user' => User::onlyTrashed()->findOrFail($id),
                    'health-record' => HealthRecord::onlyTrashed()->findOrFail($id),
                    'learning-material', 'video' => LearningMaterial::onlyTrashed()->findOrFail($id),
                    'pregnancy' => Pregnancy::onlyTrashed()->findOrFail($id),
                    'supply-request' => SupplyRequest::onlyTrashed()->findOrFail($id),
                    'referral' => \App\Models\CheckupReferral::onlyTrashed()->findOrFail($id),
                    'maternal-death' => MaternalDeath::onlyTrashed()->findOrFail($id),
                    'maternal-morbidity' => MaternalMorbidity::onlyTrashed()->findOrFail($id),
                };
                // Health-record retention: patient/pregnancy/health rows are
                // never hard-deleted through the archive hub.
                if ($model instanceof User && ($model->role ?? null) === 'user') {
                    continue;
                }
                if ($model instanceof Pregnancy || $model instanceof HealthRecord) {
                    continue;
                }
                $model->forceDelete();
                $deleted++;
            } catch (\Throwable $e) {
                continue;
            }
        }

        ActivityLog::log('bulk_delete', "Permanently deleted {$deleted} archived {$data['type']} record(s). Reason: {$data['reason']}", null);

        return back()->with('success', "Permanently deleted {$deleted} archived record(s). Patient, pregnancy and health records are always retained for audit.");
    }

    /**
     * Storage overview: which archived groups consume the most rows / files.
     * Read-only sizes only — nothing here deletes anything automatically.
     */
    private function storageStats(): array
    {
        $diskFiles = static function (?string $path): int {
            if (! $path) {
                return 0;
            }
            $full = public_path($path);
            if (! is_file($full)) {
                $full = storage_path('app/public/' . ltrim($path, '/'));
            }

            return is_file($full) ? (int) filesize($full) : 0;
        };

        $materialBytes = (int) LearningMaterial::onlyTrashed()->get(['file', 'video_url'])->sum(
            fn ($m) => $diskFiles($m->file)
        );
        $backupBytes = 0;
        $backupCount = 0;
        $backupDir = storage_path('app/backups');
        if (is_dir($backupDir)) {
            foreach ((array) glob($backupDir . '/*') as $file) {
                if (is_file($file)) {
                    $backupCount++;
                    $backupBytes += (int) filesize($file);
                }
            }
        }

        $format = fn (int $bytes): string => $bytes >= 1073741824
            ? round($bytes / 1073741824, 2) . ' GB'
            : ($bytes >= 1048576 ? round($bytes / 1048576, 2) . ' MB' : round($bytes / 1024, 2) . ' KB');

        return [
            ['label' => 'Archived patients', 'rows' => User::onlyTrashed()->where('role', 'user')->count(), 'bytes' => null, 'note' => 'Database rows only — retained for audit.'],
            ['label' => 'Archived staff', 'rows' => User::onlyTrashed()->where('role', '!=', 'user')->count(), 'bytes' => null, 'note' => 'Database rows only.'],
            ['label' => 'Archived health records', 'rows' => HealthRecord::onlyTrashed()->count(), 'bytes' => null, 'note' => 'Database rows only — never auto-deleted.'],
            ['label' => 'Archived pregnancies', 'rows' => Pregnancy::onlyTrashed()->count(), 'bytes' => null, 'note' => 'Database rows only — never auto-deleted.'],
            ['label' => 'Archived supply requests', 'rows' => SupplyRequest::onlyTrashed()->count(), 'bytes' => null, 'note' => 'Database rows only.'],
            ['label' => 'Archived learning files', 'rows' => LearningMaterial::onlyTrashed()->count(), 'bytes' => $format($materialBytes), 'note' => 'Largest file consumer — review videos first.'],
            ['label' => 'Database backups', 'rows' => $backupCount, 'bytes' => $format($backupBytes), 'note' => 'Old exports can be removed by an admin after verification.'],
        ];
    }
}
