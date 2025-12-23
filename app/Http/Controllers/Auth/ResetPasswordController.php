<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ResetPasswordController extends Controller
{
    /**
     * Display form untuk reset password dengan token validation
     * untuk memastikan token valid dan belum expired
     */
    public function show(Request $request, string $token)
    {
        $email = $request->query('email');

        // Validate token exists dan belum expired (1 jam)
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('created_at', '>', now()->subHour())
            ->first();

        if (! $tokenRecord) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Link reset password tidak valid atau sudah kadaluarsa. Silakan request link baru.']);
        }

        // Verify token hash
        if (hash('sha256', $token) !== $tokenRecord->token) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Link reset password tidak valid.']);
        }

        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Handle reset password execution dengan token validation,
     * password update, dan activity logging untuk audit trail
     */
    public function update(ResetPasswordRequest $request)
    {
        $email = $request->validated('email');
        $token = $request->validated('token');
        $password = $request->validated('password');

        DB::beginTransaction();

        try {
            // Validate token
            $tokenRecord = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->where('created_at', '>', now()->subHour())
                ->first();

            if (! $tokenRecord) {
                return back()->withErrors([
                    'email' => 'Link reset password tidak valid atau sudah kadaluarsa.',
                ]);
            }

            // Verify token hash
            if (hash('sha256', $token) !== $tokenRecord->token) {
                return back()->withErrors([
                    'email' => 'Link reset password tidak valid.',
                ]);
            }

            // Get user
            $user = User::where('email', $email)->first();

            if (! $user) {
                return back()->withErrors([
                    'email' => 'User tidak ditemukan.',
                ]);
            }

            // Update password
            $user->update([
                'password' => Hash::make($password),
            ]);

            // Delete token after successful reset
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'password_reset_completed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success',
            ]);

            // Optional: Logout all active sessions untuk security
            // TODO: Implement session termination

            DB::commit();

            return redirect()->route('login')
                ->with('success', 'Password berhasil diubah. Silakan login dengan password baru Anda.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'email' => 'Terjadi kesalahan saat mereset password. Silakan coba lagi.',
            ]);
        }
    }
}
