<?php

use App\Http\Controllers\Auth\LoginController;
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
});

/**
 * Authenticated Routes - hanya bisa diakses oleh user yang sudah login
 */
Route::middleware('auth')->group(function () {
    // Logout route
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');
});
