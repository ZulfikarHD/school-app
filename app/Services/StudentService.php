<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\StudentStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentService
{
    /**
     * Generate NIS (Nomor Induk Siswa) otomatis dengan format {tahun_masuk}{nomor_urut}
     * dimana nomor urut adalah 4 digit yang increment per tahun masuk
     *
     * @param  string  $tahunAjaran  Format: 2024/2025
     * @return string Format: 20240001
     */
    public function generateNis(string $tahunAjaran): string
    {
        // Extract tahun dari format 2024/2025 -> 2024
        $year = substr($tahunAjaran, 0, 4);

        // Get last NIS untuk tahun ini dengan proper ordering
        $lastNis = Student::where('nis', 'like', $year.'%')
            ->orderByRaw('CAST(SUBSTR(nis, 5, 4) AS INTEGER) DESC')
            ->value('nis');

        // Calculate next number
        if ($lastNis) {
            $lastNumber = (int) substr($lastNis, 4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format: {year}{4-digit number}
        return $year.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create atau link guardian ke student dengan auto-create parent account
     * untuk kontak utama yang akan digunakan untuk portal orang tua
     *
     * @param  array  $guardianData  Array dengan keys: ayah, ibu, wali (optional)
     */
    public function attachGuardiansToStudent(Student $student, array $guardianData): void
    {
        DB::beginTransaction();

        try {
            $primaryContactSet = false;

            // Process Ayah
            if (! empty($guardianData['ayah'])) {
                $ayah = $this->createOrUpdateGuardian($guardianData['ayah'], 'ayah');
                $isPrimary = $guardianData['ayah']['is_primary_contact'] ?? false;

                $student->guardians()->attach($ayah->id, [
                    'is_primary_contact' => $isPrimary,
                ]);

                if ($isPrimary) {
                    $this->createParentAccount($ayah, $student);
                    $primaryContactSet = true;
                }
            }

            // Process Ibu
            if (! empty($guardianData['ibu'])) {
                $ibu = $this->createOrUpdateGuardian($guardianData['ibu'], 'ibu');
                $isPrimary = $guardianData['ibu']['is_primary_contact'] ?? false;

                // Jika ayah sudah primary, ibu tidak bisa primary
                if ($primaryContactSet) {
                    $isPrimary = false;
                }

                $student->guardians()->attach($ibu->id, [
                    'is_primary_contact' => $isPrimary,
                ]);

                if ($isPrimary) {
                    $this->createParentAccount($ibu, $student);
                    $primaryContactSet = true;
                }
            }

            // Process Wali (optional)
            if (! empty($guardianData['wali'])) {
                $wali = $this->createOrUpdateGuardian($guardianData['wali'], 'wali');
                $isPrimary = $guardianData['wali']['is_primary_contact'] ?? false;

                if ($primaryContactSet) {
                    $isPrimary = false;
                }

                $student->guardians()->attach($wali->id, [
                    'is_primary_contact' => $isPrimary,
                ]);

                if ($isPrimary) {
                    $this->createParentAccount($wali, $student);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create atau update guardian record berdasarkan NIK
     * untuk avoid duplicate guardian jika sudah ada di sistem
     */
    protected function createOrUpdateGuardian(array $data, string $hubungan): Guardian
    {
        // Check if guardian dengan NIK ini sudah ada
        $guardian = Guardian::where('nik', $data['nik'])->first();

        if ($guardian) {
            // Update existing guardian
            $guardian->update([
                'nama_lengkap' => $data['nama_lengkap'],
                'pekerjaan' => $data['pekerjaan'],
                'pendidikan' => $data['pendidikan'],
                'penghasilan' => $data['penghasilan'],
                'no_hp' => $data['no_hp'],
                'email' => $data['email'] ?? null,
                'alamat' => $data['alamat'] ?? null,
            ]);
        } else {
            // Create new guardian
            $guardian = Guardian::create([
                'nik' => $data['nik'],
                'nama_lengkap' => $data['nama_lengkap'],
                'hubungan' => $hubungan,
                'pekerjaan' => $data['pekerjaan'],
                'pendidikan' => $data['pendidikan'],
                'penghasilan' => $data['penghasilan'],
                'no_hp' => $data['no_hp'],
                'email' => $data['email'] ?? null,
                'alamat' => $data['alamat'] ?? null,
            ]);
        }

        return $guardian;
    }

    /**
     * Create parent account untuk guardian dengan username = no HP dan password = Ortu{NIS}
     * Jika guardian sudah punya account (multi-child), skip creation
     */
    protected function createParentAccount(Guardian $guardian, Student $student): ?User
    {
        // Jika guardian sudah punya user account, skip
        if ($guardian->user_id) {
            return null;
        }

        // Normalize nomor HP untuk username (remove spaces, dashes, etc)
        $username = preg_replace('/[^0-9]/', '', $guardian->no_hp);

        // Check if user dengan username ini sudah ada (untuk multi-child case)
        $existingUser = User::where('username', $username)->first();

        if ($existingUser) {
            // Link guardian ke existing user
            $guardian->update(['user_id' => $existingUser->id]);

            return $existingUser;
        }

        // Create new parent account
        $password = 'Ortu'.$student->nis;

        $user = User::create([
            'name' => $guardian->nama_lengkap,
            'username' => $username,
            'email' => $guardian->email ?? $username.'@parent.sekolah.id',
            'phone_number' => $guardian->no_hp,
            'password' => Hash::make($password),
            'role' => 'PARENT',
            'status' => 'ACTIVE',
            'is_first_login' => true,
        ]);

        // Link guardian ke user
        $guardian->update(['user_id' => $user->id]);

        // TODO: Send WhatsApp notification dengan credentials
        // $this->sendParentCredentials($guardian, $username, $password);

        return $user;
    }

    /**
     * Bulk promote students ke kelas yang lebih tinggi dengan insert history record
     * untuk tracking perpindahan kelas siswa
     *
     * @param  array  $studentIds  Array of student IDs
     * @return int Number of students promoted
     */
    public function bulkPromoteStudents(
        array $studentIds,
        int $kelasIdBaru,
        string $tahunAjaranBaru,
        ?string $waliKelas = null
    ): int {
        DB::beginTransaction();

        try {
            $students = Student::whereIn('id', $studentIds)->get();
            $promotedCount = 0;

            foreach ($students as $student) {
                // Insert class history record
                StudentClassHistory::create([
                    'student_id' => $student->id,
                    'kelas_id' => $kelasIdBaru,
                    'tahun_ajaran' => $tahunAjaranBaru,
                    'wali_kelas' => $waliKelas,
                ]);

                // Update student current class
                $student->update([
                    'kelas_id' => $kelasIdBaru,
                ]);

                $promotedCount++;
            }

            DB::commit();

            return $promotedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update student status dengan insert history record untuk audit trail
     * dan tracking perubahan status siswa dari waktu ke waktu
     *
     * @param  array  $additionalData  Array dengan keys: tanggal, alasan, keterangan, sekolah_tujuan
     * @param  int  $changedBy  User ID yang melakukan perubahan
     */
    public function updateStudentStatus(
        Student $student,
        string $statusBaru,
        array $additionalData,
        int $changedBy
    ): StudentStatusHistory {
        DB::beginTransaction();

        try {
            $statusLama = $student->status;

            // Create status history record
            $history = StudentStatusHistory::create([
                'student_id' => $student->id,
                'status_lama' => $statusLama,
                'status_baru' => $statusBaru,
                'tanggal' => $additionalData['tanggal'],
                'alasan' => $additionalData['alasan'],
                'keterangan' => $additionalData['keterangan'] ?? null,
                'sekolah_tujuan' => $additionalData['sekolah_tujuan'] ?? null,
                'changed_by' => $changedBy,
            ]);

            // Update student status
            $student->update(['status' => $statusBaru]);

            DB::commit();

            return $history;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Assign students ke kelas tertentu
     *
     * @return int Jumlah siswa yang berhasil dipindahkan
     */
    public function assignStudentsToClass(
        array $studentIds,
        int $kelasId,
        string $tahunAjaran,
        ?string $notes = null
    ): int {
        DB::beginTransaction();

        try {
            $students = Student::whereIn('id', $studentIds)->get();
            $assignedCount = 0;

            // Get wali kelas name for history
            $kelas = SchoolClass::with('waliKelas')->find($kelasId);
            $waliKelasNama = $kelas->waliKelas->name ?? null;

            foreach ($students as $student) {
                // Insert class history record
                StudentClassHistory::create([
                    'student_id' => $student->id,
                    'kelas_id' => $kelasId,
                    'tahun_ajaran' => $tahunAjaran,
                    'wali_kelas' => $waliKelasNama,
                ]);

                // Update student current class
                $student->update(['kelas_id' => $kelasId]);
                $assignedCount++;
            }

            DB::commit();

            return $assignedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Normalize nomor HP untuk format Indonesia dengan remove spaces, dashes, dan prefix
     */
    public function normalizePhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Remove leading +62 or 62
        if (str_starts_with($cleaned, '62')) {
            $cleaned = '0'.substr($cleaned, 2);
        }

        return $cleaned;
    }
}
