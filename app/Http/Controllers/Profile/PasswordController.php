<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Update user password dengan old password verification,
     * activity logging, dan optional logout other devices
     */
    public function update(ChangePasswordRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();

            // Update password
            $user->update([
                'password' => Hash::make($request->validated('password')),
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'password_changed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success',
            ]);

            // Optional: Logout all other devices
            // TODO: Implement session termination untuk other devices
            // Requires database session driver dan query ke sessions table

            DB::commit();

            return back()->with('success', 'Password berhasil diubah.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal mengubah password. Silakan coba lagi.']);
        }
    }
}
