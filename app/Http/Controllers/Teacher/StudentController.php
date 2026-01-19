<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display list of students yang ada di kelas yang diajar oleh teacher ini
     * dengan pagination, search, dan filter capabilities (read-only)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get class IDs yang diajar oleh teacher ini dari teacher_subjects pivot table
        $teacherClassIds = DB::table('teacher_subjects')
            ->where('teacher_id', $user->id)
            ->distinct()
            ->pluck('class_id');

        // Build query untuk students di kelas yang diajar
        $query = Student::query()
            ->with(['guardians', 'primaryGuardian', 'kelas'])
            ->whereIn('kelas_id', $teacherClassIds);

        // Search by nama, NIS, atau NISN
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filter by kelas (hanya kelas yang diajar teacher)
        if ($kelasId = $request->input('kelas_id')) {
            if ($teacherClassIds->contains($kelasId)) {
                $query->byClass($kelasId);
            }
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            // Default: hanya tampilkan siswa aktif
            $query->active();
        }

        // Filter by jenis kelamin
        if ($jenisKelamin = $request->input('jenis_kelamin')) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Get only classes yang diajar oleh teacher ini
        $classes = SchoolClass::query()
            ->active()
            ->whereIn('id', $teacherClassIds)
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        return Inertia::render('Teacher/Students/Index', [
            'students' => $students,
            'filters' => $request->only(['search', 'kelas_id', 'status', 'jenis_kelamin']),
            'classes' => $classes,
        ]);
    }

    /**
     * Display detail profil siswa dengan validation bahwa teacher hanya bisa
     * view siswa di kelas yang diajar (read-only, tanpa data pembayaran)
     */
    public function show(Request $request, Student $student)
    {
        $user = $request->user();

        // Get class IDs yang diajar oleh teacher ini
        $teacherClassIds = DB::table('teacher_subjects')
            ->where('teacher_id', $user->id)
            ->distinct()
            ->pluck('class_id');

        // Validation: pastikan student ada di kelas yang diajar teacher
        if (! $teacherClassIds->contains($student->kelas_id)) {
            abort(403, 'Anda tidak memiliki akses ke data siswa ini.');
        }

        $student->load([
            'guardians',
            'primaryGuardian',
            'classHistory.kelas',
            'kelas',
            // Note: Tidak load statusHistory karena teacher tidak perlu lihat history internal
        ]);

        return Inertia::render('Teacher/Students/Show', [
            'student' => $student,
            'canEdit' => false, // Teacher read-only
            'hidePayment' => true, // Teacher tidak bisa lihat data pembayaran
        ]);
    }
}
