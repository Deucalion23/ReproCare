<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MidwifeController;
use App\Http\Controllers\BhwController;
use App\Http\Controllers\BhwPresidentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CheckupController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\PregnancyController;
use App\Http\Controllers\MenstruationController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\MaternalCareTargetClientController;
use App\Http\Controllers\ChildCareTargetClientController;
use App\Http\Controllers\ChildCheckupController;
use App\Http\Controllers\BhwAssignmentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\RhuController;
use App\Http\Controllers\ChoController;
use App\Http\Controllers\ChoHandoverController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\StaffTransitionController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\ArchivedRecordController;
use App\Http\Controllers\PostpartumController;
use App\Http\Controllers\GisController;
use App\Http\Controllers\WorkflowController;

// Landing page routes
Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/reprocare', function () {
    return view('landing');
})->name('reprocare');

// Dashboard route (redirects based on role)
Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();
    return match($user->role) {
        'cho' => redirect()->route('cho.dashboard'),
        'rhu' => redirect()->route('rhu.dashboard'),
        'midwife' => redirect()->route('midwife.dashboard'),
        'bhw' => redirect()->route('bhw.dashboard'),
        'bhw_president' => redirect()->route('bhw-president.dashboard'),
        'user' => redirect()->route('user.dashboard'),
        default => redirect()->route('login'),
    };
})->name('dashboard')->middleware('absolute.logout');

// Legacy short URLs (old bookmarks): redirect to the real auth pages.
Route::redirect('/login', '/auth/login', 301);
Route::redirect('/register', '/auth/register', 301);

// Authentication Routes (Guest + throttled)
Route::prefix('auth')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register')->middleware('guest');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post')->middleware('throttle:3,1');
    Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request')->middleware('guest');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:3,1');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update')->middleware('throttle:5,1');
});

