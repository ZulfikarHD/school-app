<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk Principal dalam memonitor data attendance
 * dengan akses read-only ke rekap absensi siswa dan guru
 * tanpa kemampuan untuk edit atau delete data
 */
class AttendanceController extends Controller
{
    /**
     * Constructor untuk inject AttendanceService dependency
     */
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Display attendance dashboard dengan real-time stats
     * dan overview status kelas yang sudah/belum input absensi
     */
    public function dashboard(Request $request): Response
    {
        $today = Carbon::today();
        $date = $request->input('date', $today->format('Y-m-d'));

        // Get today's summary
        $summary = $this->attendanceService->getTodayAttendanceSummary();

        // Get classes without attendance
        $classesWithoutAttendance = $this->attendanceService->getClassesWithoutAttendance($date);

        // Get all active classes with attendance status
        $allClasses = SchoolClass::where('is_active', true)
            ->with('waliKelas')
            ->get()
            ->map(function ($class) use ($date) {
                $class->nama_lengkap = "Kelas {$class->tingkat}{$class->nama}";
                $class->jumlah_siswa = Student::where('kelas_id', $class->id)
                    ->where('status', 'aktif')
                    ->count();

                // Check if attendance recorded
                $hasAttendance = StudentAttendance::where('class_id', $class->id)
                    ->whereDate('tanggal', $date)
                    ->exists();

                $class->has_attendance = $hasAttendance;

                // Get attendance summary if recorded
                if ($hasAttendance) {
                    $class->attendance_summary = $this->attendanceService->getClassAttendanceSummary(
                        $class->id,
                        $date
                    );
                }

                return $class;
            })
            ->sortBy('tingkat')
            ->sortBy('nama')
            ->values();

        // Get teacher presence
        $totalTeachers = User::where('role', 'TEACHER')->where('is_active', true)->count();
        $teacherAttendances = TeacherAttendance::whereDate('tanggal', $date)->get();
        $clockedInCount = $teacherAttendances->count();
        $lateCount = $teacherAttendances->where('is_late', true)->count();

        return Inertia::render('Principal/Attendance/Dashboard', [
            'title' => 'Dashboard Kehadiran',
            'summary' => $summary,
            'classes' => $allClasses,
            'classesWithoutAttendance' => $classesWithoutAttendance,
            'teacherPresence' => [
                'total' => $totalTeachers,
                'clocked_in' => $clockedInCount,
                'late' => $lateCount,
                'absent' => $totalTeachers - $clockedInCount,
            ],
            'filters' => [
                'date' => $date,
            ],
        ]);
    }

