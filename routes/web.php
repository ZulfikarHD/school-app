<?php

/**
 * Web Routes - Shared/Public Routes
 *
 * File ini berisi routes yang bersifat umum dan digunakan oleh semua role,
 * yaitu: home redirect, profile management, dan dashboard redirect
 *
 * Role-specific routes telah dipindahkan ke file terpisah:
 * - routes/admin.php     : SUPERADMIN, ADMIN
 * - routes/principal.php : PRINCIPAL
 * - routes/teacher.php   : TEACHER
 * - routes/parent.php    : PARENT
 * - routes/auth.php      : Authentication (login, logout, password reset)
 */

use Illuminate\Support\Facades\Route;

/**
 * Public Routes - accessible tanpa authentication
 */
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

/**
 * Authenticated Routes - accessible oleh semua user yang sudah login
 */
Route::middleware(['auth'])->group(function () {
    /**
     * Profile Routes - Management profil dan password untuk semua user
     */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Profile\ProfileController::class, 'show'])
            ->name('show');
        Route::post('/password', [\App\Http\Controllers\Profile\PasswordController::class, 'update'])
            ->name('password.update');
    });

    /**
     * Universal Dashboard Route - Redirect ke dashboard sesuai role user
     *
     * Route ini berfungsi sebagai entry point universal dimana user akan
     * di-redirect ke dashboard yang sesuai dengan role mereka
     */
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

    // Student Dashboard - DISABLED (untuk future implementation)
    // TODO: Uncomment ketika Student Portal sudah siap diimplementasi
    // Route::middleware('role:STUDENT')->group(function () {
    //     Route::get('/student/dashboard', function () {
    //         return Inertia::render('Dashboard/StudentDashboard');
    //     })->name('student.dashboard');
    // });
});
