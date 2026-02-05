<?php

/**
 * Admin Routes - Routes untuk role SUPERADMIN dan ADMIN (TU)
 *
 * File ini berisi semua routes yang berhubungan dengan fungsi administrasi,
 * yaitu: user management, student management, attendance management,
 * audit logs, leave request verification, payment management, dan grade management
 */

use App\Http\Controllers\Admin\AdminPsbAnnouncementController;
use App\Http\Controllers\Admin\AdminPsbController;
use App\Http\Controllers\Admin\AdminPsbExportController;
use App\Http\Controllers\Admin\AdminPsbPaymentController;
use App\Http\Controllers\Admin\AdminPsbSettingsController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BankReconciliationController;
use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\GradeWeightController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\PaymentCategoryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportCardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherAttendanceController;
use App\Http\Controllers\Admin\TeacherController;
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
     * Teacher Management - Resource routes untuk pengelolaan data guru
     *
     * Routes mencakup: index, create, store, edit, update, dan toggle status
     * Digunakan oleh Admin/TU untuk mengelola data guru, status kepegawaian,
     * dan assignment mata pelajaran
     */
    Route::patch('teachers/{teacher}/toggle-status', [TeacherController::class, 'toggleStatus'])
        ->name('teachers.toggle-status');
    Route::resource('teachers', TeacherController::class)->except(['show', 'destroy']);

    /**
     * PSB Management - Verifikasi pendaftaran siswa baru
     */
    Route::prefix('psb')->name('psb.')->group(function () {
        Route::get('/', [AdminPsbController::class, 'index'])->name('index');
        Route::get('registrations', [AdminPsbController::class, 'registrations'])
            ->middleware('throttle:60,1')
            ->name('registrations.index');
        Route::get('registrations/{registration}', [AdminPsbController::class, 'show'])->name('registrations.show');
        Route::post('registrations/{registration}/approve', [AdminPsbController::class, 'approve'])
            ->middleware('throttle:10,1')
            ->name('registrations.approve');
        Route::post('registrations/{registration}/reject', [AdminPsbController::class, 'reject'])
            ->middleware('throttle:10,1')
            ->name('registrations.reject');
        Route::post('registrations/{registration}/revision', [AdminPsbController::class, 'requestRevision'])
            ->middleware('throttle:10,1')
            ->name('registrations.revision');

        /**
         * PSB Announcements - Bulk announce pendaftaran yang disetujui
         */
        Route::prefix('announcements')->name('announcements.')->group(function () {
            Route::get('/', [AdminPsbAnnouncementController::class, 'index'])->name('index');
            Route::post('/bulk-announce', [AdminPsbAnnouncementController::class, 'bulkAnnounce'])
                ->middleware('throttle:10,1')
                ->name('bulk-announce');
        });

        /**
         * PSB Payments - Verifikasi pembayaran daftar ulang
         */
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [AdminPsbPaymentController::class, 'index'])->name('index');
            Route::post('/{payment}/verify', [AdminPsbPaymentController::class, 'verify'])
                ->middleware('throttle:10,1')
                ->name('verify');
        });

        /**
         * PSB Settings - Pengaturan periode pendaftaran per tahun ajaran
         */
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [AdminPsbSettingsController::class, 'index'])->name('index');
            Route::post('/', [AdminPsbSettingsController::class, 'store'])->name('store');
            Route::put('/{setting}', [AdminPsbSettingsController::class, 'update'])->name('update');
        });

        /**
         * PSB Export - Export data pendaftaran ke Excel
         */
        Route::get('export', [AdminPsbExportController::class, 'export'])->name('export');
    });

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
     * Payment Recording - Catat dan kelola pembayaran siswa (Legacy per-payment)
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
     * Payment Transactions - Combined payment management (1 transaction : N bills)
     */
    Route::prefix('payments/transactions')->name('payments.transactions.')->group(function () {
        Route::get('/', [PaymentController::class, 'transactionIndex'])->name('index');
        Route::post('/', [PaymentController::class, 'storeTransaction'])->name('store');
        Route::get('{transaction}', [PaymentController::class, 'showTransaction'])->name('show');
        Route::get('{transaction}/receipt', [PaymentController::class, 'transactionReceipt'])->name('receipt');
        Route::get('{transaction}/receipt/stream', [PaymentController::class, 'transactionReceiptStream'])->name('receipt.stream');
        Route::post('{transaction}/verify', [PaymentController::class, 'verifyTransaction'])->name('verify');
        Route::post('{transaction}/reject', [PaymentController::class, 'rejectTransaction'])->name('reject');
    });

    /**
     * Payment Reports - Laporan keuangan untuk Admin/TU
     */
    Route::prefix('payments/reports')->name('payments.reports.')->group(function () {
        Route::get('/', [PaymentController::class, 'reports'])->name('index');
        Route::get('export', [PaymentController::class, 'exportReports'])->name('export');
        Route::get('delinquents', [PaymentController::class, 'delinquents'])->name('delinquents');
    });

    /**
     * Bank Reconciliation - Rekonsiliasi bank dengan pembayaran
     */
    Route::prefix('payments/reconciliation')->name('payments.reconciliation.')->group(function () {
        Route::get('/', [BankReconciliationController::class, 'index'])->name('index');
        Route::post('upload', [BankReconciliationController::class, 'upload'])->name('upload');
        Route::get('{reconciliation}/match', [BankReconciliationController::class, 'showMatch'])->name('match');
        Route::post('{reconciliation}/auto-match', [BankReconciliationController::class, 'autoMatch'])->name('auto-match');
        Route::post('{reconciliation}/match', [BankReconciliationController::class, 'storeMatch'])->name('match.store');
        Route::post('{reconciliation}/items/{item}/unmatch', [BankReconciliationController::class, 'unmatch'])->name('unmatch');
        Route::post('{reconciliation}/verify', [BankReconciliationController::class, 'verify'])->name('verify');
        Route::get('{reconciliation}/export', [BankReconciliationController::class, 'export'])->name('export');
        Route::delete('{reconciliation}', [BankReconciliationController::class, 'destroy'])->name('destroy');
    });

    /**
     * API Endpoints untuk Payment (AJAX)
     */
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('students/search', [PaymentController::class, 'searchStudents'])->name('students.search');
        Route::get('students/{student}/unpaid-bills', [PaymentController::class, 'getUnpaidBills'])->name('students.unpaid-bills');
    });

    /**
     * Grade Management - Rekap nilai dan konfigurasi bobot
     */
    Route::prefix('grades')->name('grades.')->group(function () {
        Route::get('/', [GradeController::class, 'index'])->name('index');
        Route::get('summary', [GradeController::class, 'summary'])->name('summary');
        Route::get('export', [GradeController::class, 'export'])->name('export');
    });

    /**
     * Grade Weight Configuration - Pengaturan bobot nilai K13
     */
    Route::prefix('settings/grade-weights')->name('settings.grade-weights.')->group(function () {
        Route::get('/', [GradeWeightController::class, 'index'])->name('index');
        Route::put('/', [GradeWeightController::class, 'update'])->name('update');
    });

    /**
     * Report Card Management - Generate dan kelola rapor siswa
     */
    Route::prefix('report-cards')->name('report-cards.')->group(function () {
        Route::get('/', [ReportCardController::class, 'index'])->name('index');
        Route::get('generate', [ReportCardController::class, 'generate'])->name('generate');
        Route::post('validate', [ReportCardController::class, 'validateCompleteness'])->name('validate');
        Route::post('generate', [ReportCardController::class, 'processGenerate'])->name('process-generate');
        Route::get('download-zip', [ReportCardController::class, 'downloadZip'])->name('download-zip');
        Route::get('{reportCard}', [ReportCardController::class, 'show'])->name('show');
        Route::get('{reportCard}/download', [ReportCardController::class, 'downloadPdf'])->name('download');
        Route::post('{reportCard}/unlock', [ReportCardController::class, 'unlock'])->name('unlock');
        Route::post('{reportCard}/regenerate', [ReportCardController::class, 'regenerate'])->name('regenerate');
    });
});
