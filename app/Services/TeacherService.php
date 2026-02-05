<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TeacherService
{
    /**
     * Create teacher baru dengan auto-create user account
     * untuk login credentials guru
     *
     * @param  array<string, mixed>  $data  Data teacher dari form
     * @return Teacher Teacher yang berhasil dibuat
     *
     * @throws \Exception Jika gagal membuat teacher atau user
     */
    public function createTeacher(array $data): Teacher
    {
        DB::beginTransaction();

        try {
            // Handle foto upload jika ada
            $fotoPath = null;
            if (isset($data['foto']) && $data['foto'] instanceof \Illuminate\Http\UploadedFile) {
                $fotoPath = $data['foto']->store('teachers/photos', 'public');
            }

            // Generate username untuk user account: guru_{timestamp}
            $username = 'guru_'.time().rand(100, 999);

            // Generate password default: FirstName + 4 random digits
            $firstName = explode(' ', $data['nama_lengkap'])[0];
            $defaultPassword = $firstName.rand(1000, 9999);

            // Create user account untuk teacher
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'username' => $username,
                'email' => $data['email'],
                'password' => Hash::make($defaultPassword),
                'role' => 'TEACHER',
                'status' => 'ACTIVE',
                'is_first_login' => true,
                'phone_number' => $data['no_hp'] ?? null,
            ]);

            // Create teacher record
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'nip' => $data['nip'] ?? null,
                'nik' => $data['nik'],
                'nama_lengkap' => $data['nama_lengkap'],
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'alamat' => $data['alamat'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'email' => $data['email'],
                'foto' => $fotoPath,
                'status_kepegawaian' => $data['status_kepegawaian'],
                'tanggal_mulai_kerja' => $data['tanggal_mulai_kerja'] ?? null,
                'tanggal_berakhir_kontrak' => $data['tanggal_berakhir_kontrak'] ?? null,
                'kualifikasi_pendidikan' => $data['kualifikasi_pendidikan'] ?? null,
                'is_active' => true,
            ]);

            // Sync subjects jika ada
            if (! empty($data['subjects'])) {
                $this->syncSubjects($teacher, $data['subjects'], $data['tahun_ajaran'] ?? null);
            }

            DB::commit();

            // Return teacher dengan password untuk notifikasi
            $teacher->generated_password = $defaultPassword;

            return $teacher;
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded foto jika ada error
            if (isset($fotoPath) && $fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }

            throw $e;
        }
    }

    /**
     * Update teacher data dengan handling foto dan subjects
     *
     * @param  Teacher  $teacher  Teacher yang akan diupdate
     * @param  array<string, mixed>  $data  Data baru dari form
     * @return Teacher Teacher yang sudah diupdate
     *
     * @throws \Exception Jika gagal mengupdate teacher
     */
    public function updateTeacher(Teacher $teacher, array $data): Teacher
    {
        DB::beginTransaction();

        try {
            // Handle foto upload/update
            $fotoPath = $teacher->foto;
            if (isset($data['foto']) && $data['foto'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old foto
                if ($teacher->foto) {
                    Storage::disk('public')->delete($teacher->foto);
                }
                $fotoPath = $data['foto']->store('teachers/photos', 'public');
            }

            // Update teacher record
            $teacher->update([
                'nip' => $data['nip'] ?? $teacher->nip,
                'nik' => $data['nik'],
                'nama_lengkap' => $data['nama_lengkap'],
                'tempat_lahir' => $data['tempat_lahir'] ?? $teacher->tempat_lahir,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? $teacher->tanggal_lahir,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'alamat' => $data['alamat'] ?? $teacher->alamat,
                'no_hp' => $data['no_hp'] ?? $teacher->no_hp,
                'email' => $data['email'],
                'foto' => $fotoPath,
                'status_kepegawaian' => $data['status_kepegawaian'],
                'tanggal_mulai_kerja' => $data['tanggal_mulai_kerja'] ?? $teacher->tanggal_mulai_kerja,
                'tanggal_berakhir_kontrak' => $data['tanggal_berakhir_kontrak'] ?? null,
                'kualifikasi_pendidikan' => $data['kualifikasi_pendidikan'] ?? $teacher->kualifikasi_pendidikan,
            ]);

            // Update linked user data jika ada
            if ($teacher->user) {
                $teacher->user->update([
                    'name' => $data['nama_lengkap'],
                    'email' => $data['email'],
                    'phone_number' => $data['no_hp'] ?? $teacher->user->phone_number,
                ]);
            }

            // Sync subjects jika ada
            if (isset($data['subjects'])) {
                $this->syncSubjects($teacher, $data['subjects'], $data['tahun_ajaran'] ?? null);
            }

            DB::commit();

            return $teacher->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Toggle status aktif/nonaktif teacher
     * Juga mengupdate status user account jika ada
     *
     * @param  Teacher  $teacher  Teacher yang akan ditoggle statusnya
     * @return Teacher Teacher dengan status baru
     */
    public function toggleStatus(Teacher $teacher): Teacher
    {
        DB::beginTransaction();

        try {
            $newStatus = ! $teacher->is_active;

            $teacher->update(['is_active' => $newStatus]);

            // Update linked user status jika ada
            if ($teacher->user) {
                $teacher->user->update([
                    'status' => $newStatus ? 'ACTIVE' : 'INACTIVE',
                ]);
            }

            DB::commit();

            return $teacher->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Sync mata pelajaran yang diajar oleh teacher
     *
     * @param  Teacher  $teacher  Teacher yang akan di-sync subjectsnya
     * @param  array<int, array{id: int, is_primary?: bool}>|array<int>  $subjects  Array of subject IDs atau array dengan is_primary
     * @param  string|null  $tahunAjaran  Tahun ajaran dalam format 2024/2025
     */
    public function syncSubjects(Teacher $teacher, array $subjects, ?string $tahunAjaran = null): void
    {
        // Generate tahun ajaran jika tidak disediakan
        if (! $tahunAjaran) {
            $year = now()->month >= 7 ? now()->year : now()->year - 1;
            $tahunAjaran = $year.'/'.($year + 1);
        }

        // Prepare sync data
        $syncData = [];

        foreach ($subjects as $subject) {
            if (is_array($subject)) {
                // Format: [{id: 1, is_primary: true}, ...]
                $syncData[$subject['id']] = [
                    'tahun_ajaran' => $tahunAjaran,
                    'is_primary' => $subject['is_primary'] ?? false,
                    'class_id' => $subject['class_id'] ?? null,
                ];
            } else {
                // Format: [1, 2, 3, ...] - simple array of IDs
                $syncData[$subject] = [
                    'tahun_ajaran' => $tahunAjaran,
                    'is_primary' => true, // Default ke primary jika simple array
                    'class_id' => null,
                ];
            }
        }

        $teacher->subjects()->sync($syncData);
    }

    /**
     * Delete foto teacher dari storage
     *
     * @param  Teacher  $teacher  Teacher yang fotonya akan dihapus
     */
    public function deletePhoto(Teacher $teacher): void
    {
        if ($teacher->foto) {
            Storage::disk('public')->delete($teacher->foto);
            $teacher->update(['foto' => null]);
        }
    }

    /**
     * Get list guru yang kontraknya akan berakhir dalam N hari ke depan
     *
     * @param  int  $days  Jumlah hari ke depan untuk dicek
     * @return \Illuminate\Database\Eloquent\Collection<int, Teacher>
     */
    public function getExpiringContracts(int $days = 30)
    {
        return Teacher::contractEndingSoon($days)
            ->with(['user', 'subjects'])
            ->orderBy('tanggal_berakhir_kontrak')
            ->get();
    }
}
