<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use App\Models\FailedLoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login dengan form authentication
     */
    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle proses login user dengan validasi credentials, rate limiting,
     * activity logging, dan timing attack mitigation untuk audit trail
     */
    public function login(LoginRequest $request)
    {
        $identifier = $request->validated('identifier');
        $password = $request->validated('password');
        $remember = $request->validated('remember');
        $ipAddress = $request->ip();

        // Check apakah account sedang dalam status locked karena failed attempts
        $failedAttempt = FailedLoginAttempt::where('identifier', $identifier)
            ->where('ip_address', $ipAddress)
            ->first();

        if ($failedAttempt && $failedAttempt->isLocked()) {
            $remainingMinutes = now()->diffInMinutes($failedAttempt->locked_until);

            return back()->withErrors([
                'identifier' => "Akun terkunci karena terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$remainingMinutes} menit.",
            ]);
        }

        // Cari user berdasarkan username atau email
        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        // TIMING ATTACK MITIGATION: Always hash check even if user doesn't exist
        // untuk prevent user enumeration via response time analysis
        $dummyHash = '$2y$12$dummyHashValueForTimingAttackPrevention1234567890';
        $passwordToCheck = $user ? $user->password : $dummyHash;
        $passwordValid = Hash::check($password, $passwordToCheck);

        // Single validation check untuk prevent timing leaks
        if (! $user || ! $passwordValid || ! $user->isActive()) {
            $this->handleFailedLogin($identifier, $ipAddress);

            // Generic error message - tidak specify apakah username atau password yang salah
            return back()->withErrors([
                'identifier' => 'Username/email atau password salah, atau akun tidak aktif.',
            ]);
        }

        // Login berhasil - reset failed attempts
        if ($failedAttempt) {
            $failedAttempt->delete();
        }

        // Update last login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'status' => 'success',
        ]);

        // Login user ke session
        Auth::login($user, $remember);

        $request->session()->regenerate();

        // Redirect berdasarkan kondisi
        // Untuk first login, gunakan Inertia::location() untuk full page reload
        // untuk memastikan authentication state ter-update dengan benar
        if ($user->is_first_login) {
            return Inertia::location(route('auth.first-login'));
        }

        // Untuk login normal, gunakan redirect biasa untuk smooth Inertia navigation
        $dashboardRoute = $this->getDashboardRoute($user->role);
        $intendedUrl = $request->session()->pull('url.intended');

        // Jika ada intended URL dan valid, gunakan itu
        if ($intendedUrl && $intendedUrl !== route('login')) {
            return redirect($intendedUrl);
        }

        return redirect()->route($dashboardRoute);
    }

    /**
     * Handle logout user dengan clear session dan activity logging
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Log activity sebelum logout
        if ($user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success',
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('message', 'Anda telah keluar dari sistem.');
    }

    /**
     * Handle failed login attempt dengan increment counter dan lock account
     * setelah 5 percobaan gagal untuk brute force protection
     */
    protected function handleFailedLogin(string $identifier, string $ipAddress): void
    {
        $failedAttempt = FailedLoginAttempt::firstOrNew([
            'identifier' => $identifier,
            'ip_address' => $ipAddress,
        ]);

        $failedAttempt->attempts += 1;

        if ($failedAttempt->attempts >= 5) {
            $failedAttempt->locked_until = now()->addMinutes(15);
        }

        $failedAttempt->save();

        // Log failed login
        ActivityLog::create([
            'user_id' => null,
            'action' => 'failed_login',
            'ip_address' => $ipAddress,
            'user_agent' => request()->userAgent(),
            'new_values' => [
                'identifier' => $identifier,
                'attempts' => $failedAttempt->attempts,
            ],
            'status' => 'failed',
        ]);
    }

    /**
     * Helper untuk mendapatkan route dashboard berdasarkan user role
     * Note: STUDENT role currently disabled, redirect to login instead
     */
    protected function getDashboardRoute(string $role): string
    {
        return match ($role) {
            'SUPERADMIN', 'ADMIN' => 'admin.dashboard',
            'PRINCIPAL' => 'principal.dashboard',
            'TEACHER' => 'teacher.dashboard',
            'PARENT' => 'parent.dashboard',
            // 'STUDENT' => 'student.dashboard', // DISABLED - Student portal belum diimplementasi
            default => 'login', // Changed from 'home' - redirect to login for undefined roles including STUDENT
        };
    }
}
