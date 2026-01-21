<?php

/**
 * Admin Routes - Routes untuk role SUPERADMIN dan ADMIN (TU)
 *
 * File ini berisi semua routes yang berhubungan dengan fungsi administrasi,
 * yaitu: user management, student management, attendance management,
 * audit logs, leave request verification, dan payment management
 */

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\PaymentCategoryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherAttendanceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:SUPERADMIN,ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    /**
     * User Management - Resource routes dengan custom actions
     * Resource: index, create, store, show, edit, update, destroy
     */
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users.reset-password');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');
    Route::resource('users', UserController::class);

    /**
     * Student Management - Resource routes dengan custom actions
     *
     * IMPORTANT: Custom routes harus didefinisikan SEBELUM resource routes
     * untuk menghindari conflict dengan route parameter {student}
     */
    Route::prefix('students')->name('students.')->group(function () {
        // Bulk Operations
        Route::get('promote', [StudentController::class, 'showPromotePage'])->name('promote.page');
        Route::post('promote', [StudentController::class, 'promote'])->name('promote');
        Route::post('assign-class', [StudentController::class, 'assignClass'])->name('assign-class');

        // Import/Export
        Route::get('export', [StudentController::class, 'export'])->name('export');
        Route::post('import/preview', [StudentController::class, 'importPreview'])->name('import.preview');
        Route::post('import', [StudentController::class, 'import'])->name('import');
    });
    Route::post('students/{student}/update-status', [StudentController::class, 'updateStatus'])
        ->name('students.update-status');
    Route::resource('students', StudentController::class);

    /**
     * Audit Log - Read-only access untuk monitoring aktivitas sistem
     */
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    /**
     * Student Attendance Management - Reports, corrections, dan exports
     */
    Route::prefix('attendance')->name('attendance.')->group(function () {
        // Student Attendance
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [AttendanceController::class, 'studentsIndex'])->name('index');
            Route::get('report', [AttendanceController::class, 'generateReport'])->name('report');
            Route::get('export', [AttendanceController::class, 'exportStudents'])->name('export');
            Route::get('export/pdf', [AttendanceController::class, 'exportPdf'])->name('export.pdf');
            Route::get('correction', [AttendanceController::class, 'correction'])->name('correction');
        });

        // Teacher Attendance
        Route::prefix('teachers')->name('teachers.')->group(function () {
            Route::get('/', [TeacherAttendanceController::class, 'index'])->name('index');
            Route::get('report', [TeacherAttendanceController::class, 'generateReport'])->name('report');
            Route::get('export', [TeacherAttendanceController::class, 'exportTeachers'])->name('export');
            Route::get('export/payroll', [TeacherAttendanceController::class, 'exportPayroll'])->name('export.payroll');
        });

        // Shared Attendance Actions
        Route::get('statistics', [AttendanceController::class, 'getStatistics'])->name('statistics');
        Route::put('{attendance}', [AttendanceController::class, 'update'])->name('update');
        Route::delete('{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
    });

    /**
     * Student Leave Request Verification - Admin verifikasi izin siswa
     */
    Route::get('leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])
        ->name('leave-requests.approve');

    /**
     * Payment Management - Kategori pembayaran, tagihan, dan pembayaran
     */
    Route::patch('payment-categories/{payment_category}/toggle-status', [PaymentCategoryController::class, 'toggleStatus'])
        ->name('payment-categories.toggle-status');
    Route::resource('payment-categories', PaymentCategoryController::class);

    /**
     * Bills Management - Generate dan kelola tagihan siswa
     */
    Route::prefix('payments/bills')->name('payments.bills.')->group(function () {
        Route::get('/', [BillController::class, 'index'])->name('index');
        Route::get('generate', [BillController::class, 'showGenerate'])->name('generate');
        Route::post('preview', [BillController::class, 'preview'])->name('preview');
        Route::post('/', [BillController::class, 'store'])->name('store');
        Route::delete('{bill}', [BillController::class, 'destroy'])->name('destroy');
    });

    /**
     * Payment Recording - Catat dan kelola pembayaran siswa
     */
    Route::prefix('payments/records')->name('payments.records.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('create', [PaymentController::class, 'create'])->name('create');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('verification', [PaymentController::class, 'verification'])->name('verification');
        Route::get('{payment}', [PaymentController::class, 'show'])->name('show');
        Route::get('{payment}/receipt', [PaymentController::class, 'receipt'])->name('receipt');
        Route::get('{payment}/receipt/stream', [PaymentController::class, 'receiptStream'])->name('receipt.stream');
        Route::post('{payment}/verify', [PaymentController::class, 'verify'])->name('verify');
        Route::post('{payment}/cancel', [PaymentController::class, 'cancel'])->name('cancel');
    });

    /**
     * API Endpoints untuk Payment (AJAX)
     */
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('students/search', [PaymentController::class, 'searchStudents'])->name('students.search');
        Route::get('students/{student}/unpaid-bills', [PaymentController::class, 'getUnpaidBills'])->name('students.unpaid-bills');
    });
});
