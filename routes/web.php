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
    });

    // Principal Dashboard
    Route::middleware('role:PRINCIPAL')->group(function () {
        Route::get('/principal/dashboard', [PrincipalDashboardController::class, 'index'])->name('principal.dashboard');
    });

    // Teacher Dashboard
    Route::middleware('role:TEACHER')->group(function () {
        Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
    });

    // Parent Dashboard
    Route::middleware('role:PARENT')->group(function () {
        Route::get('/parent/dashboard', [ParentDashboardController::class, 'index'])->name('parent.dashboard');
    });

    // Student Dashboard - DISABLED (untuk future implementation)
    // TODO: Uncomment ketika Student Portal sudah siap diimplementasi
    // Route::middleware('role:STUDENT')->group(function () {
    //     Route::get('/student/dashboard', function () {
    //         return Inertia::render('Dashboard/StudentDashboard');
    //     })->name('student.dashboard');
    // });

    // Universal /dashboard route - redirect ke dashboard sesuai role
    Route::get('/dashboard', function () {
        $user = auth()->user();

        return redirect()->route(match ($user->role) {
            'SUPERADMIN', 'ADMIN' => 'admin.dashboard',
            'PRINCIPAL' => 'principal.dashboard',
            'TEACHER' => 'teacher.dashboard',
            'PARENT' => 'parent.dashboard',
            // 'STUDENT' => 'student.dashboard', // DISABLED - Student portal belum diimplementasi
            default => 'login',
        });
    })->name('dashboard');
});
