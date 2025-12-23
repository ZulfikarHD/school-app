<?php

use App\Http\Controllers\Auth\FirstLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

/**
 * Authentication Routes
 *
 * File ini berisi semua routes yang berhubungan dengan authentication flow,
 * yaitu: login, logout, password reset, dan first login handling dengan
 * security measures seperti rate limiting dan activity logging
 */

/**
 * Guest Routes - hanya bisa diakses oleh user yang belum login
 * dengan rate limiting untuk prevent brute force attacks
 */
Route::middleware('guest')->group(function () {
    // Login routes dengan rate limiting (5 attempts per minute)
    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.post');

    // Forgot Password routes dengan rate limiting (3 attempts per hour)
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])
        ->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
        ->middleware('throttle:3,60')
        ->name('password.email');

    // Reset Password routes
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])
        ->name('password.reset');

    Route::post('/reset-password', [ResetPasswordController::class, 'update'])
        ->name('password.update');
});

/**
 * Authenticated Routes - hanya bisa diakses oleh user yang sudah login
 */
Route::middleware('auth')->group(function () {
    // First Login - untuk user yang baru pertama kali login dan harus ubah password
    Route::get('/first-login', [FirstLoginController::class, 'show'])
        ->name('auth.first-login');

    Route::post('/first-login', [FirstLoginController::class, 'update'])
        ->name('auth.first-login.update');

    // Logout route
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');
});
