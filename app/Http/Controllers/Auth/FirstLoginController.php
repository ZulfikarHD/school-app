<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\FirstLoginRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class FirstLoginController extends Controller
{
    /**
     * Menampilkan halaman first login untuk user yang baru pertama kali login
     * dan harus mengubah password default mereka untuk security
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // Redirect jika bukan first login
        if (! $user || ! $user->is_first_login) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/FirstLogin', [
            'user' => [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Handle update password pada first login dengan validation, hashing,
     * dan activity logging untuk audit trail
     */
    public function update(FirstLoginRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $ipAddress = $request->ip();

        // Update password dan set is_first_login ke false
        $user->update([
            'password' => Hash::make($request->validated('password')),
            'is_first_login' => false,
        ]);

        // Log activity untuk audit trail
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'first_login_password_change',
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'status' => 'success',
            'description' => 'User mengubah password pada first login',
        ]);

        // Redirect ke dashboard sesuai role
        // Note: STUDENT role currently disabled
        $dashboardRoute = match ($user->role) {
            'SUPERADMIN', 'ADMIN' => 'admin.dashboard',
            'PRINCIPAL' => 'principal.dashboard',
            'TEACHER' => 'teacher.dashboard',
            'PARENT' => 'parent.dashboard',
            // 'STUDENT' => 'student.dashboard', // DISABLED - Student portal belum diimplementasi
            default => 'login', // Changed from 'home' - redirect to login for undefined roles
        };

        return redirect()->route($dashboardRoute)->with('message', 'Password berhasil diubah. Selamat datang!');
    }
}
