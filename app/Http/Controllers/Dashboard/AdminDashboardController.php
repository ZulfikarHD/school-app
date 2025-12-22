<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    /**
     * Menampilkan dashboard untuk Admin/TU dengan akses ke
     * Student Management, Payment Management, PSB, dan User Management
     */
    public function index()
    {
        return Inertia::render('Dashboard/AdminDashboard', [
            'stats' => [
                'total_students' => 0,
                'total_payments' => 0,
                'pending_psb' => 0,
                'total_users' => 0,
            ],
        ]);
    }
}
