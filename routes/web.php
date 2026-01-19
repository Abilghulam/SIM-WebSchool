<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\HomeroomAssignmentController;
use App\Http\Controllers\MyStudentController;
use App\Http\Controllers\StudentPromotionController;
use App\Http\Controllers\EnrollmentPromotionLogController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StudentImportController;
use \App\Http\Controllers\StudentBulkPlacementController;

Route::view('/', 'welcome');

// AUTH + ACTIVE (belum pakai must_change_password agar user bisa masuk ke halaman ganti password)
Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');

    });

    // Change password wajib bisa diakses meskipun must_change_password = true
    Route::get('/change-password', [PasswordChangeController::class, 'edit'])
        ->name('password.change');
    Route::put('/change-password', [PasswordChangeController::class, 'update'])
        ->name('password.change.update');

    /**
     * AREA YANG BUTUH PASSWORD SUDAH DIGANTI
     */
    Route::middleware(['must_change_password'])->group(function () {

    Route::get('/global-search', [GlobalSearchController::class, 'suggest'])
    ->name('global-search.suggest');

    Route::get('/search', [GlobalSearchController::class, 'index'])
        ->name('global-search.index');

        /**
         * ADMIN / OPERATOR (FULL ACCESS)
         * Definisikan lebih dulu supaya tidak ketangkep parameter resource route.
         */
        Route::middleware(['role:admin,operator'])->group(function () {

            // Students (CRUD terbatas)
            Route::resource('students', StudentController::class)
                ->only(['create','store','edit','update','destroy']);

            Route::post('students/{student}/documents', [StudentController::class, 'storeDocument'])
                ->name('students.documents.store');

            // Teachers (create/store/destroy + createAccount)
            Route::resource('teachers', TeacherController::class)
                ->only(['create','store','destroy']);

            Route::post('teachers/{teacher}/account', [TeacherController::class, 'createAccount'])
                ->name('teachers.account.create');

            // Teachers Account Management (admin/operator)
            Route::patch('teachers/{teacher}/account/toggle-active', [TeacherController::class, 'toggleAccountActive'])
                ->name('teachers.account.toggle-active');

            Route::patch('teachers/{teacher}/account/force-change-password', [TeacherController::class, 'forceChangePassword'])
                ->name('teachers.account.force-change-password');

            Route::put('teachers/{teacher}/account/reset-password', [TeacherController::class, 'resetAccountPassword'])
                ->name('teachers.account.reset-password');

            // Master data
            Route::resource('majors', MajorController::class)->except(['show']);

            Route::resource('school-years', SchoolYearController::class)->except(['show'])
                ->parameters(['school-years' => 'schoolYear']);

            Route::patch('school-years/{schoolYear}/activate', [SchoolYearController::class, 'activate'])
                ->name('school-years.activate');

            Route::get('/school-years/{schoolYear}', [SchoolYearController::class, 'show'])
                ->name('school-years.show');

            Route::resource('classrooms', ClassroomController::class)->except(['show']);

            Route::resource('homeroom-assignments', HomeroomAssignmentController::class)
                ->only(['index','store','destroy']);
                
            Route::get('/enrollments/promote', [StudentPromotionController::class, 'index'])
                ->name('enrollments.promote.index');

            Route::post('/enrollments/promote', [StudentPromotionController::class, 'store'])
                ->name('enrollments.promote.store');

            // Log Promote (Audit Trail)
            Route::get('/enrollments/promotions', [EnrollmentPromotionLogController::class, 'index'])
                ->name('enrollments.promotions.index');

            Route::get('/enrollments/promotions/{promotion}', [EnrollmentPromotionLogController::class, 'show'])
                ->name('enrollments.promotions.show');

            Route::middleware(['auth', 'can:manageSchoolData'])
                ->prefix('imports')
                ->name('imports.')
                ->group(function () {
                    Route::get('/students/template', [StudentImportController::class, 'template'])->name('students.template');

                    Route::get('/students', [StudentImportController::class, 'create'])->name('students.create');
                    Route::post('/students/preview', [StudentImportController::class, 'preview'])->name('students.preview');
                    Route::post('/students/commit', [StudentImportController::class, 'commit'])->name('students.commit');
                });

                Route::get('/enrollments/bulk-placement', [StudentBulkPlacementController::class, 'index'])
                    ->name('enrollments.bulk-placement.index');

                Route::post('/enrollments/bulk-placement', [StudentBulkPlacementController::class, 'store'])
                    ->name('enrollments.bulk-placement.store');

                Route::middleware(['role:admin'])->group(function () {
                    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
                    Route::get('/activity-logs/{activity}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
                });

        });

        /**
         * GENERAL (READ-ONLY / SELF-EDIT via policy)
         */
        Route::resource('students', StudentController::class)->only(['index','show']);
        Route::resource('teachers', TeacherController::class)->only(['index','show','edit','update']);

        Route::post('teachers/{teacher}/documents', [TeacherController::class, 'storeDocument'])
            ->name('teachers.documents.store');

        // Wali kelas page (wajib lewat Gate)
        Route::get('/my-class', [MyStudentController::class, 'index'])
            ->middleware(['can:viewMyClass'])
            ->name('my-class.index');
    });
});

require __DIR__ . '/auth.php';