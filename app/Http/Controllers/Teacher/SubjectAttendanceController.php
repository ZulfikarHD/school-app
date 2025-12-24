<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectAttendanceRequest;
use App\Services\AttendanceService;
use Inertia\Inertia;
use Inertia\Response;

class SubjectAttendanceController extends Controller
{
    /**
     * Constructor untuk inject AttendanceService dependency
     */
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Show form untuk input attendance per mata pelajaran
     * dengan list siswa dan subject schedule teacher
     *
     * TODO Sprint 2: Implement UI dengan form per subject
     */
    public function create(): Response
    {
        $schedule = $this->attendanceService->getTeacherSchedule(
            auth()->user(),
            today()->format('Y-m-d')
        );

        return Inertia::render('Teacher/SubjectAttendance/Create', [
            'title' => 'Input Presensi Per Mata Pelajaran',
            'schedule' => $schedule,
        ]);
    }

    /**
     * Store subject attendance untuk satu sesi kelas
     * dengan validasi teacher harus mengajar subject tersebut
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreSubjectAttendanceRequest $request)
    {
        try {
            $this->attendanceService->recordSubjectAttendance(
                $request->validated(),
                $request->user()
            );

            return back()->with('success', 'Berhasil menyimpan presensi mata pelajaran.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan presensi: '.$e->getMessage());
        }
    }
}
