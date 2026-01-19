<?php

/**
 * Principal Routes - Routes untuk role PRINCIPAL (Kepala Sekolah)
 *
 * File ini berisi semua routes yang berhubungan dengan fungsi principal,
 * yaitu: dashboard monitoring, student viewing (read-only), teacher leave approval,
 * attendance monitoring, dan audit log viewing
 */

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Dashboard\PrincipalDashboardController;
use App\Http\Controllers\Principal\AttendanceController;
use App\Http\Controllers\Principal\StudentController;
use App\Http\Controllers\Principal\TeacherLeaveController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:PRINCIPAL'])->group(function () {
    // Dashboard
    Route::get('principal/dashboard', [PrincipalDashboardController::class, 'index'])
        ->name('principal.dashboard');

    // Audit Log - Read-only access untuk monitoring
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->name('audit-logs.index');

    Route::prefix('principal')->name('principal.')->group(function () {
        /**
         * Dashboard API - Endpoints untuk metrics dashboard
         */
        Route::get('dashboard/attendance-metrics', [PrincipalDashboardController::class, 'getAttendanceMetrics'])
            ->name('dashboard.attendance-metrics');

        /**
         * Student Viewing - Read-only access untuk melihat data siswa
         * Menggunakan resource routes dengan only index dan show
         */
        Route::resource('students', StudentController::class)
            ->only(['index', 'show']);

        /**
         * Teacher Leave Management - Approval workflow untuk cuti guru
         */
        Route::prefix('teacher-leaves')->name('teacher-leaves.')->group(function () {
            Route::get('/', [TeacherLeaveController::class, 'index'])->name('index');
            Route::post('{leave}/approve', [TeacherLeaveController::class, 'approve'])->name('approve');
            Route::post('{leave}/reject', [TeacherLeaveController::class, 'reject'])->name('reject');
        });

        /**
         * Attendance Monitoring - Read-only access untuk monitoring kehadiran
         */
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('dashboard', [AttendanceController::class, 'dashboard'])->name('dashboard');

            // Student Attendance Reports
            Route::get('students', [AttendanceController::class, 'studentsIndex'])->name('students.index');
            Route::get('students/report', [AttendanceController::class, 'studentReport'])->name('students.report');

            // Teacher Attendance Reports
            Route::get('teachers', [AttendanceController::class, 'teachersIndex'])->name('teachers.index');
            Route::get('teachers/report', [AttendanceController::class, 'teacherReport'])->name('teachers.report');
        });
    });
});
