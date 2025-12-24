<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChildController extends Controller
{
    /**
     * Display list of children untuk parent yang login
     * dimana parent bisa punya multiple children di sekolah
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get guardian record untuk user ini
        $guardian = $user->guardian;

        if (! $guardian) {
            return Inertia::render('Parent/Children/Index', [
                'children' => [],
                'message' => 'Data orang tua tidak ditemukan.',
            ]);
        }

        // Get all students linked ke guardian ini
        $children = $guardian->students()
            ->with(['guardians', 'primaryGuardian'])
            ->where('status', 'aktif')
            ->get();

        return Inertia::render('Parent/Children/Index', [
            'children' => $children,
        ]);
    }

    /**
     * Display detail profil anak untuk parent (read-only)
     * dengan validation bahwa parent hanya bisa view anak sendiri
     */
    public function show(Request $request, Student $student)
    {
        $user = $request->user();

        // Get guardian record untuk user ini
        $guardian = $user->guardian;

        if (! $guardian) {
            abort(403, 'Data orang tua tidak ditemukan.');
        }

        // Check apakah student ini adalah anak dari guardian ini
        $isOwnChild = $guardian->students()->where('students.id', $student->id)->exists();

        if (! $isOwnChild) {
            abort(403, 'Anda tidak memiliki akses ke data siswa ini.');
        }

        // Load relationships untuk detail view
        $student->load([
            'guardians',
            'primaryGuardian',
            'classHistory',
            // Note: Tidak load statusHistory karena parent tidak perlu lihat history internal
        ]);

        return Inertia::render('Parent/Children/Show', [
            'student' => $student,
        ]);
    }
}
