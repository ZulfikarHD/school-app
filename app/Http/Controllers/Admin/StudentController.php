<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignClassRequest;
use App\Http\Requests\Admin\BulkPromoteRequest;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Http\Requests\Admin\UpdateStudentStatusRequest;
use App\Models\ActivityLog;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService
    ) {}

    /**
     * Display list of students dengan pagination, search, dan filter capabilities
     * untuk student management interface
     */
    public function index(Request $request)
    {
        $query = Student::query()->with(['guardians', 'primaryGuardian', 'kelas']);

        // Search by nama, NIS, atau NISN
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filter by kelas
        if ($kelasId = $request->input('kelas_id')) {
            $query->byClass($kelasId);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            // Default: hanya tampilkan siswa aktif
            $query->active();
        }

        // Filter by tahun ajaran
        if ($tahunAjaran = $request->input('tahun_ajaran')) {
            $query->byAcademicYear($tahunAjaran);
        }

        // Filter by jenis kelamin
        if ($jenisKelamin = $request->input('jenis_kelamin')) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $classes = SchoolClass::active()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'filters' => $request->only(['search', 'kelas_id', 'status', 'tahun_ajaran', 'jenis_kelamin']),
            'classes' => $classes,
        ]);
    }

    /**
     * Show form untuk create student baru
     */
    public function create()
    {
        return Inertia::render('Admin/Students/Create');
    }

    /**
     * Store student baru dengan auto-generated NIS dan auto-create parent account
     * serta activity logging untuk audit trail
     */
    public function store(StoreStudentRequest $request)
    {
        DB::beginTransaction();

        try {
            // Generate NIS
            $nis = $this->studentService->generateNis($request->tahun_ajaran_masuk);

            // Handle foto upload
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('students/photos', 'public');
            }

            // Create student
            $student = Student::create([
                'nis' => $nis,
                'nisn' => $request->nisn,
                'nik' => $request->nik,
                'nama_lengkap' => $request->nama_lengkap,
                'nama_panggilan' => $request->nama_panggilan,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'anak_ke' => $request->anak_ke,
                'jumlah_saudara' => $request->jumlah_saudara,
                'status_keluarga' => $request->status_keluarga,
                'alamat' => $request->alamat,
                'rt_rw' => $request->rt_rw,
                'kelurahan' => $request->kelurahan,
                'kecamatan' => $request->kecamatan,
                'kota' => $request->kota,
                'provinsi' => $request->provinsi,
                'kode_pos' => $request->kode_pos,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'foto' => $fotoPath,
                'kelas_id' => $request->kelas_id,
                'tahun_ajaran_masuk' => $request->tahun_ajaran_masuk,
                'tanggal_masuk' => $request->tanggal_masuk,
                'status' => 'aktif',
            ]);

            // Attach guardians dan create parent account
            $guardianData = [
                'ayah' => $request->ayah,
                'ibu' => $request->ibu,
                'wali' => $request->wali,
            ];

            $this->studentService->attachGuardiansToStudent($student, $guardianData);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create_student',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'student_id' => $student->id,
                    'nis' => $student->nis,
                    'nama_lengkap' => $student->nama_lengkap,
                ],
                'status' => 'success',
            ]);

            DB::commit();

            return redirect()->route('admin.students.index')
                ->with('success', "Siswa berhasil ditambahkan dengan NIS: {$nis}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create student', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal menambahkan siswa. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Display detail profil siswa lengkap dengan guardians dan history
     */
    public function show(Student $student)
    {
        $student->load([
            'guardians',
            'primaryGuardian',
            'classHistory',
            'statusHistory.changedBy',
            'kelas',
        ]);

        $classes = SchoolClass::active()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        return Inertia::render('Admin/Students/Show', [
            'student' => $student,
            'classes' => $classes,
        ]);
    }

    /**
     * Show form untuk edit student existing dengan current data
     */
    public function edit(Student $student)
    {
        $student->load('guardians');

        return Inertia::render('Admin/Students/Edit', [
            'student' => $student,
        ]);
    }

    /**
     * Update student data dengan validation dan activity logging
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        DB::beginTransaction();

        try {
            $oldValues = [
                'nama_lengkap' => $student->nama_lengkap,
                'nik' => $student->nik,
                'nisn' => $student->nisn,
                'kelas_id' => $student->kelas_id,
            ];

            // Handle foto upload
            $fotoPath = $student->foto;
            if ($request->hasFile('foto')) {
                // Delete old foto
                if ($student->foto) {
                    Storage::disk('public')->delete($student->foto);
                }
                $fotoPath = $request->file('foto')->store('students/photos', 'public');
            }

            // Update student
            $student->update([
                'nisn' => $request->nisn,
                'nik' => $request->nik,
                'nama_lengkap' => $request->nama_lengkap,
                'nama_panggilan' => $request->nama_panggilan,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'anak_ke' => $request->anak_ke,
                'jumlah_saudara' => $request->jumlah_saudara,
                'status_keluarga' => $request->status_keluarga,
                'alamat' => $request->alamat,
                'rt_rw' => $request->rt_rw,
                'kelurahan' => $request->kelurahan,
                'kecamatan' => $request->kecamatan,
                'kota' => $request->kota,
                'provinsi' => $request->provinsi,
                'kode_pos' => $request->kode_pos,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'foto' => $fotoPath,
                'kelas_id' => $request->kelas_id,
            ]);

            // Update guardians - detach all dan re-attach
            $student->guardians()->detach();

            $guardianData = [
                'ayah' => $request->ayah,
                'ibu' => $request->ibu,
                'wali' => $request->wali,
            ];

            $this->studentService->attachGuardiansToStudent($student, $guardianData);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_student',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => $oldValues,
                'new_values' => [
                    'student_id' => $student->id,
                    'nama_lengkap' => $student->nama_lengkap,
                    'nik' => $student->nik,
                    'nisn' => $student->nisn,
                ],
                'status' => 'success',
            ]);

            DB::commit();

            return redirect()->route('admin.students.index')
                ->with('success', 'Data siswa berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update student', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal mengupdate siswa. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Soft delete student dengan konfirmasi
     */
    public function destroy(Student $student)
    {
        DB::beginTransaction();

        try {
            // Log activity before delete
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete_student',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => [
                    'student_id' => $student->id,
                    'nis' => $student->nis,
                    'nama_lengkap' => $student->nama_lengkap,
                ],
                'status' => 'success',
            ]);

            // Soft delete
            $student->delete();

            DB::commit();

            return redirect()->route('admin.students.index')
                ->with('success', 'Siswa berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal menghapus siswa. Silakan coba lagi.']);
        }
    }

    /**
     * Update student status dengan insert history record
     */
    public function updateStatus(UpdateStudentStatusRequest $request, Student $student)
    {
        try {
            $this->studentService->updateStudentStatus(
                $student,
                $request->status,
                [
                    'tanggal' => $request->tanggal,
                    'alasan' => $request->alasan,
                    'keterangan' => $request->keterangan,
                    'sekolah_tujuan' => $request->sekolah_tujuan,
                ],
                auth()->id()
            );

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_student_status',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'student_id' => $student->id,
                    'status_baru' => $request->status,
                ],
                'status' => 'success',
            ]);

            return back()->with('success', 'Status siswa berhasil diupdate.');
        } catch (\Exception $e) {
            Log::error('Failed to update student status', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal mengupdate status siswa.']);
        }
    }

    /**
     * Assign students to class (single or bulk)
     */
    public function assignClass(AssignClassRequest $request)
    {
        try {
            $count = $this->studentService->assignStudentsToClass(
                $request->student_ids,
                $request->kelas_id,
                $request->tahun_ajaran ?? '2024/2025', // Default fallback if not provided
                $request->notes
            );

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'assign_students_to_class',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'student_count' => $count,
                    'kelas_id' => $request->kelas_id,
                    'tahun_ajaran' => $request->tahun_ajaran,
                ],
                'status' => 'success',
            ]);

            return back()->with('success', "{$count} siswa berhasil dipindahkan ke kelas.");
        } catch (\Exception $e) {
            Log::error('Failed to assign class', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal memindahkan siswa.']);
        }
    }

    /**
     * Display promote page dengan wizard untuk bulk promote students
     */
    public function showPromotePage()
    {
        $classes = SchoolClass::query()
            ->active()
            ->orderBy('tahun_ajaran', 'desc')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nama_lengkap', 'tingkat', 'tahun_ajaran']);

        return Inertia::render('Admin/Students/Promote', [
            'classes' => $classes,
        ]);
    }

    /**
     * Bulk promote students ke kelas yang lebih tinggi
     */
    public function promote(BulkPromoteRequest $request)
    {
        try {
            $promotedCount = $this->studentService->bulkPromoteStudents(
                $request->student_ids,
                $request->kelas_id_baru,
                $request->tahun_ajaran_baru,
                $request->wali_kelas
            );

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'bulk_promote_students',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'student_count' => $promotedCount,
                    'kelas_id_baru' => $request->kelas_id_baru,
                    'tahun_ajaran_baru' => $request->tahun_ajaran_baru,
                ],
                'status' => 'success',
            ]);

            return back()->with('success', "{$promotedCount} siswa berhasil dipindahkan ke kelas baru.");
        } catch (\Exception $e) {
            Log::error('Failed to promote students', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal memindahkan siswa.']);
        }
    }

    /**
     * Export student data to Excel
     * TODO: Implement Excel export using Laravel Excel package
     */
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return back()->with('info', 'Fitur export akan segera tersedia.');
    }

    /**
     * Preview import data dari Excel
     * TODO: Implement Excel import preview
     */
    public function importPreview(Request $request)
    {
        // TODO: Implement import preview functionality
        return back()->with('info', 'Fitur import akan segera tersedia.');
    }

    /**
     * Import student data dari Excel
     * TODO: Implement Excel import
     */
    public function import(Request $request)
    {
        // TODO: Implement import functionality
        return back()->with('info', 'Fitur import akan segera tersedia.');
    }
}