// Logout (Authenticated)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Midwife Routes
Route::prefix('midwife')->name('midwife.')->middleware(['web', 'absolute.logout', 'auth', 'role:midwife', 'prevent-back'])->group(function () {
    Route::get('/dashboard', [MidwifeController::class, 'dashboard'])->name('dashboard');
    Route::get('/settings', [MidwifeController::class, 'settings'])->name('settings');
    Route::put('/settings', [MidwifeController::class, 'updateSettings'])->name('settings.update');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [MidwifeController::class, 'notifications'])->name('index');
        Route::get('/create', [MidwifeController::class, 'createNotification'])->name('create');
        Route::post('/', [MidwifeController::class, 'storeNotification'])->name('store');
        Route::get('/{id}', [MidwifeController::class, 'showNotification'])->name('show');
        Route::post('/{id}/read', [MidwifeController::class, 'markNotificationAsRead'])->name('mark-read');
        Route::delete('/{id}', [MidwifeController::class, 'deleteNotification'])->name('delete');
    });
    
    // Patients (view only — registration & verification belong to BHW / RHU Admin)
    Route::get('/patients', [MidwifeController::class, 'patients'])->name('patients');
    Route::get('/decision-support', [MidwifeController::class, 'decisionSupport'])->name('decision-support');
    Route::get('/patients/{id}', [MidwifeController::class, 'patientDetails'])->name('patient-details');



    Route::prefix('maternal-care-target-clients')->name('maternal-care-target-clients.')->group(function () {
        Route::get('/', [MaternalCareTargetClientController::class, 'index'])->name('index');
        Route::get('/print', [MaternalCareTargetClientController::class, 'print'])->name('print');
        Route::get('/create', [MaternalCareTargetClientController::class, 'create'])->name('create');
        Route::post('/', [MaternalCareTargetClientController::class, 'store'])->name('store');
        Route::get('/{woman}/edit', [MaternalCareTargetClientController::class, 'edit'])->name('edit');
        Route::get('/{woman}/{pregnancyId}/edit', [MaternalCareTargetClientController::class, 'edit'])->name('edit');
        Route::put('/{woman}/{pregnancyId?}', [MaternalCareTargetClientController::class, 'update'])->name('update');
    });

    Route::prefix('child-care-target-clients')->name('child-care-target-clients.')->group(function () {
        Route::get('/', [ChildCareTargetClientController::class, 'index'])->name('index');
        Route::get('/print', [ChildCareTargetClientController::class, 'print'])->name('print');
        Route::get('/create', [ChildCareTargetClientController::class, 'create'])->name('create');
        Route::post('/', [ChildCareTargetClientController::class, 'store'])->name('store');
        Route::get('/{child}/edit', [ChildCareTargetClientController::class, 'edit'])->name('edit');
        Route::put('/{child}', [ChildCareTargetClientController::class, 'update'])->name('update');
    });

    // Child Checkups
    Route::prefix('child-checkups')->name('child-checkups.')->group(function () {
        Route::get('/{childId}', [ChildCheckupController::class, 'index'])->name('index');
        Route::get('/{childId}/create', [ChildCheckupController::class, 'create'])->name('create');
        Route::post('/{childId}', [ChildCheckupController::class, 'store'])->name('store');
        Route::get('/{childId}/{id}', [ChildCheckupController::class, 'show'])->name('show');
        Route::get('/{childId}/{id}/edit', [ChildCheckupController::class, 'edit'])->name('edit');
        Route::put('/{childId}/{id}', [ChildCheckupController::class, 'update'])->name('update');
        Route::delete('/{childId}/{id}', [ChildCheckupController::class, 'destroy'])->name('destroy');
    });

    // Admin Dashboard (Health Officials Analytics)
    Route::get('/admin-dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Checkups
    Route::prefix('checkups')->name('checkups.')->group(function () {
        Route::get('/', [CheckupController::class, 'index'])->name('index');
        Route::get('/create/{userId?}', [CheckupController::class, 'create'])->name('create');
        Route::post('/', [CheckupController::class, 'store'])->name('store');
        Route::get('/{id}', [CheckupController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CheckupController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CheckupController::class, 'update'])->name('update');
        Route::delete('/{id}', [CheckupController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/complete', [CheckupController::class, 'markCompleted'])->name('complete');
        Route::post('/{id}/miss', [CheckupController::class, 'markMissed'])->name('miss');
        Route::post('/{id}/schedule', [CheckupController::class, 'markScheduled'])->name('schedule');
        Route::post('/{id}/cancel', [CheckupController::class, 'markCancelled'])->name('cancel');
    });
    
    // Health Records
    Route::prefix('health-records')->name('health-records.')->group(function () {
        Route::get('/', [HealthRecordController::class, 'index'])->name('index');
        Route::get('/archived', [HealthRecordController::class, 'archived'])->name('archived');
        Route::get('/create/{userId?}', [HealthRecordController::class, 'create'])->name('create');
        Route::post('/', [HealthRecordController::class, 'store'])->name('store');
        Route::get('/{id}', [HealthRecordController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [HealthRecordController::class, 'edit'])->name('edit');
        Route::put('/{id}', [HealthRecordController::class, 'update'])->name('update');
        Route::post('/{id}/accept', [HealthRecordController::class, 'acceptFromPresident'])->name('accept');
        Route::post('/{id}/archive', [HealthRecordController::class, 'archive'])->name('archive');
        Route::delete('/{id}', [HealthRecordController::class, 'destroy'])->name('destroy');
        Route::get('/patient/{userId}', [HealthRecordController::class, 'patientRecords'])->name('patient');
        Route::get('/complete/{userId}', [HealthRecordController::class, 'completeRecords'])->name('complete');
        Route::get('/incomplete/{userId}', [HealthRecordController::class, 'incompleteRecords'])->name('incomplete');
        Route::get('/download/{userId}', [HealthRecordController::class, 'downloadRecords'])->name('download');
        Route::get('/patient-records/{userId}', [HealthRecordController::class, 'patientRecords'])->name('patient-records');
    });
    
    // Pregnancies
    Route::prefix('pregnancies')->name('pregnancies.')->group(function () {
        Route::get('/', [PregnancyController::class, 'index'])->name('index');
        Route::get('/active', [PregnancyController::class, 'active'])->name('active');
        Route::get('/create/{userId?}', [PregnancyController::class, 'create'])->name('create');
        Route::post('/', [PregnancyController::class, 'store'])->name('store');
        Route::get('/{id}', [PregnancyController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PregnancyController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PregnancyController::class, 'update'])->name('update');
        Route::delete('/{id}', [PregnancyController::class, 'destroy'])->name('destroy');
        // Workflow routes
        Route::post('/{id}/submit', [PregnancyController::class, 'submitToBhwPresident'])->name('submit');
        Route::post('/{id}/reopen', [PregnancyController::class, 'reopen'])->name('reopen');
    });

    // Postpartum & Newborn Monitoring (review)
    Route::prefix('postpartum')->name('postpartum.')->group(function () {
        Route::get('/', [PostpartumController::class, 'index'])->name('index');
        Route::get('/mother/{id}', [PostpartumController::class, 'showMother'])->name('show-mother');
        Route::post('/immunization/{id}', [PostpartumController::class, 'markImmunization'])->name('immunize');
    });
    
    // Menstruation Records
    Route::prefix('menstruation')->name('menstruation.')->group(function () {
        Route::get('/', [MenstruationController::class, 'index'])->name('index');
        Route::get('/create/{userId?}', [MenstruationController::class, 'create'])->name('create');
        Route::post('/', [MenstruationController::class, 'store'])->name('store');
        Route::get('/{id}', [MenstruationController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [MenstruationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [MenstruationController::class, 'update'])->name('update');
        Route::delete('/{id}', [MenstruationController::class, 'destroy'])->name('destroy');
        Route::get('/patient/{userId}', [MenstruationController::class, 'patientRecords'])->name('patient');
    });
    
    // Learning Materials
    Route::prefix('learning')->name('learning.')->group(function () {
        Route::get('/', [LearningController::class, 'adminIndex'])->name('index');
        Route::get('/create', [LearningController::class, 'create'])->name('create');
        Route::post('/', [LearningController::class, 'store'])->name('store');
        Route::get('/{id}', [LearningController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LearningController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LearningController::class, 'update'])->name('update');
        Route::delete('/{id}', [LearningController::class, 'destroy'])->name('destroy');
    });
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [MidwifeController::class, 'reportsIndex'])->name('index');
        Route::get('/export/csv', [MidwifeController::class, 'reportsExportCsv'])->name('export.csv');
        Route::get('/export/pdf', [MidwifeController::class, 'reportsExportPdf'])->name('export.pdf');
        // Keep existing 'details' route for backward compatibility and add 'show'
        Route::get('/{id}/details', [MidwifeController::class, 'reportsDetails'])->name('details');
        Route::get('/{id}', [MidwifeController::class, 'reportsDetails'])->name('show');
    });
    
    // Pregnant Patients
    Route::get('/pregnant-patients', [MidwifeController::class, 'pregnantPatients'])->name('pregnant-patients');
    Route::get('/pregnant-patients/{id}/history', [MidwifeController::class, 'pregnancyHistory'])->name('pregnancy-history');

    // Risk Alerts inbox (clinical review queue)
    Route::get('/risk-alerts', [MidwifeController::class, 'riskAlerts'])->name('risk-alerts');
    
    // Forum Administration
    Route::prefix('forum-admin')->name('forum.admin.')->group(function () {
        Route::get('/', [ForumController::class, 'adminIndex'])->name('index');
        Route::get('/create', [ForumController::class, 'adminCreate'])->name('create');
        Route::post('/', [ForumController::class, 'adminStore'])->name('store');
        Route::delete('/bulk', [ForumController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/{id}', [ForumController::class, 'adminShow'])->name('show');
        Route::get('/{id}/edit', [ForumController::class, 'adminEdit'])->name('edit');
        Route::put('/{id}', [ForumController::class, 'adminUpdate'])->name('update');
        Route::delete('/{id}', [ForumController::class, 'adminDestroy'])->name('destroy');
        Route::post('/{id}/restore', [ForumController::class, 'restorePost'])->name('restore');
    });
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/create', [NotificationController::class, 'create'])->name('create');
        Route::post('/', [NotificationController::class, 'store'])->name('store');
    });
    


    // Checkup Referrals
    Route::prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', [MidwifeController::class, 'referrals'])->name('index');
        Route::get('/{id}', [MidwifeController::class, 'showReferral'])->name('show');
        Route::post('/{id}/review', [MidwifeController::class, 'reviewReferral'])->name('review');
        Route::post('/{id}/convert', [MidwifeController::class, 'convertToCheckup'])->name('convert');
        Route::post('/{id}/decline', [MidwifeController::class, 'declineReferral'])->name('decline');
    });

    // Walk-in Patients (review + portal account activation for Unlinked Profiles;
    // demographic edits belong to the recording BHW)
    Route::prefix('walk-in-patients')->name('walk-in-patients.')->group(function () {
        Route::get('/', [MidwifeController::class, 'walkInPatients'])->name('index');
        Route::get('/{id}', [MidwifeController::class, 'showWalkInPatient'])->name('show');
        Route::get('/{id}/activate', [MidwifeController::class, 'activateWalkInPatient'])->name('activate');
        Route::post('/{id}/activate', [MidwifeController::class, 'storeActivatedAccount'])->name('store-activate');
    });



    // Messaging
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/compose', [MessageController::class, 'create'])->name('create');
        Route::get('/trash', [MessageController::class, 'trash'])->name('trash');
        Route::post('/send', [MessageController::class, 'send'])->name('send');
        Route::get('/{id}/updates', [MessageController::class, 'updates'])->name('updates');
        Route::get('/{id}', [MessageController::class, 'thread'])->name('thread');
        Route::post('/{id}/read', [MessageController::class, 'markRead'])->name('mark-read');
        Route::post('/{id}/restore', [MessageController::class, 'restore'])->name('restore');
        Route::delete('/{id}', [MessageController::class, 'destroy'])->name('destroy');
    });

    // SMS Alerts
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/', [SmsController::class, 'index'])->name('index');
        Route::post('/send', [SmsController::class, 'send'])->name('send');
        Route::post('/walk-in', [SmsController::class, 'sendWalkIn'])->name('walk-in');
        Route::post('/broadcast', [SmsController::class, 'broadcast'])->name('broadcast');
    });

    // BHW Presidents are managed by RHU Admin (rhu.bhw-presidents.*).
});

