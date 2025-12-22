<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class TeacherDashboardController extends Controller
{
    /**
     * Menampilkan dashboard untuk Guru dengan akses ke
     * class management, attendance, grades, dan schedule
     */
    public function index()
    {
        return Inertia::render('Dashboard/TeacherDashboard', [
            'stats' => [
                'my_classes' => 0,
                'total_students' => 0,
                'pending_grades' => 0,
                'today_schedule' => [],
            ],
        ]);
    }
}
