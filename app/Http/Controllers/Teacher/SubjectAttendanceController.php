<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectAttendanceRequest;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectAttendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
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

    /**
     * Display history of subject attendance records
     * dengan filter subject, class, dan tanggal
     */
    public function index(Request $request): Response
    {
        $query = SubjectAttendance::with(['student', 'subject', 'class'])
            ->where('teacher_id', auth()->id());

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('tanggal', $request->input('date'));
        }

        $attendances = $query->latest('tanggal')
            ->latest('jam_ke')
            ->paginate(50);

        // Get teacher's subjects and classes for filters
        $teacherClasses = $this->attendanceService->getTeacherClasses(auth()->user());
        $schedule = $this->attendanceService->getTeacherSchedule(auth()->user(), today()->format('Y-m-d'));

        $subjects = $schedule->unique('subject_id')->map(function ($item) {
            return [
                'id' => $item->subject_id,
                'nama_mapel' => $item->nama_mapel,
            ];
        })->values();

        return Inertia::render('Teacher/SubjectAttendance/Index', [
            'title' => 'Riwayat Presensi Per Mata Pelajaran',
            'attendances' => $attendances,
            'subjects' => $subjects,
            'classes' => $teacherClasses,
            'filters' => $request->only(['subject_id', 'class_id', 'date']),
        ]);
    }
}
