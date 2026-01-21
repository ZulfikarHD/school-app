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
use App\Http\Controllers\Parent\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:PARENT'])->group(function () {
    // Dashboard
    Route::get('parent/dashboard', [ParentDashboardController::class, 'index'])
        ->name('parent.dashboard');

    Route::prefix('parent')->name('parent.')->group(function () {
        /**
         * Children Portal - Melihat data dan kehadiran anak
         */
        Route::prefix('children')->name('children.')->group(function () {
            Route::get('/', [ChildController::class, 'index'])->name('index');
            Route::get('{student}', [ChildController::class, 'show'])->name('show');
            Route::get('{student}/attendance', [ChildController::class, 'attendance'])->name('attendance');
            Route::get('{student}/attendance/export', [ChildController::class, 'exportAttendance'])->name('attendance.export');
        });

        /**
         * Leave Request Management - Pengajuan izin untuk anak
         * Menggunakan resource routes tanpa show action
         */
        Route::resource('leave-requests', LeaveRequestController::class)
            ->except(['show']);

        /**
         * Payments Portal - Melihat tagihan, riwayat pembayaran, dan submit pembayaran
         */
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('history', [PaymentController::class, 'history'])->name('history');
            Route::get('submit', [PaymentController::class, 'showSubmit'])->name('submit');
            Route::post('submit', [PaymentController::class, 'submitPayment'])->name('submit.store');
            Route::get('pending', [PaymentController::class, 'pendingPayments'])->name('pending');
            Route::get('{payment}/receipt', [PaymentController::class, 'downloadReceipt'])->name('receipt');
        });
    });
});
