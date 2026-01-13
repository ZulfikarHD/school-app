<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentAttendanceRequest;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
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
     */
    public function index(): Response
    {
        $teacher = auth()->user();

        // Get teacher's classes (wali kelas + kelas yang diajar)
        $classes = $this->attendanceService->getTeacherClasses($teacher);

        // Get filters from request
        $kelasId = request('kelas_id');
        $startDate = request('start_date');
        $endDate = request('end_date');
        $search = request('search');

        // Query attendance records grouped by date and class
        $query = \DB::table('student_attendances')
            ->select(
                \DB::raw('CONCAT(student_attendances.tanggal, "-", student_attendances.class_id) as id'),
                'student_attendances.tanggal',
                'student_attendances.class_id',
                \DB::raw('MAX(student_attendances.recorded_at) as recorded_at'),
                \DB::raw('COUNT(DISTINCT student_attendances.student_id) as total_siswa'),
                \DB::raw('SUM(CASE WHEN student_attendances.status = "H" THEN 1 ELSE 0 END) as hadir'),
                \DB::raw('SUM(CASE WHEN student_attendances.status = "I" THEN 1 ELSE 0 END) as izin'),
                \DB::raw('SUM(CASE WHEN student_attendances.status = "S" THEN 1 ELSE 0 END) as sakit'),
                \DB::raw('SUM(CASE WHEN student_attendances.status = "A" THEN 1 ELSE 0 END) as alpha')
            )
            ->where('student_attendances.recorded_by', $teacher->id);

        // Apply search filter untuk student NIS atau nama
        if ($search) {
            $query->join('students', 'student_attendances.student_id', '=', 'students.id')
                ->where(function ($q) use ($search) {
                    $q->where('students.nis', 'like', "%{$search}%")
                        ->orWhere('students.nama_lengkap', 'like', "%{$search}%");
                });
        }

        $query->groupBy('student_attendances.tanggal', 'student_attendances.class_id')
            ->orderBy('student_attendances.tanggal', 'desc');

        // Apply filters
        if ($kelasId) {
            $query->where('student_attendances.class_id', $kelasId);
        }
        if ($startDate) {
            $query->where('student_attendances.tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('student_attendances.tanggal', '<=', $endDate);
        }

        $attendances = $query->paginate(20);

        // Add kelas_nama and can_edit flag to each record
        $attendances->getCollection()->transform(function ($record) use ($classes) {
            // Convert stdClass to array for easier manipulation
            $recordArray = (array) $record;

            $kelas = collect($classes)->firstWhere('id', $recordArray['class_id']);
            $recordArray['kelas_nama'] = $kelas ? $kelas['nama_lengkap'] : 'Unknown';

            // Can edit if within 7 days
            $daysDiff = now()->diffInDays($recordArray['tanggal'], false);
            $recordArray['can_edit'] = $daysDiff >= -7 && $daysDiff <= 0;

            return (object) $recordArray;
        });

        // Calculate summary statistics
        $summaryQuery = \DB::table('student_attendances')
            ->select(
                \DB::raw('COUNT(DISTINCT CONCAT(student_attendances.tanggal, "-", student_attendances.class_id)) as total_records'),
                \DB::raw('SUM(CASE WHEN student_attendances.status = "H" THEN 1 ELSE 0 END) as total_hadir'),
                \DB::raw('SUM(CASE WHEN student_attendances.status = "I" THEN 1 ELSE 0 END) as total_izin'),
                \DB::raw('SUM(CASE WHEN student_attendances.status = "S" THEN 1 ELSE 0 END) as total_sakit'),
                \DB::raw('SUM(CASE WHEN student_attendances.status = "A" THEN 1 ELSE 0 END) as total_alpha'),
                \DB::raw('COUNT(*) as total_students')
            )
            ->where('student_attendances.recorded_by', $teacher->id);

        // Apply search filter untuk summary juga
        if ($search) {
            $summaryQuery->join('students', 'student_attendances.student_id', '=', 'students.id')
                ->where(function ($q) use ($search) {
                    $q->where('students.nis', 'like', "%{$search}%")
                        ->orWhere('students.nama_lengkap', 'like', "%{$search}%");
                });
        }

        if ($kelasId) {
            $summaryQuery->where('student_attendances.class_id', $kelasId);
        }
        if ($startDate) {
            $summaryQuery->where('student_attendances.tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $summaryQuery->where('student_attendances.tanggal', '<=', $endDate);
        }

        $summary = $summaryQuery->first();

        $attendanceRate = $summary->total_students > 0
            ? number_format(($summary->total_hadir / $summary->total_students) * 100, 1)
            : '0';

        return Inertia::render('Teacher/Attendance/Index', [
            'title' => 'Daftar Presensi Siswa',
            'classes' => $classes,
            'attendances' => $attendances,
            'filters' => [
                'kelas_id' => $kelasId ? (int) $kelasId : null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'search' => $search,
            ],
            'summary' => [
                'total_records' => $summary->total_records,
                'total_hadir' => $summary->total_hadir,
                'total_izin' => $summary->total_izin,
                'total_sakit' => $summary->total_sakit,
                'total_alpha' => $summary->total_alpha,
                'attendance_rate' => $attendanceRate,
            ],
        ]);
    }

    /**
     * API endpoint to check existing attendance for a class and date
     */
    public function checkExisting(Request $request)
    {
        $kelasId = $request->input('class_id');
        $tanggal = $request->input('tanggal');

        \Log::info('Checking existing attendance', ['class_id' => $kelasId, 'tanggal' => $tanggal]);

        if (! $kelasId || ! $tanggal) {
            return response()->json(['data' => []]);
        }

        $existingAttendance = \App\Models\StudentAttendance::where('class_id', $kelasId)
            ->whereDate('tanggal', $tanggal)
            ->with('student:id,nama_lengkap,nis')
            ->get()
            ->map(function ($att) {
                return [
                    'id' => $att->id,
                    'student_id' => $att->student_id,
                    'student_nama' => $att->student->nama_lengkap ?? '',
                    'student_nis' => $att->student->nis ?? '',
                    'status' => $att->status,
                    'keterangan' => $att->keterangan,
                ];
            });

        \Log::info('Found existing attendance', ['count' => $existingAttendance->count()]);

        return response()->json(['data' => $existingAttendance]);
    }

    /**
     * Show form untuk input attendance harian
     * dengan list siswa dalam kelas yang dipilih
     * Jika ada query params kelas_id dan tanggal, load existing attendance untuk edit mode
     */
    public function create(): Response
    {
        $teacher = auth()->user();

        // Get teacher's classes untuk dropdown selection
        $classes = $this->attendanceService->getTeacherClasses($teacher);

        // Check if this is edit mode (has kelas_id dan tanggal params)
        $kelasId = request('kelas_id');
        $tanggal = request('tanggal');
        $existingAttendance = null;

        if ($kelasId && $tanggal) {
            // Load existing attendance for this class and date
            $existingAttendance = \App\Models\StudentAttendance::where('class_id', $kelasId)
                ->where('tanggal', $tanggal)
                ->where('recorded_by', $teacher->id)
                ->with('student:id,nama_lengkap,nis')
                ->get()
                ->map(function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'student_id' => $attendance->student_id,
                        'student_nama' => $attendance->student->nama_lengkap,
                        'student_nis' => $attendance->student->nis,
                        'status' => $attendance->status,
                        'keterangan' => $attendance->keterangan,
                    ];
                });
        }

        return Inertia::render('Teacher/Attendance/Create', [
            'title' => $existingAttendance ? 'Edit Presensi Harian' : 'Input Presensi Harian',
            'classes' => $classes,
            'existingAttendance' => $existingAttendance,
            'editMode' => [
                'kelas_id' => $kelasId ? (int) $kelasId : null,
                'tanggal' => $tanggal,
            ],
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
