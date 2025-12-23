<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Display user profile page dengan informasi user dan option
     * untuk change password
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Profile/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'status' => $user->status,
                'last_login_at' => $user->last_login_at?->format('d M Y H:i'),
                'created_at' => $user->created_at->format('d M Y'),
            ],
        ]);
    }
}
