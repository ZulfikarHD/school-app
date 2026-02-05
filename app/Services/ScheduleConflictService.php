<?php

namespace App\Services;

use App\Models\TeachingSchedule;
use Illuminate\Support\Facades\DB;

/**
 * ScheduleConflictService - Service untuk mendeteksi konflik jadwal mengajar
 *
 * Service ini bertujuan untuk memvalidasi jadwal baru atau update agar tidak
 * terjadi konflik dengan jadwal existing, yaitu: guru double-booking,
 * kelas double-booking, atau ruangan double-booking
 */
class ScheduleConflictService
{
    /**
     * Check semua jenis konflik untuk jadwal baru atau update
     *
     * @param  array<string, mixed>  $data  Data jadwal yang akan dicek
     * @param  int|null  $excludeId  ID jadwal yang dikecualikan (untuk update)
     * @return array<string, string> Array konflik dengan key dan pesan error
     */
    public function checkConflicts(array $data, ?int $excludeId = null): array
    {
        $conflicts = [];

        // Check teacher conflict
        if ($this->hasTeacherConflict(
            $data['teacher_id'],
            $data['hari'],
            $data['jam_mulai'],
            $data['jam_selesai'],
            $data['academic_year_id'],
            $excludeId
        )) {
            $conflicts['teacher'] = 'Guru sudah memiliki jadwal mengajar di waktu tersebut';
        }

        // Check class conflict
        if ($this->hasClassConflict(
            $data['class_id'],
            $data['hari'],
            $data['jam_mulai'],
            $data['jam_selesai'],
            $data['academic_year_id'],
            $excludeId
        )) {
            $conflicts['class'] = 'Kelas sudah memiliki jadwal pelajaran di waktu tersebut';
        }

        // Check room conflict (optional, only if ruangan is provided)
        if (! empty($data['ruangan'])) {
            if ($this->hasRoomConflict(
                $data['ruangan'],
                $data['hari'],
                $data['jam_mulai'],
                $data['jam_selesai'],
                $data['academic_year_id'],
                $excludeId
            )) {
                $conflicts['room'] = 'Ruangan sudah dipakai di waktu tersebut';
            }
        }

        return $conflicts;
    }

    /**
     * Check apakah guru sudah memiliki jadwal di waktu yang overlap
     *
     * @param  int  $teacherId  ID guru
     * @param  string  $hari  Hari dalam format enum (senin, selasa, dst)
     * @param  string  $jamMulai  Jam mulai format H:i
     * @param  string  $jamSelesai  Jam selesai format H:i
     * @param  int  $academicYearId  ID tahun ajaran
     * @param  int|null  $excludeId  ID jadwal yang dikecualikan
     */
    public function hasTeacherConflict(
        int $teacherId,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        int $academicYearId,
        ?int $excludeId = null
    ): bool {
        return TeachingSchedule::query()
            ->byTeacher($teacherId)
            ->byDay($hari)
            ->byAcademicYear($academicYearId)
            ->active()
            ->hasTimeOverlap($jamMulai, $jamSelesai, $excludeId)
            ->exists();
    }

    /**
     * Check apakah kelas sudah memiliki jadwal di waktu yang overlap
     *
     * @param  int  $classId  ID kelas
     * @param  string  $hari  Hari dalam format enum (senin, selasa, dst)
     * @param  string  $jamMulai  Jam mulai format H:i
     * @param  string  $jamSelesai  Jam selesai format H:i
     * @param  int  $academicYearId  ID tahun ajaran
     * @param  int|null  $excludeId  ID jadwal yang dikecualikan
     */
    public function hasClassConflict(
        int $classId,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        int $academicYearId,
        ?int $excludeId = null
    ): bool {
        return TeachingSchedule::query()
            ->byClass($classId)
            ->byDay($hari)
            ->byAcademicYear($academicYearId)
            ->active()
            ->hasTimeOverlap($jamMulai, $jamSelesai, $excludeId)
            ->exists();
    }

