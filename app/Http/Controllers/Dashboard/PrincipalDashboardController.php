<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class PrincipalDashboardController extends Controller
{
    /**
     * Menampilkan dashboard untuk Kepala Sekolah dengan akses ke
     * reports, analytics, dan monitoring keseluruhan sistem sekolah
     */
    public function index()
    {
        return Inertia::render('Dashboard/PrincipalDashboard', [
            'stats' => [
                'total_students' => 0,
                'total_teachers' => 0,
                'total_classes' => 0,
                'attendance_rate' => 0,
            ],
        ]);
    }
}
