<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherAttendanceController as AdminTeacherAttendanceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\ParentDashboardController;
use App\Http\Controllers\Dashboard\PrincipalDashboardController;
use App\Http\Controllers\Dashboard\TeacherDashboardController;
use App\Http\Controllers\Parent\ChildController;
use App\Http\Controllers\Parent\LeaveRequestController as ParentLeaveRequestController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\ClockController;
use App\Http\Controllers\Teacher\LeaveRequestController as TeacherLeaveRequestController;
use App\Http\Controllers\Teacher\SubjectAttendanceController;
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
            // Note: Special routes (promote, export, import) harus didefinisikan SEBELUM resource
            // untuk menghindari conflict dengan route {student}
            Route::get('students/promote', [StudentController::class, 'showPromotePage'])
                ->name('students.promote.page');
            Route::post('students/promote', [StudentController::class, 'promote'])
                ->name('students.promote');
            Route::get('students/export', [StudentController::class, 'export'])
                ->name('students.export');
            Route::post('students/import/preview', [StudentController::class, 'importPreview'])
                ->name('students.import.preview');
            Route::post('students/import', [StudentController::class, 'import'])
                ->name('students.import');
            Route::post('students/assign-class', [StudentController::class, 'assignClass'])
                ->name('students.assign-class');
            Route::post('students/{student}/update-status', [StudentController::class, 'updateStatus'])
                ->name('students.update-status');
            Route::resource('students', StudentController::class);

            // Audit Log Routes
            Route::get('audit-logs', [AuditLogController::class, 'index'])
                ->name('audit-logs.index');

            // Attendance Management Routes
            Route::get('attendance/students', [AdminAttendanceController::class, 'studentsIndex'])
                ->name('attendance.students.index');
            Route::get('attendance/students/correction', [AdminAttendanceController::class, 'correction'])
                ->name('attendance.students.correction');
            Route::get('attendance/teachers', [AdminTeacherAttendanceController::class, 'index'])
                ->name('attendance.teachers.index');
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

        // Teacher Routes untuk Attendance Management
        Route::prefix('teacher')->name('teacher.')->group(function () {
            // Daily Attendance Routes
            Route::get('attendance', [AttendanceController::class, 'index'])
                ->name('attendance.index');
            Route::get('attendance/daily', [AttendanceController::class, 'create'])
                ->name('attendance.daily.create');
            Route::post('attendance/daily', [AttendanceController::class, 'store'])
                ->name('attendance.daily.store');

            // API untuk fetch students by class
            Route::get('/api/classes/{class}/students', function ($classId) {
                $class = \App\Models\SchoolClass::findOrFail($classId);
                $students = \App\Models\Student::where('kelas_id', $classId)
                    ->where('status', 'aktif')
                    ->orderBy('nama_lengkap')
                    ->get();

                return response()->json(['data' => $students]);
            })->name('api.classes.students');

            // Subject Attendance Routes
            Route::get('attendance/subject', [SubjectAttendanceController::class, 'create'])
                ->name('attendance.subject.create');
            Route::post('attendance/subject', [SubjectAttendanceController::class, 'store'])
                ->name('attendance.subject.store');

            // Clock In/Out Routes (API endpoints)
            Route::post('clock/in', [ClockController::class, 'clockIn'])
                ->name('clock.in');
            Route::post('clock/out', [ClockController::class, 'clockOut'])
                ->name('clock.out');
            Route::get('clock/status', [ClockController::class, 'status'])
                ->name('clock.status');

            // Leave Request Verification Routes
            Route::get('leave-requests', [TeacherLeaveRequestController::class, 'index'])
                ->name('leave-requests.index');
            Route::post('leave-requests/{leaveRequest}/approve', [TeacherLeaveRequestController::class, 'approve'])
                ->name('leave-requests.approve');
        });
    });

    // Parent Dashboard
    Route::middleware('role:PARENT')->group(function () {
        Route::get('/parent/dashboard', [ParentDashboardController::class, 'index'])->name('parent.dashboard');

        // Parent Portal - View Children & Leave Requests
        Route::prefix('parent')->name('parent.')->group(function () {
            Route::get('children', [ChildController::class, 'index'])->name('children.index');
            Route::get('children/{student}', [ChildController::class, 'show'])->name('children.show');

            // Leave Request Routes
            Route::resource('leave-requests', ParentLeaveRequestController::class)
                ->only(['index', 'create', 'store']);
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
