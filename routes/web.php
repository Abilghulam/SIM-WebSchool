<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\HomeroomAssignmentController;
use App\Http\Controllers\MyStudentController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    /**
     * =========================
     * ADMIN / OPERATOR ROUTES
     * =========================
     * Harus didefinisikan lebih dulu agar tidak ketangkep route parameter:
     * /students/{student} atau /teachers/{teacher}
     */
    Route::middleware(['role:admin,operator'])->group(function () {

        // Students: create/store/edit/update/destroy
        Route::resource('students', StudentController::class)
            ->only(['create', 'store', 'edit', 'update', 'destroy']);

        // Upload dokumen siswa
        Route::post('students/{student}/documents', [StudentController::class, 'storeDocument'])
            ->name('students.documents.store');

        // Teachers: create/store/destroy
        Route::resource('teachers', TeacherController::class)
            ->only(['create', 'store', 'destroy']);

        Route::resource('majors', MajorController::class)->except(['show']);
        Route::resource('school-years', SchoolYearController::class)->except(['show'])
            ->parameters(['school-years' => 'schoolYear']);

        // set active school year
        Route::patch('school-years/{schoolYear}/activate', [SchoolYearController::class, 'activate'])
            ->name('school-years.activate');

        Route::resource('classrooms', ClassroomController::class)->except(['show']);
        Route::resource('homeroom-assignments', HomeroomAssignmentController::class)->only(['index', 'store', 'destroy']);
    });

    /**
     * =========================
     * GENERAL ROUTES (AUTH+ACTIVE)
     * =========================
     */

    // Students: semua role yang lolos policy boleh index + show (wali kelas read-only)
    Route::resource('students', StudentController::class)->only(['index', 'show']);

    // Teachers: index/show/edit/update untuk self (policy)
    Route::resource('teachers', TeacherController::class)->only(['index', 'show', 'edit', 'update']);

    // Upload dokumen guru (sesuai kode kamu: saat ini tidak dibatasi role)
    Route::post('teachers/{teacher}/documents', [TeacherController::class, 'storeDocument'])
        ->name('teachers.documents.store');

    Route::get('/my-class', [MyStudentController::class, 'index'])
        ->middleware(['can:viewMyClass'])
        ->name('my-class.index');

});

require __DIR__ . '/auth.php';