// BHW Routes
Route::prefix('bhw')->name('bhw.')->middleware(['web', 'absolute.logout', 'auth', 'role:bhw', 'prevent-back'])->group(function () {
    Route::get('/dashboard', [BhwController::class, 'dashboard'])->name('dashboard');
    Route::get('/settings', [BhwController::class, 'settings'])->name('settings');
    Route::put('/settings', [BhwController::class, 'updateSettings'])->name('settings.update');
    Route::get('/notifications', [BhwController::class, 'notifications'])->name('notifications.index');
    Route::get('/patients', [BhwController::class, 'patients'])->name('patients');
    Route::get('/patients/create', [BhwController::class, 'createWoman'])->name('patients.create');
    Route::post('/patients', [BhwController::class, 'storeWoman'])->name('patients.store');
    Route::get('/patients/{id}', [BhwController::class, 'patientDetails'])->name('patient-details');
    Route::get('/patients/{id}/menstruation', [BhwController::class, 'patientMenstruation'])->name('patient-menstruation');
    Route::get('/patients/{id}/menstruation/report', [BhwController::class, 'patientMenstruationReport'])->name('patient-menstruation-report');
    Route::get('/patients/{id}/menstruation/export', [BhwController::class, 'patientMenstruationExport'])->name('patient-menstruation-export');
    Route::get('/schedules', [BhwController::class, 'schedules'])->name('schedules');

    // Checkups (BHW view only — no manual archive; completed checkups
    // automatically move to the archived list)
    Route::prefix('checkups')->name('checkups.')->group(function () {
        Route::get('/', [BhwController::class, 'checkups'])->name('index');
        Route::get('/archived', [BhwController::class, 'archivedCheckups'])->name('archived');
        Route::get('/create/{userId}', [BhwController::class, 'createCheckup'])->name('create');
        Route::post('/', [BhwController::class, 'storeCheckup'])->name('store')->middleware('sync');
    });

    // Health Records (Add Only)
    Route::prefix('health-records')->name('health-records.')->group(function () {
        Route::get('/', [BhwController::class, 'healthRecords'])->name('index');
        Route::get('/create/{userId?}/{walkInPatientId?}', [BhwController::class, 'createHealthRecord'])->name('create');
        Route::post('/', [BhwController::class, 'storeHealthRecord'])->name('store')->middleware('sync');
        Route::post('/{id}/submit-to-president', [BhwController::class, 'submitHealthRecordToPresident'])->name('submit-to-president');
        Route::post('/archive/{id}', [BhwController::class, 'archiveHealthRecord'])->name('archive');
    });

    // Postpartum & Newborn Monitoring
    Route::prefix('postpartum')->name('postpartum.')->group(function () {
        Route::get('/', [PostpartumController::class, 'index'])->name('index');
        Route::get('/mother/{id}', [PostpartumController::class, 'showMother'])->name('show-mother');
        Route::get('/newborn/create', [PostpartumController::class, 'createNewborn'])->name('newborn-create');
        Route::post('/newborn', [PostpartumController::class, 'storeNewborn'])->name('newborn-store')->middleware('sync');
        Route::get('/visit/create', [PostpartumController::class, 'createVisit'])->name('visit-create');
        Route::post('/visit', [PostpartumController::class, 'storeVisit'])->name('visit-store')->middleware('sync');
        Route::post('/immunization/{id}', [PostpartumController::class, 'markImmunization'])->name('immunize');
    });

    // Pregnancies (list + BHW field report to the midwife queue)
    Route::prefix('pregnancies')->name('pregnancies.')->group(function () {
        Route::get('/', [BhwController::class, 'pregnancies'])->name('index');
        Route::get('/create', [BhwController::class, 'createPregnancy'])->name('create');
        Route::post('/', [BhwController::class, 'storePregnancy'])->name('store')->middleware('sync');
    });

    // Learning Materials
    Route::prefix('learning')->name('learning.')->group(function () {
        Route::get('/', [LearningController::class, 'bhwIndex'])->name('index');
        Route::get('/{id}', [LearningController::class, 'bhwShow'])->name('show');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [BhwController::class, 'reportsIndex'])->name('index');
        Route::get('/create', [BhwController::class, 'reportsCreate'])->name('create');
        Route::post('/', [BhwController::class, 'reportsStore'])->name('store');
        Route::get('/{id}', [BhwController::class, 'reportsShow'])->name('show');
        Route::get('/{id}/print', [BhwController::class, 'reportsPrint'])->name('print');
        Route::delete('/{id}', [BhwController::class, 'reportsDestroy'])->name('destroy');
        Route::post('/{id}/submit-to-president', [BhwController::class, 'reportsSubmitToPresident'])->name('submit-to-president');
    });

    // Checkup Referrals
    Route::prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', [BhwController::class, 'referrals'])->name('index');
        Route::get('/create', [BhwController::class, 'createReferral'])->name('create');
        Route::get('/report-pregnancy/{pregnancy}', [BhwController::class, 'reportPregnancy'])->name('report-pregnancy');
        Route::post('/report-pregnancy', [BhwController::class, 'storePregnancyReport'])->name('store-pregnancy-report');
        Route::post('/', [BhwController::class, 'storeReferral'])->name('store')->middleware('sync');
        Route::get('/{id}', [BhwController::class, 'showReferral'])->name('show');
    });

    // My Tasks (assigned by BHW President — view + advance only)
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [BhwController::class, 'myTasks'])->name('index');
        Route::post('/{id}/start', [BhwController::class, 'startTask'])->name('start');
        Route::post('/{id}/complete', [BhwController::class, 'completeTask'])->name('complete');
    });

    // Walk-in Patients
    Route::prefix('walk-in-patients')->name('walk-in-patients.')->group(function () {
        Route::get('/', [BhwController::class, 'walkInPatients'])->name('index');
        Route::get('/create', [BhwController::class, 'createWalkInPatient'])->name('create');
        Route::get('/trash', [BhwController::class, 'trashWalkInPatients'])->name('trash');
        Route::post('/', [BhwController::class, 'storeWalkInPatient'])->name('store')->middleware('sync');
        Route::post('/{id}/restore', [BhwController::class, 'restoreWalkInPatient'])->name('restore');
        Route::get('/{id}', [BhwController::class, 'showWalkInPatient'])->name('show');
        Route::get('/{id}/edit', [BhwController::class, 'editWalkInPatient'])->name('edit');
        Route::put('/{id}', [BhwController::class, 'updateWalkInPatient'])->name('update');
        Route::delete('/{id}', [BhwController::class, 'deleteWalkInPatient'])->name('destroy');
        Route::get('/{id}/convert', [BhwController::class, 'convertWalkInToUser'])->name('convert');
        Route::post('/{id}/convert', [BhwController::class, 'storeConvertedUser'])->name('store-convert');
    });

    // Messages
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/compose', [MessageController::class, 'create'])->name('create');
        Route::get('/trash', [MessageController::class, 'trash'])->name('trash');
        Route::post('/send', [MessageController::class, 'send'])->name('send');
        Route::get('/{id}/updates', [MessageController::class, 'updates'])->name('updates');
        Route::get('/{id}', [MessageController::class, 'thread'])->name('thread');
        Route::post('/{id}/read', [MessageController::class, 'markRead'])->name('mark-read');
        Route::post('/{id}/restore', [MessageController::class, 'restore'])->name('restore');
        Route::delete('/{id}', [MessageController::class, 'destroy'])->name('destroy');
    });
});

