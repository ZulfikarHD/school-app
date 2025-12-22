<?php

use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\ParentDashboardController;
use App\Http\Controllers\Dashboard\PrincipalDashboardController;
use App\Http\Controllers\Dashboard\TeacherDashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/**
 * Public Routes - accessible tanpa authentication
 */
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

/**
 * Dashboard Routes - Protected dengan authentication dan role-based access control
 */
Route::middleware(['auth'])->group(function () {
    // Admin/TU Dashboard
    Route::middleware('role:SUPERADMIN,ADMIN')->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    });

    // Principal Dashboard
    Route::middleware('role:PRINCIPAL')->group(function () {
        Route::get('/principal/dashboard', [PrincipalDashboardController::class, 'index'])->name('principal.dashboard');
        Route::get('/dashboard', [PrincipalDashboardController::class, 'index'])->name('dashboard');
    });

    // Teacher Dashboard
    Route::middleware('role:TEACHER')->group(function () {
        Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    });

    // Parent Dashboard
    Route::middleware('role:PARENT')->group(function () {
        Route::get('/parent/dashboard', [ParentDashboardController::class, 'index'])->name('parent.dashboard');
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
    });

    // Student Dashboard (optional, untuk future implementation)
    Route::middleware('role:STUDENT')->group(function () {
        Route::get('/student/dashboard', function () {
            return Inertia::render('Dashboard/StudentDashboard');
        })->name('student.dashboard');
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard/StudentDashboard');
        })->name('dashboard');
    });
});