    /**
     * Check apakah ruangan sudah dipakai di waktu yang overlap
     *
     * @param  string  $ruangan  Nama ruangan
     * @param  string  $hari  Hari dalam format enum (senin, selasa, dst)
     * @param  string  $jamMulai  Jam mulai format H:i
     * @param  string  $jamSelesai  Jam selesai format H:i
     * @param  int  $academicYearId  ID tahun ajaran
     * @param  int|null  $excludeId  ID jadwal yang dikecualikan
     */
    public function hasRoomConflict(
        string $ruangan,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        int $academicYearId,
        ?int $excludeId = null
    ): bool {
        return TeachingSchedule::query()
            ->where('ruangan', $ruangan)
            ->byDay($hari)
            ->byAcademicYear($academicYearId)
            ->active()
            ->hasTimeOverlap($jamMulai, $jamSelesai, $excludeId)
            ->exists();
    }

    /**
     * Get detail konflik untuk ditampilkan di UI
     *
     * @param  array<string, mixed>  $data  Data jadwal yang akan dicek
     * @param  int|null  $excludeId  ID jadwal yang dikecualikan
     * @return array<string, array{message: string, conflicting_schedule: array<string, mixed>|null}>
     */
    public function getConflictDetails(array $data, ?int $excludeId = null): array
    {
        $details = [];

        // Get teacher conflict details
        $teacherConflict = TeachingSchedule::query()
            ->with(['subject', 'schoolClass'])
            ->byTeacher($data['teacher_id'])
            ->byDay($data['hari'])
            ->byAcademicYear($data['academic_year_id'])
            ->active()
            ->hasTimeOverlap($data['jam_mulai'], $data['jam_selesai'], $excludeId)
            ->first();

        if ($teacherConflict) {
            $details['teacher'] = [
                'message' => 'Guru sudah mengajar '.$teacherConflict->subject->nama_mapel.
                    ' di Kelas '.$teacherConflict->schoolClass->tingkat.$teacherConflict->schoolClass->nama.
                    ' ('.$teacherConflict->time_range.')',
                'conflicting_schedule' => [
                    'id' => $teacherConflict->id,
                    'subject' => $teacherConflict->subject->nama_mapel,
                    'class' => 'Kelas '.$teacherConflict->schoolClass->tingkat.$teacherConflict->schoolClass->nama,
                    'time' => $teacherConflict->time_range,
                ],
            ];
        }

        // Get class conflict details
        $classConflict = TeachingSchedule::query()
            ->with(['subject', 'teacher'])
            ->byClass($data['class_id'])
            ->byDay($data['hari'])
            ->byAcademicYear($data['academic_year_id'])
            ->active()
            ->hasTimeOverlap($data['jam_mulai'], $data['jam_selesai'], $excludeId)
            ->first();

        if ($classConflict) {
            $details['class'] = [
                'message' => 'Kelas sudah ada jadwal '.$classConflict->subject->nama_mapel.
                    ' dengan '.$classConflict->teacher->nama_lengkap.
                    ' ('.$classConflict->time_range.')',
                'conflicting_schedule' => [
                    'id' => $classConflict->id,
                    'subject' => $classConflict->subject->nama_mapel,
                    'teacher' => $classConflict->teacher->nama_lengkap,
                    'time' => $classConflict->time_range,
                ],
            ];
        }

        return $details;
    }