// BHW President Routes
Route::prefix('bhw-president')->name('bhw-president.')->middleware(['web', 'absolute.logout', 'auth', 'role:bhw_president', 'prevent-back'])->group(function () {
    Route::get('/dashboard', [BhwPresidentController::class, 'dashboard'])->name('dashboard');

    // Patient account verification belongs to RHU Admin (rhu.pending-patients).
    
    // BHW Management
    Route::prefix('bhws')->name('bhws.')->group(function () {
        Route::get('/', [BhwPresidentController::class, 'bhws'])->name('index');
        Route::get('/create', [BhwPresidentController::class, 'createBhw'])->name('create');
        Route::post('/', [BhwPresidentController::class, 'storeBhw'])->name('store');
        Route::get('/{id}', [BhwPresidentController::class, 'bhwDetails'])->name('details');
        Route::post('/{id}/assign-purok', [BhwPresidentController::class, 'assignPurok'])->name('assign-purok');
        Route::post('/{id}/archive', [BhwPresidentController::class, 'archiveBhw'])->name('archive');
        Route::post('/{id}/inactive', [BhwPresidentController::class, 'markInactive'])->name('inactive');
        Route::post('/{id}/activate', [BhwPresidentController::class, 'activateBhw'])->name('activate');
        Route::delete('/{id}', [BhwPresidentController::class, 'deleteBhw'])->name('delete');
    });

    // Tasks
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/create', [TaskController::class, 'create'])->name('create');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{id}', [TaskController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{id}', [TaskController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/complete', [TaskController::class, 'markComplete'])->name('complete');
        Route::post('/{id}/progress', [TaskController::class, 'markInProgress'])->name('progress');
    });

    Route::prefix('health-records')->name('health-records.')->group(function () {
        Route::get('/', [BhwPresidentController::class, 'healthRecords'])->name('index');
        Route::post('/{id}/pass', [BhwPresidentController::class, 'passHealthRecordToMidwife'])->name('pass');
        // Workflow routes
        Route::get('/{id}/review', [HealthRecordController::class, 'bhwPresidentReview'])->name('review');
        Route::post('/{id}/approve', [HealthRecordController::class, 'bhwPresidentApprove'])->name('approve');
        Route::post('/{id}/reject', [HealthRecordController::class, 'bhwPresidentReject'])->name('reject');
    });
    
    // Analytics
    Route::get('/analytics', [BhwPresidentController::class, 'analytics'])->name('analytics');
    
    // Coverage
    Route::get('/coverage', [BhwPresidentController::class, 'coverage'])->name('coverage');
    
    // High-Risk
    Route::get('/high-risk', [BhwPresidentController::class, 'highRisk'])->name('high-risk');
    
    // Pregnancies
    Route::prefix('pregnancies')->name('pregnancies.')->group(function () {
        Route::get('/', [BhwPresidentController::class, 'pregnancies'])->name('index');
        // Workflow routes
        Route::get('/{id}/review', [PregnancyController::class, 'bhwPresidentReview'])->name('review');
        Route::post('/{id}/approve', [PregnancyController::class, 'bhwPresidentApprove'])->name('approve');
        Route::post('/{id}/reject', [PregnancyController::class, 'bhwPresidentReject'])->name('reject');
    });
    
    // Settings
    Route::get('/settings', [BhwPresidentController::class, 'settings'])->name('settings');
    Route::put('/settings', [BhwPresidentController::class, 'updateSettings'])->name('settings.update');
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [BhwPresidentController::class, 'reports'])->name('index');
        Route::get('/{id}', [BhwPresidentController::class, 'reportShow'])->name('show');
        Route::delete('/{id}', [BhwPresidentController::class, 'reportDelete'])->name('delete');
        Route::post('/{id}/approve', [BhwPresidentController::class, 'reportApprove'])->name('approve');
        Route::post('/{id}/reject', [BhwPresidentController::class, 'reportReject'])->name('reject');
    });

    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/compose', [MessageController::class, 'create'])->name('create');
        Route::get('/trash', [MessageController::class, 'trash'])->name('trash');
        Route::post('/send', [MessageController::class, 'send'])->name('send');
        Route::get('/{id}/updates', [MessageController::class, 'updates'])->name('updates');
        Route::get('/{id}', [MessageController::class, 'thread'])->name('thread');
        Route::post('/{id}/read', [MessageController::class, 'markRead'])->name('mark-read');
        Route::post('/{id}/restore', [MessageController::class, 'restore'])->name('restore');
        Route::delete('/{id}', [MessageController::class, 'destroy'])->name('destroy');
    });
});

