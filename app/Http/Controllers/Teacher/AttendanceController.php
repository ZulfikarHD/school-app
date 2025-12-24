<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentAttendanceRequest;
use App\Services\AttendanceService;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    /**
     * Constructor untuk inject AttendanceService dependency
     * yang digunakan untuk handle business logic attendance
     */
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Display list attendance records untuk teacher
     * dengan filter berdasarkan kelas dan tanggal
     *
     * TODO Sprint 2: Implement UI dengan table attendance history
     */
    public function index(): Response
    {
        return Inertia::render('Teacher/Attendance/Index', [
            'title' => 'Daftar Presensi Siswa',
        ]);
    }

    /**
     * Show form untuk input attendance harian
     * dengan list siswa dalam kelas yang dipilih
     *
     * TODO Sprint 2: Implement UI dengan form input per student
     */
    public function create(): Response
    {
        return Inertia::render('Teacher/Attendance/Create', [
            'title' => 'Input Presensi Harian',
        ]);
    }

    /**
     * Store attendance harian untuk multiple siswa
     * dengan validasi dan sync ke database via service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreStudentAttendanceRequest $request)
    {
        try {
            $attendances = $this->attendanceService->recordDailyAttendance(
                $request->validated(),
                $request->user()
            );

            return redirect()
                ->route('teacher.attendance.index')
                ->with('success', "Berhasil menyimpan presensi untuk {$attendances->count()} siswa.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan presensi: '.$e->getMessage());
        }
    }
}
