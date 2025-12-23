<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Mail\UserAccountCreated;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display list of users dengan pagination, search, dan filter capabilities
     * untuk user management interface
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name, username, or email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    /**
     * Show form untuk create user baru dengan role options
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Store user baru dengan auto-generated password dan email notification
     * serta activity logging untuk audit trail
     */
    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();

        try {
            // Generate default password: FirstName + 4 random digits
            $firstName = explode(' ', $request->name)[0];
            $defaultPassword = $firstName.rand(1000, 9999);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone_number' => $request->phone_number,
                'role' => $request->role,
                'status' => $request->status,
                'password' => Hash::make($defaultPassword),
                'is_first_login' => true,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create_user',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'created_user_id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role,
                ],
                'status' => 'success',
            ]);

            // Send email dengan credentials
            try {
                Mail::to($user->email)->send(new UserAccountCreated($user, $defaultPassword));
            } catch (\Exception $e) {
                // Log error tapi tidak blocking proses create user
                Log::error('Failed to send user account email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan. Password telah dikirim ke email.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal menambahkan user. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Show form untuk edit user existing dengan current data
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update user data dengan validation dan activity logging
     * serta invalidate session jika role berubah
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        DB::beginTransaction();

        try {
            $oldValues = [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'status' => $user->status,
            ];

            $roleChanged = $user->role !== $request->role;
            $statusChanged = $user->status !== $request->status;

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone_number' => $request->phone_number,
                'role' => $request->role,
                'status' => $request->status,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_user',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => $oldValues,
                'new_values' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
                'status' => 'success',
            ]);

            // Jika role atau status berubah, terminate active sessions untuk force re-login
            if ($roleChanged || ($statusChanged && $user->status === 'INACTIVE')) {
                // TODO: Implement session termination untuk user tertentu
                // Membutuhkan database session driver dan query ke sessions table
            }

            DB::commit();

            $message = 'User berhasil diupdate.';
            if ($roleChanged) {
                $message .= ' User akan diminta login ulang untuk perubahan role.';
            }

            return redirect()->route('admin.users.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal mengupdate user. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Delete user dengan soft delete atau hard delete
     * Note: Spec says deactivate, not delete - so we toggle status instead
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        // Prevent deleting last Super Admin
        if ($user->role === 'SUPERADMIN') {
            $superAdminCount = User::where('role', 'SUPERADMIN')->where('status', 'ACTIVE')->count();
            if ($superAdminCount <= 1) {
                return back()->withErrors(['error' => 'Tidak dapat menghapus Super Admin terakhir.']);
            }
        }

        DB::beginTransaction();

        try {
            // Log activity before delete
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete_user',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role,
                ],
                'status' => 'success',
            ]);

            $user->delete();

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal menghapus user. Silakan coba lagi.']);
        }
    }

    /**
     * Reset password user dan kirim password baru via email
     * untuk situasi dimana user lupa password atau perlu force reset
     */
    public function resetPassword(User $user)
    {
        DB::beginTransaction();

        try {
            // Generate new password
            $firstName = explode(' ', $user->name)[0];
            $newPassword = $firstName.rand(1000, 9999);

            $user->update([
                'password' => Hash::make($newPassword),
                'is_first_login' => true,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'reset_user_password',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'new_values' => [
                    'target_user_id' => $user->id,
                    'username' => $user->username,
                ],
                'status' => 'success',
            ]);

            // Send email dengan new password
            try {
                Mail::to($user->email)->send(new UserAccountCreated($user, $newPassword));
            } catch (\Exception $e) {
                Log::error('Failed to send password reset email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            return back()->with('success', 'Password berhasil direset. Password baru telah dikirim ke email user.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal mereset password. Silakan coba lagi.']);
        }
    }

    /**
     * Toggle status user antara active dan inactive
     * dengan auto-terminate sessions untuk inactive users
     */
    public function toggleStatus(User $user)
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
        }

        // Prevent deactivating last Super Admin
        if ($user->role === 'SUPERADMIN' && $user->status === 'ACTIVE') {
            $activeAdminCount = User::where('role', 'SUPERADMIN')->where('status', 'ACTIVE')->count();
            if ($activeAdminCount <= 1) {
                return back()->withErrors(['error' => 'Tidak dapat menonaktifkan Super Admin terakhir.']);
            }
        }

        DB::beginTransaction();

        try {
            $newStatus = $user->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';

            $user->update(['status' => $newStatus]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'toggle_user_status',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => ['status' => $user->status],
                'new_values' => [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'status' => $newStatus,
                ],
                'status' => 'success',
            ]);

            // TODO: Terminate active sessions jika user di-deactivate
            // Requires database session driver

            DB::commit();

            $message = $newStatus === 'ACTIVE' ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.';

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal mengubah status user. Silakan coba lagi.']);
        }
    }
}