// User (Patient) Routes
Route::prefix('user')->name('user.')->middleware(['web', 'absolute.logout', 'auth', 'role:user', 'prevent-back'])->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    
    // Profile - Use unified profile system at /profile
    Route::get('/settings', [UserController::class, 'settings'])->name('settings');
    
    // Menstruation Tracking
    Route::prefix('menstruation')->name('menstruation.')->group(function () {
        Route::get('/', [UserController::class, 'menstruation'])->name('index');
        Route::get('/calendar', [UserController::class, 'menstruationCalendar'])->name('calendar');
        Route::get('/statistics', [UserController::class, 'menstruationStatistics'])->name('statistics');
        Route::get('/report', [UserController::class, 'generateMenstruationReport'])->name('report');
        Route::get('/create', [UserController::class, 'createMenstruationRecord'])->name('create');
        Route::post('/', [UserController::class, 'storeMenstruationRecord'])->name('store');
        Route::delete('/{id}', [UserController::class, 'destroyMenstruationRecord'])->name('destroy');
    });
    
    // Cycle Tracking API (Task 1/5)
    Route::get('/cycles', [UserController::class, 'getCycles'])->name('cycles.index');
    Route::post('/cycles', [UserController::class, 'storeCycle'])->name('cycles.store');
    Route::delete('/cycles/{id}', [UserController::class, 'destroyCycle'])->name('cycles.destroy');
    Route::get('/cycles/prediction', [UserController::class, 'getCyclePrediction'])->name('cycles.prediction');
    Route::get('/cycles/calendar', [UserController::class, 'getCycleCalendar'])->name('cycles.calendar');
    Route::get('/cycle-status', [UserController::class, 'getCycleStatus'])->name('cycle.status');
    // Pregnancy Tracking
    Route::prefix('pregnancies')->name('pregnancies.')->group(function () {
        Route::get('/', [UserController::class, 'pregnancies'])->name('index');
        Route::get('/create', [UserController::class, 'createPregnancy'])->name('create');
        Route::post('/', [UserController::class, 'storePregnancy'])->name('store');
        Route::get('/{id}', [UserController::class, 'showPregnancy'])->name('show');
    });
    
    // View Only
    Route::get('/checkups', [UserController::class, 'checkups'])->name('checkups');
    Route::get('/health-records', [UserController::class, 'healthRecords'])->name('health-records');
    Route::get('/health-records/create', [UserController::class, 'createHealthRecord'])->name('health-records.create');
    Route::post('/health-records', [UserController::class, 'storeHealthRecord'])->name('health-records.store');
    Route::get('/notifications', [UserController::class, 'notifications'])->name('notifications');

    // Messaging
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/compose', [MessageController::class, 'create'])->name('create');
        Route::get('/trash', [MessageController::class, 'trash'])->name('trash');
        Route::post('/send', [MessageController::class, 'send'])->name('send');
        Route::get('/{id}/updates', [MessageController::class, 'updates'])->name('updates');
        Route::get('/{id}', [MessageController::class, 'thread'])->name('thread');
        Route::post('/{id}/read', [MessageController::class, 'markRead'])->name('mark-read');
        Route::post('/{id}/restore', [MessageController::class, 'restore'])->name('restore');
        Route::delete('/{id}', [MessageController::class, 'destroy'])->name('destroy');
    });
});

