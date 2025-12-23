<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Mail\PasswordResetMail;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    /**
     * Display form untuk request password reset link
     */
    public function show()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Handle request untuk send password reset link via email
     * dengan rate limiting dan activity logging untuk security
     */
    public function sendResetLink(ForgotPasswordRequest $request)
    {
        $email = $request->validated('email');

        // Check rate limiting: max 3 requests per 24 hours per email
        $recentRequests = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('created_at', '>', now()->subDay())
            ->count();

        if ($recentRequests >= 3) {
            return back()->withErrors([
                'email' => 'Anda telah mencapai batas maksimal permintaan reset password (3x per 24 jam). Silakan coba lagi nanti.',
            ]);
        }

        $user = User::where('email', $email)->first();

        // Check if user is active
        if (! $user->isActive()) {
            return back()->withErrors([
                'email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
            ]);
        }

        DB::beginTransaction();

        try {
            // Generate unique token
            $token = Str::random(64);

            // Delete old tokens untuk email ini
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            // Insert new token
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => hash('sha256', $token),
                'created_at' => now(),
            ]);

            // Generate reset URL
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $email]);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'password_reset_requested',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'email' => $email,
                ],
                'status' => 'success',
            ]);

            // Send email
            try {
                Mail::to($email)->send(new PasswordResetMail($resetUrl, $user->name));
            } catch (\Exception $e) {
                Log::error('Failed to send password reset email', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);

                // Still continue - user akan dapat error message
                DB::rollBack();

                return back()->withErrors([
                    'email' => 'Gagal mengirim email reset password. Silakan coba lagi atau hubungi administrator.',
                ]);
            }

            DB::commit();

            return back()->with('success', 'Link reset password telah dikirim ke email Anda. Periksa inbox Anda (link valid selama 1 jam).');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'email' => 'Terjadi kesalahan. Silakan coba lagi.',
            ]);
        }
    }
}