    /**
     * Copy jadwal dari tahun ajaran sebelumnya ke tahun ajaran baru
     *
     * @param  int  $fromAcademicYearId  ID tahun ajaran asal
     * @param  int  $toAcademicYearId  ID tahun ajaran tujuan
     * @return int Jumlah jadwal yang berhasil di-copy
     *
     * @throws \Exception Jika terjadi error saat copy
     */
    public function copyFromPreviousSemester(int $fromAcademicYearId, int $toAcademicYearId): int
    {
        // Get all active schedules from source academic year
        $sourceSchedules = TeachingSchedule::query()
            ->byAcademicYear($fromAcademicYearId)
            ->active()
            ->get();

        if ($sourceSchedules->isEmpty()) {
            return 0;
        }

        $copiedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($sourceSchedules as $schedule) {
                // Check if teacher and class still exist and active
                if (! $schedule->teacher || ! $schedule->teacher->is_active) {
                    continue;
                }

                if (! $schedule->schoolClass || ! $schedule->schoolClass->is_active) {
                    continue;
                }

                // Check if this exact schedule already exists in target year
                $exists = TeachingSchedule::query()
                    ->byTeacher($schedule->teacher_id)
                    ->byClass($schedule->class_id)
                    ->where('subject_id', $schedule->subject_id)
                    ->byDay($schedule->hari->value)
                    ->byAcademicYear($toAcademicYearId)
                    ->where('jam_mulai', $schedule->jam_mulai)
                    ->where('jam_selesai', $schedule->jam_selesai)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Check for conflicts in target academic year
                $conflicts = $this->checkConflicts([
                    'teacher_id' => $schedule->teacher_id,
                    'subject_id' => $schedule->subject_id,
                    'class_id' => $schedule->class_id,
                    'academic_year_id' => $toAcademicYearId,
                    'hari' => $schedule->hari->value,
                    'jam_mulai' => $schedule->jam_mulai,
                    'jam_selesai' => $schedule->jam_selesai,
                    'ruangan' => $schedule->ruangan,
                ]);

                // Skip if there are conflicts
                if (! empty($conflicts)) {
                    continue;
                }

                // Create new schedule in target academic year
                TeachingSchedule::create([
                    'teacher_id' => $schedule->teacher_id,
                    'subject_id' => $schedule->subject_id,
                    'class_id' => $schedule->class_id,
                    'academic_year_id' => $toAcademicYearId,
                    'hari' => $schedule->hari->value,
                    'jam_mulai' => $schedule->jam_mulai,
                    'jam_selesai' => $schedule->jam_selesai,
                    'ruangan' => $schedule->ruangan,
                    'is_active' => true,
                ]);

                $copiedCount++;
            }

            DB::commit();

            return $copiedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get summary of schedules by teacher for a specific academic year
     *
     * @param  int  $academicYearId  ID tahun ajaran
     * @return \Illuminate\Support\Collection Summary per teacher
     */
    public function getTeacherScheduleSummary(int $academicYearId)
    {
        return TeachingSchedule::query()
            ->selectRaw('teacher_id, COUNT(*) as total_schedules, SUM(TIMESTAMPDIFF(MINUTE, jam_mulai, jam_selesai)) as total_minutes')
            ->byAcademicYear($academicYearId)
            ->active()
            ->groupBy('teacher_id')
            ->with('teacher:id,nama_lengkap')
            ->get()
            ->map(function ($item) {
                return [
                    'teacher_id' => $item->teacher_id,
                    'teacher_name' => $item->teacher?->nama_lengkap ?? '-',
                    'total_schedules' => $item->total_schedules,
                    'total_hours' => round($item->total_minutes / 60, 1),
                    'total_jam_pelajaran' => round($item->total_minutes / 45, 1),
                ];
            });
    }

    /**
     * Validate time range untuk jadwal sekolah (07:00 - 16:00)
     *
     * @param  string  $jamMulai  Jam mulai format H:i
     * @param  string  $jamSelesai  Jam selesai format H:i
     * @return array<string, string> Array error messages
     */
    public function validateTimeRange(string $jamMulai, string $jamSelesai): array
    {
        $errors = [];
        $schoolStart = '07:00';
        $schoolEnd = '16:00';

        if ($jamMulai < $schoolStart) {
            $errors['jam_mulai'] = 'Jam mulai tidak boleh sebelum jam 07:00';
        }

        if ($jamSelesai > $schoolEnd) {
            $errors['jam_selesai'] = 'Jam selesai tidak boleh setelah jam 16:00';
        }

        if ($jamMulai >= $jamSelesai) {
            $errors['jam_selesai'] = 'Jam selesai harus setelah jam mulai';
        }

        return $errors;
    }
}