// Forum Routes (All authenticated users)
Route::prefix('forum')->name('forum.')->middleware(['web', 'absolute.logout', 'auth', 'prevent-back'])->group(function () {
    Route::get('/', [ForumController::class, 'index'])->name('index');
    Route::get('/create', [ForumController::class, 'create'])->name('create');
    Route::post('/', [ForumController::class, 'store'])->name('store');
    Route::get('/{id}', [ForumController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [ForumController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ForumController::class, 'update'])->name('update');
    Route::delete('/{id}', [ForumController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/comment', [ForumController::class, 'comment'])->name('comment');
    Route::post('/{id}/like', [ForumController::class, 'like'])->name('like');
});

// Profile Routes (Unified for all roles)
Route::prefix('profile')->name('profile.')->middleware(['web', 'absolute.logout', 'auth', 'prevent-back'])->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('show');
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/', [ProfileController::class, 'update'])->name('update');
    Route::put('/emergency-contacts', [ProfileController::class, 'updateEmergencyContacts'])->name('emergency-contacts.update');
    Route::post('/remove-image', [ProfileController::class, 'removeImage'])->name('remove-image');
    Route::get('/download', [ProfileController::class, 'downloadData'])->name('download');
    Route::delete('/account', [ProfileController::class, 'destroyAccount'])->name('account.destroy');
    Route::get('/{id}', [ProfileController::class, 'viewProfile'])->name('view');
});

// Learning Materials (Public for authenticated users)
Route::prefix('learning')->name('learning.')->middleware(['web', 'absolute.logout', 'auth', 'prevent-back'])->group(function () {
    Route::get('/', [LearningController::class, 'index'])->name('index');
    Route::get('/articles', [LearningController::class, 'articles'])->name('articles');
    Route::get('/videos', [LearningController::class, 'videos'])->name('videos');
    Route::get('/links', [LearningController::class, 'links'])->name('links');
    Route::get('/files', [LearningController::class, 'files'])->name('files');
    Route::get('/training', [LearningController::class, 'hcwTraining'])->name('training');
    Route::get('/week/{week}', [LearningController::class, 'weekGuide'])->name('week-guide');
    Route::get('/{id}', [LearningController::class, 'show'])->name('show');
    Route::post('/{id}/quiz', [LearningController::class, 'submitQuiz'])->name('quiz.submit');
});

// Device API Routes (PWA offline-cache + session validation)
Route::prefix('api/device')->name('api.device.')->middleware(['web', 'auth', 'prevent-back'])->group(function () {
    Route::get('/session-check', [DeviceController::class, 'sessionCheck'])->name('session-check');
});

// Notification API Routes
Route::prefix('api/notifications')->name('api.notifications.')->middleware(['web', 'auth', 'prevent-back'])->group(function () {
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('delete');
});

// CHO Routes
Route::prefix('cho')->name('cho.')->middleware(['web', 'absolute.logout', 'auth', 'role:cho', 'prevent-back'])->group(function () {
    Route::get('/dashboard', [ChoController::class, 'dashboard'])->name('dashboard');
    Route::get('/settings', [ChoController::class, 'settings'])->name('settings');
    Route::put('/settings', [ChoController::class, 'updateSettings'])->name('settings.update');

    // CHO Super Admin Role Handover & Succession
    Route::prefix('handover')->name('handover.')->group(function () {
        Route::get('/', [ChoHandoverController::class, 'index'])->name('index');
        Route::post('/recovery-key', [ChoHandoverController::class, 'setupRecoveryKey'])->name('recovery-key');
        Route::post('/execute', [ChoHandoverController::class, 'execute'])->name('execute');
    });

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [ChoController::class, 'users'])->name('index');
        Route::get('/create', [ChoController::class, 'createUser'])->name('create');
        Route::post('/', [ChoController::class, 'storeUser'])->name('store');
        Route::get('/{id}', [ChoController::class, 'userDetails'])->name('show');
        Route::post('/{id}/approve', [ChoController::class, 'approveUser'])->name('approve');
        Route::post('/{id}/reject', [ChoController::class, 'rejectUser'])->name('reject');
        Route::post('/{id}/deactivate', [ChoController::class, 'deactivateUser'])->name('deactivate');
        Route::post('/{id}/activate', [ChoController::class, 'activateUser'])->name('activate');
    });

    // Supply Requests
    Route::prefix('supply-requests')->name('supply-requests.')->group(function () {
        Route::get('/', [ChoController::class, 'supplyRequests'])->name('index');
        Route::get('/{id}', [ChoController::class, 'showSupplyRequest'])->name('show');
        Route::post('/{id}/approve', [ChoController::class, 'approveSupplyRequest'])->name('approve');
        Route::post('/{id}/decline', [ChoController::class, 'rejectSupplyRequest'])->name('decline');
    });

    // Maternal Deaths
    Route::prefix('maternal-deaths')->name('maternal-deaths.')->group(function () {
        Route::get('/', [ChoController::class, 'maternalDeaths'])->name('index');
        Route::get('/{id}', [ChoController::class, 'showMaternalDeath'])->name('show');
        Route::post('/{id}/audit', [ChoController::class, 'auditMaternalDeath'])->name('audit');
    });

    // Activity Logs
    Route::get('/logs', [ChoController::class, 'logs'])->name('logs.index');

    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics');
    Route::post('/analytics/chat', [\App\Http\Controllers\AnalyticsController::class, 'chat'])->middleware('throttle:10,1')->name('analytics.chat');

    // City-Wide Pregnancy Monitoring
    Route::prefix('pregnancies')->name('pregnancies.')->group(function () {
        Route::get('/', [ChoController::class, 'pregnancies'])->name('index');
        Route::get('/{id}', [ChoController::class, 'pregnancyDetails'])->name('show');
    });

    // Immunization Records
    Route::prefix('immunization')->name('immunization.')->group(function () {
        Route::get('/', [ChoController::class, 'immunizationRecords'])->name('index');
    });

    // City-Wide Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ChoController::class, 'reports'])->name('index');
        Route::get('/export/csv', [ChoController::class, 'exportReportsCsv'])->name('export.csv');
        Route::get('/export/pdf', [ChoController::class, 'exportReportsPdf'])->name('export.pdf');
    });

    // Staff Management (read-only directory; account lifecycle lives under users.*)
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [ChoController::class, 'staffIndex'])->name('index');
        Route::get('/midwives', [ChoController::class, 'midwives'])->name('midwives');
        Route::get('/bhws', [ChoController::class, 'bhws'])->name('bhws');
    });

    // Database Management
    Route::prefix('database')->name('database.')->group(function () {
        Route::get('/', [DatabaseBackupController::class, 'index'])->name('index');
        Route::post('/export', [DatabaseBackupController::class, 'export'])->name('export');
        Route::post('/import', [DatabaseBackupController::class, 'import'])->name('import');
        Route::get('/download/{filename}', [DatabaseBackupController::class, 'download'])->name('download');
        Route::delete('/delete/{filename}', [DatabaseBackupController::class, 'delete'])->name('delete');
    });

    // GIS Maternal Risk Heat Map
    Route::prefix('gis')->name('gis.')->group(function () {
        Route::get('/', [GisController::class, 'index'])->name('index');
        Route::get('/data', [GisController::class, 'data'])->name('data');
    });

    // SMS Management (Read Only)
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/', [SmsController::class, 'index'])->name('index');
    });

    // Archived Records Hub (No Hard Deletes / Data Integrity)
    Route::prefix('archived')->name('archived.')->group(function () {
        Route::get('/', [ArchivedRecordController::class, 'index'])->name('index');
        Route::post('/{type}/{id}/restore', [ArchivedRecordController::class, 'restore'])->name('restore');
    });
});

