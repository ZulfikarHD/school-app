<?php

/**
 * Teacher Routes - Routes untuk role TEACHER (Guru)
 *
 * File ini berisi semua routes yang berhubungan dengan fungsi guru,
 * yaitu: dashboard, student viewing, daily attendance, subject attendance,
 * clock in/out, student leave verification, dan teacher's own leave management
 */

use App\Http\Controllers\Dashboard\TeacherDashboardController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\ClockController;
use App\Http\Controllers\Teacher\LeaveRequestController;
use App\Http\Controllers\Teacher\StudentController;
use App\Http\Controllers\Teacher\SubjectAttendanceController;
use App\Http\Controllers\Teacher\TeacherLeaveController;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:TEACHER'])->group(function () {
    // Dashboard
    Route::get('teacher/dashboard', [TeacherDashboardController::class, 'index'])
        ->name('teacher.dashboard');

    /**
     * API Routes - Endpoints untuk AJAX calls dari frontend
     * Diletakkan di luar prefix /teacher untuk path yang lebih clean
     */
    Route::prefix('api')->name('teacher.api.')->group(function () {
        // Get students by class
        Route::get('classes/{class}/students', function ($classId) {
            $class = SchoolClass::findOrFail($classId);
            $students = Student::where('kelas_id', $classId)
                ->where('status', 'aktif')
                ->orderBy('nama_lengkap')
                ->get();

            return response()->json(['data' => $students]);
        })->name('classes.students');

        // Search students
        Route::get('students/search', function (Request $request) {
            $query = $request->input('q');
            $students = Student::with('kelas')
                ->where('status', 'aktif')
                ->where(function ($q) use ($query) {
                    $q->where('nama_lengkap', 'like', "%{$query}%")
                        ->orWhere('nis', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get();

            return response()->json(['data' => $students]);
        })->name('students.search');

        // Get student attendance history
        Route::get('students/{student}/attendance', function (Student $student) {
            $attendances = $student->dailyAttendances()
                ->with(['recordedBy'])
                ->orderBy('tanggal', 'desc')
                ->limit(50)
                ->get();

            return response()->json(['data' => $attendances]);
        })->name('students.attendance');
    });

    Route::prefix('teacher')->name('teacher.')->group(function () {
        /**
         * Student Viewing - Read-only, filtered by assigned classes
         * Menggunakan resource routes dengan only index dan show
         */
        Route::resource('students', StudentController::class)
            ->only(['index', 'show']);

        /**
         * Daily Attendance Management - Input kehadiran harian siswa
         */
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('check-existing', [AttendanceController::class, 'checkExisting'])->name('check-existing');

            // Daily attendance CRUD
            Route::get('daily', [AttendanceController::class, 'create'])->name('daily.create');
            Route::post('daily', [AttendanceController::class, 'store'])->name('daily.store');

            // Subject attendance
            Route::get('subject', [SubjectAttendanceController::class, 'create'])->name('subject.create');
            Route::post('subject', [SubjectAttendanceController::class, 'store'])->name('subject.store');
            Route::get('subject/history', [SubjectAttendanceController::class, 'index'])->name('subject.index');
        });

        /**
         * Clock In/Out - Presensi kehadiran guru
         */
        Route::prefix('clock')->name('clock.')->group(function () {
            Route::post('in', [ClockController::class, 'clockIn'])->name('in');
            Route::post('out', [ClockController::class, 'clockOut'])->name('out');
            Route::get('status', [ClockController::class, 'status'])->name('status');
        });
        Route::get('my-attendance', [ClockController::class, 'myAttendance'])->name('my-attendance');

        /**
         * Student Leave Request Verification - Verifikasi izin siswa oleh wali kelas
         */
        Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
            Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
            Route::post('{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('approve');
            Route::post('{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('reject');
        });

        /**
         * Teacher Leave Management - Pengajuan cuti guru sendiri
         * Menggunakan resource routes dengan only index, create, store
         */
        Route::resource('teacher-leaves', TeacherLeaveController::class)
            ->only(['index', 'create', 'store']);
    });
});
