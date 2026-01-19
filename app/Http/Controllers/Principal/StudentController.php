<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display list of all students dengan pagination, search, dan filter capabilities
     * untuk Principal dengan read-only access
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

        return Inertia::render('Principal/Students/Index', [
            'students' => $students,
            'filters' => $request->only(['search', 'kelas_id', 'status', 'tahun_ajaran', 'jenis_kelamin']),
            'classes' => $classes,
        ]);
    }

    /**
     * Display detail profil siswa lengkap dengan guardians, history,
     * dan payment summary (Principal memiliki akses ke data pembayaran)
     */
    public function show(Student $student)
    {
        $student->load([
            'guardians',
            'primaryGuardian',
            'classHistory.kelas',
            'statusHistory.changedBy',
            'kelas',
        ]);

        return Inertia::render('Principal/Students/Show', [
            'student' => $student,
            'canEdit' => false, // Principal hanya read-only
        ]);
    }
}