    /**
     * Display rekap attendance siswa dengan filter kelas, tanggal, status
     * untuk monitoring oleh principal (read-only)
     */
    public function studentsIndex(Request $request): Response
    {
        $query = StudentAttendance::with(['student', 'class', 'recordedBy']);

        // Filter berdasarkan kelas
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        // Filter berdasarkan tanggal
        if ($request->filled('date')) {
            $query->whereDate('tanggal', $request->input('date'));
        } else {
            // Default: tampilkan data hari ini
            $query->whereDate('tanggal', Carbon::today());
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $attendances = $query->latest('tanggal')->paginate(50);

        $classes = SchoolClass::where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get()
            ->map(function ($class) {
                $class->nama_lengkap = "Kelas {$class->tingkat}{$class->nama}";

                return $class;
            });

        // Calculate summary untuk tanggal yang dipilih
        $summaryDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        $summaryQuery = StudentAttendance::whereDate('tanggal', $summaryDate);

        if ($request->filled('class_id')) {
            $summaryQuery->where('class_id', $request->input('class_id'));
        }

        $summaryAttendances = $summaryQuery->get();
        $summary = [
            'total' => $summaryAttendances->count(),
            'hadir' => $summaryAttendances->where('status', 'H')->count(),
            'izin' => $summaryAttendances->where('status', 'I')->count(),
            'sakit' => $summaryAttendances->where('status', 'S')->count(),
            'alpha' => $summaryAttendances->where('status', 'A')->count(),
        ];

        return Inertia::render('Principal/Attendance/Students/Index', [
            'title' => 'Rekap Kehadiran Siswa',
            'attendances' => $attendances,
            'classes' => $classes,
            'summary' => $summary,
            'filters' => $request->only(['class_id', 'date', 'status']),
        ]);
    }

    /**
     * Generate comprehensive attendance report untuk siswa
     * dengan statistics dan visualisasi untuk analisis principal
     */
    public function studentReport(Request $request): Response
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'class_id' => 'nullable|exists:classes,id',
            'status' => 'nullable|in:H,I,S,A',
            'student_id' => 'nullable|exists:students,id',
        ]);

        // Default date range: bulan ini
        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $validated['end_date'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $filters = array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'class_id' => $validated['class_id'] ?? null,
            'status' => $validated['status'] ?? null,
            'student_id' => $validated['student_id'] ?? null,
        ]);

        $attendances = $this->attendanceService->getAttendanceReport($filters);

        // Calculate statistics
        $statistics = [
            'total_records' => $attendances->count(),
            'hadir' => $attendances->where('status', 'H')->count(),
            'izin' => $attendances->where('status', 'I')->count(),
            'sakit' => $attendances->where('status', 'S')->count(),
            'alpha' => $attendances->where('status', 'A')->count(),
        ];

        // Calculate percentage
        if ($statistics['total_records'] > 0) {
            $statistics['persentase_hadir'] = round(
                ($statistics['hadir'] / $statistics['total_records']) * 100,
                2
            );
            $statistics['persentase_kehadiran_valid'] = round(
                (($statistics['hadir'] + $statistics['izin'] + $statistics['sakit']) / $statistics['total_records']) * 100,
                2
            );
        } else {
            $statistics['persentase_hadir'] = 0;
            $statistics['persentase_kehadiran_valid'] = 0;
        }

        // Get trend data untuk chart jika ada class filter
        $trendData = [];
        if (isset($filters['class_id'])) {
            $trendData = $this->attendanceService->getClassAttendanceTrend(
                $filters['class_id'],
                $startDate,
                $endDate
            );
        }

        // Get classes untuk filter dropdown
        $classes = SchoolClass::where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get()
            ->map(function ($class) {
                $class->nama_lengkap = "Kelas {$class->tingkat}{$class->nama}";

                return $class;
            });

        // Get low attendance students (< 80%)
        $lowAttendanceStudents = $this->getLowAttendanceStudents($startDate, $endDate, $filters['class_id'] ?? null);

        return Inertia::render('Principal/Attendance/Students/Report', [
            'title' => 'Laporan Kehadiran Siswa',
            'attendances' => $attendances,
            'statistics' => $statistics,
            'trendData' => $trendData,
            'classes' => $classes,
            'lowAttendanceStudents' => $lowAttendanceStudents,
            'filters' => $filters,
        ]);
    }

    /**
     * Display rekap presensi guru dengan filter tanggal dan status
     * untuk monitoring kehadiran dan keterlambatan
     */
    public function teachersIndex(Request $request): Response
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $query = TeacherAttendance::with('teacher');

        // Filter berdasarkan tanggal
        if ($request->filled('date')) {
            $query->whereDate('tanggal', $request->input('date'));
        } else {
            $query->whereDate('tanggal', $date);
        }

        // Filter berdasarkan keterlambatan
        if ($request->filled('is_late')) {
            $query->where('is_late', $request->boolean('is_late'));
        }

        $attendances = $query->latest('clock_in')->paginate(50);

        // Get all teachers untuk menampilkan yang belum clock in
        $allTeachers = User::where('role', 'TEACHER')
            ->where('is_active', true)
            ->get();

        $clockedInTeacherIds = TeacherAttendance::whereDate('tanggal', $date)
            ->pluck('teacher_id');

        $absentTeachers = $allTeachers->whereNotIn('id', $clockedInTeacherIds);

        // Calculate summary
        $dayAttendances = TeacherAttendance::whereDate('tanggal', $date)->get();
        $summary = [
            'total_guru' => $allTeachers->count(),
            'hadir' => $dayAttendances->count(),
            'terlambat' => $dayAttendances->where('is_late', true)->count(),
            'belum_hadir' => $allTeachers->count() - $dayAttendances->count(),
        ];

        return Inertia::render('Principal/Attendance/Teachers/Index', [
            'title' => 'Rekap Presensi Guru',
            'attendances' => $attendances,
            'absentTeachers' => $absentTeachers->values(),
            'summary' => $summary,
            'filters' => [
                'date' => $date,
                'is_late' => $request->input('is_late'),
            ],
        ]);
    }

    /**
     * Generate comprehensive teacher attendance report
     * untuk analisis kehadiran dan perhitungan jam kerja
     */
    public function teacherReport(Request $request): Response
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        // Default date range: bulan ini
        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $validated['end_date'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $filters = array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'teacher_id' => $validated['teacher_id'] ?? null,
        ]);

        $attendances = $this->attendanceService->getTeacherAttendanceReport($filters);

        // Get all teachers untuk filter dropdown
        $teachers = User::where('role', 'TEACHER')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $statistics = [
            'total_records' => $attendances->count(),
            'total_present' => $attendances->whereNotNull('clock_in')->count(),
            'total_late' => $attendances->where('is_late', true)->count(),
            'average_hours' => 0,
            'total_hours' => 0,
        ];

        // Calculate work hours
        $totalHours = 0;
        $countWithClockOut = 0;
        foreach ($attendances as $attendance) {
            if ($attendance->clock_in && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = Carbon::parse($attendance->clock_out);
                $totalHours += $clockOut->diffInHours($clockIn, true);
                $countWithClockOut++;
            }
        }

        $statistics['total_hours'] = round($totalHours, 2);
        $statistics['average_hours'] = $countWithClockOut > 0
            ? round($totalHours / $countWithClockOut, 2)
            : 0;

        // Calculate attendance percentage
        $workDays = $this->countWorkDays($startDate, $endDate);
        $statistics['work_days'] = $workDays;
        $statistics['attendance_percentage'] = $workDays > 0 && isset($filters['teacher_id'])
            ? round(($statistics['total_present'] / $workDays) * 100, 2)
            : 0;

        return Inertia::render('Principal/Attendance/Teachers/Report', [
            'title' => 'Laporan Presensi Guru',
            'attendances' => $attendances,
            'statistics' => $statistics,
            'teachers' => $teachers,
            'filters' => $filters,
        ]);
    }

    /**
     * Get students dengan kehadiran di bawah 80%
     * untuk early warning system
     */
    private function getLowAttendanceStudents(string $startDate, string $endDate, ?int $classId = null): array
    {
        $query = Student::where('status', 'aktif');

        if ($classId) {
            $query->where('kelas_id', $classId);
        }

        $students = $query->get();
        $lowAttendanceStudents = [];

        foreach ($students as $student) {
            $stats = $this->attendanceService->calculateAttendanceStatistics(
                $student->id,
                $startDate,
                $endDate
            );

            // Calculate valid attendance percentage (hadir + izin + sakit)
            if ($stats['total'] > 0) {
                $validAttendance = $stats['hadir'] + $stats['izin'] + $stats['sakit'];
                $percentage = round(($validAttendance / $stats['total']) * 100, 2);

                if ($percentage < 80) {
                    $lowAttendanceStudents[] = [
                        'id' => $student->id,
                        'nis' => $student->nis,
                        'nama' => $student->nama_lengkap,
                        'kelas' => $student->kelas ? "Kelas {$student->kelas->tingkat}{$student->kelas->nama}" : '-',
                        'persentase' => $percentage,
                        'total_hadir' => $stats['hadir'],
                        'total_alpha' => $stats['alpha'],
                        'total_hari' => $stats['total'],
                    ];
                }
            }
        }

        // Sort by percentage ascending (lowest first)
        usort($lowAttendanceStudents, function ($a, $b) {
            return $a['persentase'] <=> $b['persentase'];
        });

        return array_slice($lowAttendanceStudents, 0, 20); // Limit to top 20
    }

    /**
     * Calculate jumlah hari kerja dalam range tanggal
     * dengan exclude weekend
     */
    private function countWorkDays(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workDays = 0;

        while ($start->lte($end)) {
            if (! $start->isWeekend()) {
                $workDays++;
            }
            $start->addDay();
        }

        return $workDays;
    }
}
