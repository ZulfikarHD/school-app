<?php

/**
 * Parent Routes - Routes untuk role PARENT (Orang Tua/Wali)
 *
 * File ini berisi semua routes yang berhubungan dengan fungsi orang tua,
 * yaitu: dashboard, children viewing, attendance history, leave request management,
 * dan payment/billing viewing
 */

use App\Http\Controllers\Dashboard\ParentDashboardController;
use App\Http\Controllers\Parent\ChildController;
use App\Http\Controllers\Parent\LeaveRequestController;
use App\Http\Controllers\Parent\ParentPsbController;
use App\Http\Controllers\Parent\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:PARENT'])->group(function () {
    // Dashboard
    Route::get('parent/dashboard', [ParentDashboardController::class, 'index'])
        ->name('parent.dashboard');

    Route::prefix('parent')->name('parent.')->group(function () {
        /**
         * Children Portal - Melihat data, kehadiran, nilai, dan rapor anak
         */
        Route::prefix('children')->name('children.')->group(function () {
            Route::get('/', [ChildController::class, 'index'])->name('index');
            Route::get('{student}', [ChildController::class, 'show'])->name('show');
            Route::get('{student}/attendance', [ChildController::class, 'attendance'])->name('attendance');
            Route::get('{student}/attendance/export', [ChildController::class, 'exportAttendance'])->name('attendance.export');

            // Grades - Rekap nilai anak
            Route::get('{student}/grades', [ChildController::class, 'grades'])->name('grades');

            // Report Cards - Rapor anak
            Route::prefix('{student}/report-cards')->name('report-cards.')->group(function () {
                Route::get('/', [ChildController::class, 'reportCards'])->name('index');
                Route::get('{reportCard}', [ChildController::class, 'showReportCard'])->name('show');
                Route::get('{reportCard}/download', [ChildController::class, 'downloadReportCard'])->name('download');
            });
        });

        /**
         * PSB Re-registration - Daftar ulang untuk pendaftar yang diterima
         */
        Route::prefix('psb')->name('psb.')->group(function () {
            Route::get('/re-register', [ParentPsbController::class, 'reRegister'])->name('re-register');
            Route::post('/re-register', [ParentPsbController::class, 'submitReRegister'])->name('submit-re-register');
            Route::post('/payment', [ParentPsbController::class, 'uploadPayment'])->name('upload-payment');
            Route::get('/welcome', [ParentPsbController::class, 'welcome'])->name('welcome');
        });

        /**
         * Leave Request Management - Pengajuan izin untuk anak
         * Menggunakan resource routes tanpa show action
         */
        Route::resource('leave-requests', LeaveRequestController::class)
            ->except(['show']);

        /**
         * Payments Portal - Melihat tagihan, riwayat pembayaran, dan submit pembayaran
         * Updated: Mendukung combined payment (1 transaksi untuk multiple bills)
         */
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('history', [PaymentController::class, 'history'])->name('history');
            Route::get('submit', [PaymentController::class, 'showSubmit'])->name('submit');
            Route::post('submit', [PaymentController::class, 'submitPayment'])->name('submit.store');
            Route::get('pending', [PaymentController::class, 'pendingPayments'])->name('pending');

            // Transaction-based routes (combined payment)
            Route::get('transactions/{transaction}', [PaymentController::class, 'showTransaction'])->name('transactions.show');
            Route::get('transactions/{transaction}/receipt', [PaymentController::class, 'downloadTransactionReceipt'])->name('transactions.receipt');
            Route::post('transactions/{transaction}/cancel', [PaymentController::class, 'cancelTransaction'])->name('transactions.cancel');

            // Legacy payment receipt (backward compatibility)
            Route::get('{payment}/receipt', [PaymentController::class, 'downloadReceipt'])->name('receipt');
        });
    });
});
