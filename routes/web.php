<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\ParentDashboardController;
use App\Http\Controllers\Dashboard\PrincipalDashboardController;
use App\Http\Controllers\Dashboard\TeacherDashboardController;
use App\Http\Controllers\Parent\ChildController;
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

        // User Management Routes
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('users', UserController::class);
            Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
                ->name('users.reset-password');
            Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
                ->name('users.toggle-status');

            // Student Management Routes
            Route::resource('students', StudentController::class);
            Route::post('students/assign-class', [StudentController::class, 'assignClass'])
                ->name('students.assign-class');
            Route::post('students/{student}/update-status', [StudentController::class, 'updateStatus'])
                ->name('students.update-status');
            Route::post('students/promote', [StudentController::class, 'promote'])
                ->name('students.promote');
            Route::get('students/export', [StudentController::class, 'export'])
                ->name('students.export');
            Route::post('students/import/preview', [StudentController::class, 'importPreview'])
                ->name('students.import.preview');
            Route::post('students/import', [StudentController::class, 'import'])
                ->name('students.import');

            // Audit Log Routes
            Route::get('audit-logs', [AuditLogController::class, 'index'])
                ->name('audit-logs.index');
        });
    });

    // Principal Dashboard - can also view audit logs
    Route::middleware('role:PRINCIPAL')->group(function () {
        Route::get('/principal/dashboard', [PrincipalDashboardController::class, 'index'])->name('principal.dashboard');

        // Audit Log Routes (read-only for Principal)
        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index');
    });

    // Teacher Dashboard
    Route::middleware('role:TEACHER')->group(function () {
        Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
    });

    // Parent Dashboard
    Route::middleware('role:PARENT')->group(function () {
        Route::get('/parent/dashboard', [ParentDashboardController::class, 'index'])->name('parent.dashboard');

        // Parent Portal - View Children
        Route::prefix('parent')->name('parent.')->group(function () {
            Route::get('children', [ChildController::class, 'index'])->name('children.index');
            Route::get('children/{student}', [ChildController::class, 'show'])->name('children.show');
        });
    });

    // Student Dashboard - DISABLED (untuk future implementation)
    // TODO: Uncomment ketika Student Portal sudah siap diimplementasi
    // Route::middleware('role:STUDENT')->group(function () {
    //     Route::get('/student/dashboard', function () {
    //         return Inertia::render('Dashboard/StudentDashboard');
    //     })->name('student.dashboard');
    // });

    // Profile Routes - accessible by all authenticated users
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Profile\ProfileController::class, 'show'])
            ->name('show');
        Route::post('/password', [\App\Http\Controllers\Profile\PasswordController::class, 'update'])
            ->name('password.update');
    });

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
