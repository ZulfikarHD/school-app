<?php

namespace App\Services;

use App\Jobs\SendAlphaNotification;
use App\Models\LeaveRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\SubjectAttendance;
use App\Models\TeacherAttendance;
use App\Models\TeacherLeave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    // ==================== STUDENT DAILY ATTENDANCE ====================

    /**
     * Record attendance harian untuk multiple siswa dalam satu kelas
     * dengan validasi untuk mencegah duplicate dan unauthorized access
     *
     * @param  array{class_id: int, tanggal: string, attendances: array<array{student_id: int, status: string, keterangan: ?string}>}  $data
     * @return Collection<int, StudentAttendance>
     */
    public function recordDailyAttendance(array $data, User $teacher): Collection
    {
        // Validasi authorization
        if (! $this->canRecordAttendance($teacher, $data['class_id'], $data['tanggal'])) {
            throw new \Exception('Anda tidak memiliki akses untuk input attendance kelas ini.');
        }

        $attendances = collect();

        DB::transaction(function () use ($data, $teacher, &$attendances) {
            foreach ($data['attendances'] as $item) {
                // Use updateOrCreate to handle both create and update
                $attendance = StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $item['student_id'],
                        'tanggal' => $data['tanggal'],
                    ],
                    [
                        'class_id' => $data['class_id'],
                        'status' => $item['status'],
                        'keterangan' => $item['keterangan'] ?? null,
                        'recorded_by' => $teacher->id,
                        'recorded_at' => now(),
                    ]
                );

                $attendances->push($attendance);

                // Dispatch notification job jika status Alpha
                if ($item['status'] === 'A') {
                    SendAlphaNotification::dispatch($attendance);
                }
            }
        });

        return $attendances;
    }

    /**
     * Update attendance record yang sudah ada
     * dengan validasi untuk memastikan hanya yang berhak bisa update
     *
     * @param  array{status: string, keterangan: ?string}  $data
     */
    public function updateAttendance(StudentAttendance $attendance, array $data): StudentAttendance
    {
        $attendance->update([
            'status' => $data['status'],
            'keterangan' => $data['keterangan'] ?? $attendance->keterangan,
            'recorded_at' => now(),
        ]);

        return $attendance->fresh();
    }

    /**
     * Check apakah teacher bisa record attendance untuk kelas tertentu
     * dimana teacher harus menjadi wali kelas atau mengajar di kelas tersebut
     */
    public function canRecordAttendance(User $teacher, int $classId, string $date): bool
    {
        $class = SchoolClass::find($classId);

        if (! $class) {
            return false;
        }

        // Wali kelas bisa input
        if ($class->wali_kelas_id === $teacher->id) {
            return true;
        }

        // Guru yang mengajar di kelas ini bisa input
        $teachesInClass = DB::table('teacher_subjects')
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->exists();

        return $teachesInClass;
    }

    /**
     * Check apakah attendance untuk student dan tanggal sudah ada
     * untuk mencegah duplicate entry
     */
    public function isDuplicateAttendance(int $studentId, string $date): bool
    {
        return StudentAttendance::where('student_id', $studentId)
            ->whereDate('tanggal', $date)
            ->exists();
    }

    // ==================== SUBJECT ATTENDANCE ====================

    /**
     * Record attendance per mata pelajaran untuk satu sesi kelas
     * dengan validasi teacher harus mengajar subject tersebut
     *
     * @param  array{class_id: int, subject_id: int, tanggal: string, jam_ke: int, attendances: array<array{student_id: int, status: string, keterangan: ?string}>}  $data
     */
    public function recordSubjectAttendance(array $data, User $teacher): SubjectAttendance
    {
        // Validasi teacher mengajar subject di kelas ini
        $canTeach = DB::table('teacher_subjects')
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $data['subject_id'])
            ->where('class_id', $data['class_id'])
            ->exists();

        if (! $canTeach) {
            throw new \Exception('Anda tidak mengajar mata pelajaran ini di kelas tersebut.');
        }

        $attendances = collect();

        DB::transaction(function () use ($data, $teacher, &$attendances) {
            foreach ($data['attendances'] as $item) {
                // Check duplicate subject attendance
                $exists = SubjectAttendance::where('student_id', $item['student_id'])
                    ->where('subject_id', $data['subject_id'])
                    ->whereDate('tanggal', $data['tanggal'])
                    ->where('jam_ke', $data['jam_ke'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $attendance = SubjectAttendance::create([
                    'student_id' => $item['student_id'],
                    'class_id' => $data['class_id'],
                    'subject_id' => $data['subject_id'],
                    'teacher_id' => $teacher->id,
                    'tanggal' => $data['tanggal'],
                    'jam_ke' => $data['jam_ke'],
                    'status' => $item['status'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);

                $attendances->push($attendance);
            }
        });

        return $attendances->first();
    }

    /**
     * Get jadwal mengajar teacher untuk tanggal tertentu
     * yang mencakup mata pelajaran dan kelas
     *
     * @return Collection<int, \stdClass>
     */
    public function getTeacherSchedule(User $teacher, string $date): Collection
    {
        return DB::table('teacher_subjects')
            ->join('subjects', 'teacher_subjects.subject_id', '=', 'subjects.id')
            ->join('classes', 'teacher_subjects.class_id', '=', 'classes.id')
            ->where('teacher_subjects.teacher_id', $teacher->id)
            ->select(
                'subjects.id as subject_id',
                'subjects.nama_mapel',
                'classes.id as class_id',
                DB::raw('CONCAT(classes.tingkat, classes.nama) as nama_kelas')
            )
            ->get();
    }

    // ==================== LEAVE MANAGEMENT ====================

    /**
     * Submit permohonan izin/sakit dari parent untuk anaknya
     * dengan upload attachment jika ada
     *
     * @param  array{student_id: int, jenis: string, tanggal_mulai: string, tanggal_selesai: string, alasan: string, attachment_path: ?string}  $data
     */
    public function submitLeaveRequest(array $data, User $parent): LeaveRequest
    {
        // Validasi parent hanya bisa submit untuk anaknya
        $isParent = DB::table('student_guardian')
            ->join('guardians', 'student_guardian.guardian_id', '=', 'guardians.id')
            ->where('guardians.user_id', $parent->id)
            ->where('student_guardian.student_id', $data['student_id'])
            ->exists();

        if (! $isParent) {
            throw new \Exception('Anda tidak dapat mengajukan izin untuk siswa ini.');
        }

        return LeaveRequest::create([
            'student_id' => $data['student_id'],
            'jenis' => $data['jenis'],
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'alasan' => $data['alasan'],
            'attachment_path' => $data['attachment_path'] ?? null,
            'submitted_by' => $parent->id,
            'status' => 'PENDING',
        ]);
    }

    /**
     * Approve leave request dan auto-sync ke student_attendances
     * dengan membuat attendance records untuk date range
     */
    public function approveLeaveRequest(LeaveRequest $request, User $reviewer): void
    {
        DB::transaction(function () use ($request, $reviewer) {
            $request->approve($reviewer);
            $this->syncLeaveToAttendance($request);
        });
    }

    /**
     * Reject leave request dengan alasan tertentu
     * tanpa membuat attendance records
     */
    public function rejectLeaveRequest(LeaveRequest $request, User $reviewer, string $reason): void
    {
        $request->reject($reviewer, $reason);
    }

    /**
     * Sync approved leave ke student_attendances table
     * dengan membuat attendance records untuk setiap tanggal dalam range
     * menggunakan status I (Izin) atau S (Sakit)
     */
    public function syncLeaveToAttendance(LeaveRequest $request): void
    {
        $student = $request->student;
        $startDate = Carbon::parse($request->tanggal_mulai);
        $endDate = Carbon::parse($request->tanggal_selesai);

        $status = $request->jenis === 'IZIN' ? 'I' : 'S';

        $dates = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Skip weekend (Saturday & Sunday)
            if (! $currentDate->isWeekend()) {
                $dates[] = $currentDate->format('Y-m-d');
            }
            $currentDate->addDay();
        }

        foreach ($dates as $date) {
            // Create or update attendance
            StudentAttendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'tanggal' => $date,
                ],
                [
                    'class_id' => $student->kelas_id,
                    'status' => $status,
                    'keterangan' => 'Auto-generated from leave request: '.$request->alasan,
                    'recorded_by' => $request->reviewed_by,
                    'recorded_at' => now(),
                ]
            );
        }
    }

    // ==================== TEACHER ATTENDANCE ====================

    /**
     * Clock in guru dengan GPS coordinates
     * dan auto-detect lateness berdasarkan jam masuk sekolah
     */
    public function clockIn(User $teacher, float $lat, float $lng): TeacherAttendance
    {
        $today = Carbon::today()->format('Y-m-d');

        if ($this->isAlreadyClockedIn($teacher, $today)) {
            throw new \Exception('Anda sudah clock in hari ini.');
        }

        $attendance = TeacherAttendance::create([
            'teacher_id' => $teacher->id,
            'tanggal' => $today,
        ]);

        $attendance->clockIn($lat, $lng);

        return $attendance->fresh();
    }

    /**
     * Clock out guru dengan GPS coordinates
     * untuk mencatat waktu pulang dan calculate duration
     */
    public function clockOut(User $teacher, float $lat, float $lng): TeacherAttendance
    {
        $today = Carbon::today()->format('Y-m-d');

        $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereDate('tanggal', $today)
            ->firstOrFail();

        if ($attendance->clock_out) {
            throw new \Exception('Anda sudah clock out hari ini.');
        }

        $attendance->clockOut($lat, $lng);

        return $attendance->fresh();
    }

    /**
     * Check apakah teacher sudah clock in untuk tanggal tertentu
     * untuk mencegah duplicate clock in
     */
    public function isAlreadyClockedIn(User $teacher, string $date): bool
    {
        return TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereDate('tanggal', $date)
            ->exists();
    }

    /**
     * Calculate lateness berdasarkan clock in time vs jam masuk sekolah
     * dengan return array yang mencakup is_late dan minutes_late
     *
     * @return array{is_late: bool, minutes_late: int}
     */
    public function calculateLateness(Carbon $clockIn): array
    {
        $schoolStartTime = Carbon::today()->setTime(7, 30);

        $isLate = $clockIn->isAfter($schoolStartTime);
        $minutesLate = $isLate ? $clockIn->diffInMinutes($schoolStartTime) : 0;

        return [
            'is_late' => $isLate,
            'minutes_late' => $minutesLate,
        ];
    }

    // ==================== TEACHER LEAVE ====================

    /**
     * Submit permohonan cuti guru dengan attachment
     * dan auto-calculate jumlah hari
     *
     * @param  array{jenis: string, tanggal_mulai: string, tanggal_selesai: string, alasan: string, attachment_path: ?string}  $data
     */
    public function submitTeacherLeave(array $data, User $teacher): TeacherLeave
    {
        $startDate = Carbon::parse($data['tanggal_mulai']);
        $endDate = Carbon::parse($data['tanggal_selesai']);

        // Calculate business days (exclude weekends)
        $jumlahHari = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if (! $currentDate->isWeekend()) {
                $jumlahHari++;
            }
            $currentDate->addDay();
        }

        return TeacherLeave::create([
            'teacher_id' => $teacher->id,
            'jenis' => $data['jenis'],
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'jumlah_hari' => $jumlahHari,
            'alasan' => $data['alasan'],
            'attachment_path' => $data['attachment_path'] ?? null,
            'status' => 'PENDING',
        ]);
    }

    /**
     * Approve teacher leave request
     * dengan mengubah status dan record reviewer
     */
    public function approveTeacherLeave(TeacherLeave $leave, User $reviewer): void
    {
        $leave->approve($reviewer);
    }

    // ==================== REPORTING ====================

    /**
     * Get summary attendance untuk satu kelas pada tanggal tertentu
     * yang mencakup total hadir, izin, sakit, alpha
     *
     * @return array{total_siswa: int, hadir: int, izin: int, sakit: int, alpha: int, belum_diabsen: int}
     */
    public function getClassAttendanceSummary(int $classId, string $date): array
    {
        $class = SchoolClass::with('students')->find($classId);
        $totalSiswa = $class->students()->count();

        $attendances = StudentAttendance::where('class_id', $classId)
            ->whereDate('tanggal', $date)
            ->get();

        return [
            'total_siswa' => $totalSiswa,
            'hadir' => $attendances->where('status', 'H')->count(),
            'izin' => $attendances->where('status', 'I')->count(),
            'sakit' => $attendances->where('status', 'S')->count(),
            'alpha' => $attendances->where('status', 'A')->count(),
            'belum_diabsen' => $totalSiswa - $attendances->count(),
        ];
    }

    /**
     * Get attendance report untuk satu siswa dalam periode tertentu
     * yang mencakup detail per tanggal dan summary
     *
     * @return array{summary: array{hadir: int, izin: int, sakit: int, alpha: int, total: int}, details: Collection<int, StudentAttendance>}
     */
    public function getStudentAttendanceReport(int $studentId, string $startDate, string $endDate): array
    {
        $student = Student::findOrFail($studentId);

        $attendances = $student->dailyAttendances()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->get();

        $summary = [
            'hadir' => $attendances->where('status', 'H')->count(),
            'izin' => $attendances->where('status', 'I')->count(),
            'sakit' => $attendances->where('status', 'S')->count(),
            'alpha' => $attendances->where('status', 'A')->count(),
            'total' => $attendances->count(),
        ];

        return [
            'summary' => $summary,
            'details' => $attendances,
        ];
    }

    /**
     * Get list kelas yang dapat diakses oleh teacher
     * yaitu kelas dimana teacher menjadi wali kelas atau mengajar
     *
     * @return Collection<int, SchoolClass>
     */
    public function getTeacherClasses(User $teacher): Collection
    {
        // Get kelas dimana teacher adalah wali kelas
        $homeRoomClasses = SchoolClass::where('wali_kelas_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        // Get kelas dimana teacher mengajar
        $teachingClassIds = DB::table('teacher_subjects')
            ->where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();

        $teachingClasses = SchoolClass::whereIn('id', $teachingClassIds)
            ->where('is_active', true)
            ->get();

        // Merge dan remove duplicates
        $allClasses = $homeRoomClasses->merge($teachingClasses)->unique('id');

        // Add computed fields
        return $allClasses->map(function ($class) {
            $class->nama_lengkap = "Kelas {$class->tingkat}{$class->nama}";
            $class->jumlah_siswa = Student::where('kelas_id', $class->id)
                ->where('status', 'aktif')
                ->count();

            return $class;
        })->sortBy('tingkat')->sortBy('nama')->values();
    }

    // ==================== ADVANCED REPORTING ====================

    /**
     * Get comprehensive attendance report dengan filters
     * untuk admin/principal dengan pagination dan sorting
     * Menggunakan eager loading untuk prevent N+1 queries
     *
     * @param  array{start_date?: string, end_date?: string, class_id?: int, status?: string, student_id?: int}  $filters
     * @return Collection<int, StudentAttendance>
     */
    public function getAttendanceReport(array $filters): Collection
    {
        $query = StudentAttendance::with([
            'student:id,nis,nama_lengkap,kelas_id',
            'class:id,tingkat,nama',
            'recordedBy:id,name',
        ]);

        if (isset($filters['start_date'])) {
            $query->whereDate('tanggal', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('tanggal', '<=', $filters['end_date']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        return $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(1000) // Limit untuk performance
            ->get();
    }

    /**
     * Calculate attendance statistics untuk satu siswa dalam periode tertentu
     * dengan persentase kehadiran dan breakdown per status
     *
     * @return array{hadir: int, izin: int, sakit: int, alpha: int, total: int, persentase_hadir: float}
     */
    public function calculateAttendanceStatistics(int $studentId, string $startDate, string $endDate): array
    {
        $attendances = StudentAttendance::where('student_id', $studentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $hadir = $attendances->where('status', 'H')->count();
        $izin = $attendances->where('status', 'I')->count();
        $sakit = $attendances->where('status', 'S')->count();
        $alpha = $attendances->where('status', 'A')->count();
        $total = $attendances->count();

        $persentaseHadir = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

        return [
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpha' => $alpha,
            'total' => $total,
            'persentase_hadir' => $persentaseHadir,
        ];
    }

    /**
     * Get attendance trend untuk satu kelas dalam periode tertentu
     * untuk visualisasi chart dengan data per tanggal
     *
     * @return array<string, array{date: string, hadir: int, izin: int, sakit: int, alpha: int}>
     */
    public function getClassAttendanceTrend(int $classId, string $startDate, string $endDate): array
    {
        $attendances = StudentAttendance::where('class_id', $classId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get()
            ->groupBy(function ($attendance) {
                return Carbon::parse($attendance->tanggal)->format('Y-m-d');
            });

        $trend = [];
        foreach ($attendances as $date => $dayAttendances) {
            $trend[$date] = [
                'date' => $date,
                'hadir' => $dayAttendances->where('status', 'H')->count(),
                'izin' => $dayAttendances->where('status', 'I')->count(),
                'sakit' => $dayAttendances->where('status', 'S')->count(),
                'alpha' => $dayAttendances->where('status', 'A')->count(),
            ];
        }

        return $trend;
    }

    /**
     * Get teacher attendance report dengan filters
     * untuk admin/principal dengan calculation work hours
     * Menggunakan eager loading untuk prevent N+1 queries
     *
     * @param  array{start_date?: string, end_date?: string, teacher_id?: int}  $filters
     * @return Collection<int, TeacherAttendance>
     */
    public function getTeacherAttendanceReport(array $filters): Collection
    {
        $query = TeacherAttendance::with(['teacher:id,name,email']);

        if (isset($filters['start_date'])) {
            $query->whereDate('tanggal', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('tanggal', '<=', $filters['end_date']);
        }

        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        return $query->orderBy('tanggal', 'desc')
            ->orderBy('clock_in', 'asc')
            ->limit(1000) // Limit untuk performance
            ->get();
    }

    /**
     * Calculate work hours untuk satu teacher dalam satu bulan
     * dengan total hours, late days, dan absent days
     *
     * @return array{total_days: int, present_days: int, late_days: int, total_hours: float, average_hours: float}
     */
    public function calculateTeacherWorkHours(int $teacherId, string $month): array
    {
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $attendances = TeacherAttendance::where('teacher_id', $teacherId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $totalDays = $startDate->diffInWeekdays($endDate) + 1;
        $presentDays = $attendances->count();
        $lateDays = $attendances->where('is_late', true)->count();

        $totalHours = 0;
        foreach ($attendances as $attendance) {
            if ($attendance->clock_in && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = Carbon::parse($attendance->clock_out);
                $totalHours += $clockOut->diffInHours($clockIn, true);
            }
        }

        $averageHours = $presentDays > 0 ? round($totalHours / $presentDays, 2) : 0;

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'total_hours' => round($totalHours, 2),
            'average_hours' => $averageHours,
        ];
    }

    /**
     * Get today's attendance summary untuk dashboard
     * dengan total students, attendance rate, dan breakdown per status
     * Cached for 5 minutes untuk performance
     *
     * @return array{total_students: int, total_present: int, total_absent: int, attendance_rate: float, by_status: array}
     */
    public function getTodayAttendanceSummary(): array
    {
        $today = Carbon::today()->format('Y-m-d');
        $cacheKey = "attendance.summary.{$today}";

        return Cache::remember($cacheKey, 300, function () use ($today) {
            $totalStudents = Student::where('status', 'aktif')->count();
            $attendances = StudentAttendance::whereDate('tanggal', $today)->get();

            $totalPresent = $attendances->where('status', 'H')->count();
            $totalAbsent = $attendances->whereIn('status', ['A'])->count();
            $attendanceRate = $totalStudents > 0 ? round(($totalPresent / $totalStudents) * 100, 2) : 0;

            return [
                'total_students' => $totalStudents,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'attendance_rate' => $attendanceRate,
                'by_status' => [
                    'hadir' => $attendances->where('status', 'H')->count(),
                    'izin' => $attendances->where('status', 'I')->count(),
                    'sakit' => $attendances->where('status', 'S')->count(),
                    'alpha' => $attendances->where('status', 'A')->count(),
                ],
            ];
        });
    }

    /**
     * Get classes yang belum input attendance untuk tanggal tertentu
     * untuk alert principal/admin
     *
     * @return Collection<int, SchoolClass>
     */
    public function getClassesWithoutAttendance(string $date): Collection
    {
        $classesWithAttendance = StudentAttendance::whereDate('tanggal', $date)
            ->distinct()
            ->pluck('class_id');

        return SchoolClass::where('is_active', true)
            ->whereNotIn('id', $classesWithAttendance)
            ->with('waliKelas')
            ->get()
            ->map(function ($class) {
                $class->nama_lengkap = "Kelas {$class->tingkat}{$class->nama}";
                $class->jumlah_siswa = Student::where('kelas_id', $class->id)
                    ->where('status', 'aktif')
                    ->count();

                return $class;
            });
    }

    /**
     * Get teachers yang belum clock in untuk tanggal tertentu
     * untuk alert principal
     *
     * @return Collection<int, User>
     */
    public function getTeacherAbsenceToday(): Collection
    {
        $today = Carbon::today()->format('Y-m-d');

        $clockedInTeacherIds = TeacherAttendance::whereDate('tanggal', $today)
            ->pluck('teacher_id');

        return User::where('role', 'TEACHER')
            ->where('is_active', true)
            ->whereNotIn('id', $clockedInTeacherIds)
            ->get();
    }
}
