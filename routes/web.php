<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // profil (semua role login)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================
    // ADMIN + OPERATOR ONLY
    // ============================
    Route::middleware(['role:admin,operator'])->group(function () {

        Route::resource('students', StudentController::class);
        Route::resource('teachers', TeacherController::class);

        Route::post('students/{student}/documents', [StudentController::class, 'storeDocument'])
            ->name('students.documents.store');

        Route::post('teachers/{teacher}/documents', [TeacherController::class, 'storeDocument'])
            ->name('teachers.documents.store');
    });
});

require __DIR__.'/auth.php';
