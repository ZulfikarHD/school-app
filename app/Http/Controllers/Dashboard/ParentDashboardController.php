<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ParentDashboardController extends Controller
{
    /**
     * Menampilkan dashboard untuk Orang Tua dengan akses ke
     * child information, payments, grades, dan attendance
     */
    public function index()
    {
        return Inertia::render('Dashboard/ParentDashboard', [
            'stats' => [
                'children' => [],
                'pending_payments' => 0,
                'recent_grades' => [],
                'attendance_summary' => [],
            ],
        ]);
    }
}