// RHU Routes
Route::prefix('rhu')->name('rhu.')->middleware(['web', 'absolute.logout', 'auth', 'role:rhu', 'prevent-back'])->group(function () {
    Route::get('/dashboard', [RhuController::class, 'dashboard'])->name('dashboard');
    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/pregnancies/{id}', [\App\Http\Controllers\AnalyticsController::class, 'pregnancy'])->name('analytics.pregnancies.show');
    Route::post('/analytics/chat', [\App\Http\Controllers\AnalyticsController::class, 'chat'])->middleware('throttle:10,1')->name('analytics.chat');
    Route::get('/settings', [RhuController::class, 'settings'])->name('settings');
    Route::put('/settings', [RhuController::class, 'updateSettings'])->name('settings.update');

    // Women: registration & account verification (RHU Admin only)
    Route::get('/patients/create', [RhuController::class, 'createPatient'])->name('patients.create');
    Route::post('/patients', [RhuController::class, 'storePatient'])->name('patients.store');
    Route::get('/pending-patients', [RhuController::class, 'pendingPatients'])->name('pending-patients');
    Route::post('/pending-patients/{id}/approve', [RhuController::class, 'approvePatient'])->name('approve-patient');
    Route::post('/pending-patients/{id}/reject', [RhuController::class, 'rejectPatient'])->name('reject-patient');
    Route::post('/pending-patients/{id}/link', [RhuController::class, 'linkDuplicate'])->name('link-duplicate');
    Route::post('/pending-patients/{id}/dismiss', [RhuController::class, 'dismissDuplicate'])->name('dismiss-duplicate');

    // Midwife Management
    Route::prefix('midwives')->name('midwives.')->group(function () {
        Route::get('/', [RhuController::class, 'midwives'])->name('index');
        Route::get('/create', [RhuController::class, 'createMidwife'])->name('create');
        Route::post('/', [RhuController::class, 'storeMidwife'])->name('store');
        Route::get('/{id}', [RhuController::class, 'midwifeDetails'])->name('show');
        Route::get('/{id}/edit', [RhuController::class, 'editMidwife'])->name('edit');
        Route::put('/{id}', [RhuController::class, 'updateMidwife'])->name('update');
        Route::delete('/{id}', [RhuController::class, 'destroyMidwife'])->name('destroy');
    });

    // BHW Presidents
    Route::prefix('bhw-presidents')->name('bhw-presidents.')->group(function () {
        Route::get('/', [RhuController::class, 'bhwPresidents'])->name('index');
        Route::get('/create', [RhuController::class, 'createBhwPresident'])->name('create');
        Route::post('/', [RhuController::class, 'storeBhwPresident'])->name('store');
        Route::post('/promote', [RhuController::class, 'promoteBhwPresident'])->name('promote');
        Route::get('/{id}', [RhuController::class, 'showBhwPresident'])->name('show');
        Route::get('/{id}/edit', [RhuController::class, 'editBhwPresident'])->name('edit');
        Route::put('/{id}', [RhuController::class, 'updateBhwPresident'])->name('update');
        Route::delete('/{id}', [RhuController::class, 'destroyBhwPresident'])->name('destroy');
    });

    // BHW Account Management — single archive function (no deletes: accounts
    // are archived with a reason, sessions revoked, history retained).
    Route::prefix('bhws')->name('bhws.')->group(function () {
        Route::get('/', [RhuController::class, 'bhws'])->name('index');
        Route::get('/create', [RhuController::class, 'createBhw'])->name('create');
        Route::post('/', [RhuController::class, 'storeBhw'])->name('store');
        Route::get('/{id}/edit', [RhuController::class, 'editBhw'])->name('edit');
        Route::put('/{id}', [RhuController::class, 'updateBhw'])->name('update');
        Route::post('/{id}/archive', [RhuController::class, 'archiveBhw'])->name('archive');
        Route::post('/{id}/activate', [RhuController::class, 'activateBhw'])->name('activate');
    });

    // Staff Transitions (replace / transfer with audit + reassignment)
    Route::prefix('staff-transitions')->name('staff-transitions.')->group(function () {
        Route::get('/', [StaffTransitionController::class, 'index'])->name('index');
        Route::post('/execute', [StaffTransitionController::class, 'execute'])->name('execute');
    });

    // Supply Requests
    Route::prefix('supply-requests')->name('supply-requests.')->group(function () {
        Route::get('/', [RhuController::class, 'supplyRequests'])->name('index');
        Route::get('/create', [RhuController::class, 'createSupplyRequest'])->name('create');
        Route::post('/', [RhuController::class, 'storeSupplyRequest'])->name('store');
        Route::get('/{id}', [RhuController::class, 'showSupplyRequest'])->name('show');
        Route::delete('/{id}', [RhuController::class, 'destroySupplyRequest'])->name('destroy');
    });

    // Maternal Deaths
    Route::prefix('maternal-deaths')->name('maternal-deaths.')->group(function () {
        Route::get('/', [RhuController::class, 'maternalDeaths'])->name('index');
        Route::get('/create', [RhuController::class, 'createMaternalDeath'])->name('create');
        Route::post('/', [RhuController::class, 'storeMaternalDeath'])->name('store');
        Route::get('/{id}', [RhuController::class, 'showMaternalDeath'])->name('show');
        Route::get('/{id}/edit', [RhuController::class, 'editMaternalDeath'])->name('edit');
        Route::put('/{id}', [RhuController::class, 'updateMaternalDeath'])->name('update');
        Route::delete('/{id}', [RhuController::class, 'destroyMaternalDeath'])->name('destroy');
    });

    // Maternal Morbidities / Near-Miss
    Route::prefix('morbidities')->name('morbidities.')->group(function () {
        Route::get('/', [RhuController::class, 'morbidities'])->name('index');
        Route::get('/create', [RhuController::class, 'createMorbidity'])->name('create');
        Route::post('/', [RhuController::class, 'storeMorbidity'])->name('store');
        Route::get('/{id}', [RhuController::class, 'showMorbidity'])->name('show');
        Route::get('/{id}/edit', [RhuController::class, 'editMorbidity'])->name('edit');
        Route::put('/{id}', [RhuController::class, 'updateMorbidity'])->name('update');
        Route::delete('/{id}', [RhuController::class, 'destroyMorbidity'])->name('destroy');
    });

    // Activity Logs
    Route::get('/logs', [RhuController::class, 'logs'])->name('logs.index');

    // Reports (FHSIS/MNCHN)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [RhuController::class, 'reports'])->name('index');
        Route::get('/details/{id}', [RhuController::class, 'reportDetails'])->name('details');
        Route::get('/{id}', [RhuController::class, 'reportDetails'])->name('show');
        Route::get('/export/csv', [RhuController::class, 'exportCsv'])->name('export.csv');
        Route::get('/export/pdf', [RhuController::class, 'exportPdf'])->name('export.pdf');
    });

    // GIS Maternal Risk Heat Map
    Route::prefix('gis')->name('gis.')->group(function () {
        Route::get('/', [GisController::class, 'index'])->name('index');
        Route::get('/data', [GisController::class, 'data'])->name('data');
    });

    // BHW Monthly Reports Approval
    Route::prefix('bhw-reports')->name('bhw-reports.')->group(function () {
        Route::get('/', [RhuController::class, 'bhwReports'])->name('index');
        Route::get('/{id}', [RhuController::class, 'bhwReportShow'])->name('show');
        Route::get('/{id}/print', [RhuController::class, 'bhwReportPrint'])->name('print');
        Route::post('/{id}/approve', [RhuController::class, 'bhwReportApprove'])->name('approve');
        Route::post('/{id}/reject', [RhuController::class, 'bhwReportReject'])->name('reject');
        Route::delete('/{id}', [RhuController::class, 'bhwReportDestroy'])->name('destroy');
    });

    // Database Backup & Restore
    Route::prefix('database')->name('database.')->group(function () {
        Route::get('/', [DatabaseBackupController::class, 'index'])->name('index');
        Route::post('/export', [DatabaseBackupController::class, 'export'])->name('export');
        Route::post('/import', [DatabaseBackupController::class, 'import'])->name('import');
        Route::get('/download/{filename}', [DatabaseBackupController::class, 'download'])->name('download');
        Route::delete('/delete/{filename}', [DatabaseBackupController::class, 'delete'])->name('delete');
    });

    // SMS Alerts (midwife + RHU admin only)
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/', [SmsController::class, 'index'])->name('index');
        Route::post('/send', [SmsController::class, 'send'])->name('send');
        Route::post('/walk-in', [SmsController::class, 'sendWalkIn'])->name('walk-in');
        Route::post('/broadcast', [SmsController::class, 'broadcast'])->name('broadcast');
    });
});

