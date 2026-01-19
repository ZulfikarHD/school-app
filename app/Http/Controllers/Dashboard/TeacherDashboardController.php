<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeacherDashboardController extends Controller
{
    /**
     * Menampilkan dashboard untuk Guru dengan akses ke
     * class management, attendance, grades, dan schedule
     *
     * Sprint C Enhancement:
     * - Added pending leave requests count untuk siswa di kelas yang diampu
     * - Added my_classes count berdasarkan wali kelas assignment
     * - Added total_students count berdasarkan kelas yang diampu
     */
    public function index(Request $request)
    {
        $teacher = $request->user();

        // Get kelas yang diampu oleh teacher ini sebagai wali kelas
        $myClasses = SchoolClass::where('wali_kelas_id', $teacher->id)->get();
        $myClassIds = $myClasses->pluck('id');

        // Get total siswa di kelas yang diampu
        $totalStudents = Student::whereIn('kelas_id', $myClassIds)
            ->where('status', 'aktif')
            ->count();

        // Get pending leave requests count untuk siswa di kelas yang diampu
        $pendingLeaveRequests = LeaveRequest::whereHas('student.kelas', function ($query) use ($teacher) {
            $query->where('wali_kelas_id', $teacher->id);
        })
            ->pending()
            ->count();

        return Inertia::render('Dashboard/TeacherDashboard', [
            'stats' => [
                'my_classes' => $myClasses->count(),
                'total_students' => $totalStudents,
                'pending_grades' => 0,
                'today_schedule' => [],
            ],
            'pendingLeaveRequests' => $pendingLeaveRequests,
        ]);
    }
}