// ─── Cross-cutting workflows: correction loop, transfers, emergency,
//     delivery auto-transition (BHW creates; President/RHU/Midwife review) ───
Route::prefix('workflow')->name('workflow.')->middleware(['web', 'absolute.logout', 'auth', 'prevent-back'])->group(function () {
    // 1. Correction & Resubmission (BHW)
    Route::get('/revision-queue', [WorkflowController::class, 'revisionQueue'])->name('revision-queue')->middleware('role:bhw');
    Route::post('/health-records/{id}/resubmit', [WorkflowController::class, 'resubmitHealthRecord'])->name('health-records.resubmit')->middleware('role:bhw');
    Route::post('/reports/{id}/resubmit', [WorkflowController::class, 'resubmitReport'])->name('reports.resubmit')->middleware('role:bhw');
    Route::post('/health-records/{id}/send-back', [WorkflowController::class, 'midwifeSendBack'])->name('health-records.send-back')->middleware('role:midwife');

    // 2. Patient Relocation (BHW requests; President/RHU/Midwife approve)
    Route::get('/transfers', [WorkflowController::class, 'transfers'])->name('transfers.index');
    Route::post('/transfers', [WorkflowController::class, 'requestTransfer'])->name('transfers.store');
    Route::post('/transfers/{id}/approve', [WorkflowController::class, 'approveTransfer'])->name('transfers.approve');
    Route::post('/transfers/{id}/reject', [WorkflowController::class, 'rejectTransfer'])->name('transfers.reject');

    // 3. Emergency Fast-Lane (BHW panic button; Midwife/RHU acknowledge)
    Route::post('/emergency', [WorkflowController::class, 'emergency'])->name('emergency');
    Route::post('/emergency/{id}/acknowledge', [WorkflowController::class, 'acknowledgeEmergency'])->name('emergency.acknowledge');

    // 4. Pregnancy → Postpartum auto-transition (BHW/Midwife log delivery)
    Route::post('/pregnancies/{pregnancyId}/delivery', [WorkflowController::class, 'logDelivery'])->name('pregnancies.delivery');
});
