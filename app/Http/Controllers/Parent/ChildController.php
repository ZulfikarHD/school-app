<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Models\Student;
use App\Services\AttendanceService;
use App\Services\GradeCalculationService;
use App\Services\ReportCardService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChildController extends Controller
{
    /**
     * Constructor untuk inject dependencies
     */
    public function __construct(
        protected AttendanceService $attendanceService,
        protected GradeCalculationService $gradeCalculationService,
        protected ReportCardService $reportCardService
    ) {}

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
            'classHistory.kelas',
            // Note: Tidak load statusHistory karena parent tidak perlu lihat history internal
        ]);

        return Inertia::render('Parent/Children/Show', [
            'student' => $student,
        ]);
    }

    /**
     * Display attendance history untuk anak
     * dengan calendar view dan summary statistics
     */
    public function attendance(Request $request, Student $student)
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

        // Get date range from request or default to current month
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Get attendance report
        $report = $this->attendanceService->getStudentAttendanceReport(
            $student->id,
            $startDate,
            $endDate
        );

        // Load student with class info
        $student->load('kelas');

        return Inertia::render('Parent/Children/Attendance', [
            'title' => 'Riwayat Kehadiran',
            'student' => $student,
            'attendances' => $report['details'],
            'summary' => $report['summary'],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Export attendance report to PDF untuk parent
     *
     * TODO Sprint 2: Implement PDF export
     */
    public function exportAttendance(Request $request, Student $student)
    {
        // This will be implemented in Phase 5: Export Functionality
        return response()->json([
            'message' => 'Export functionality will be implemented in Phase 5',
        ]);
    }

    /**
     * Menampilkan rekap nilai anak dengan breakdown per komponen
     * Parent hanya bisa melihat nilai anak sendiri
     */
    public function grades(Request $request, Student $student): Response
    {
        // Authorize: pastikan student adalah anak dari parent ini
        $this->authorizeChild($request, $student);

        $tahunAjaran = $request->input('tahun_ajaran', $this->reportCardService->getCurrentTahunAjaran());
        $semester = $request->input('semester', $this->reportCardService->getCurrentSemester());

        // Load student dengan kelas
        $student->load('kelas');

        // Get grade summary menggunakan GradeCalculationService
        $gradeSummary = [];
        if ($student->kelas) {
            $gradeSummary = $this->gradeCalculationService->getStudentGradeSummary(
                $student->id,
                $tahunAjaran,
                $semester
            );
        }

        return Inertia::render('Parent/Children/Grades', [
            'title' => 'Rekap Nilai - '.$student->nama_lengkap,
            'student' => [
                'id' => $student->id,
                'nama_lengkap' => $student->nama_lengkap,
                'nis' => $student->nis,
                'foto' => $student->foto,
                'kelas' => $student->kelas ? [
                    'id' => $student->kelas->id,
                    'nama_lengkap' => $student->kelas->nama_lengkap,
                ] : null,
            ],
            'gradeSummary' => $gradeSummary,
            'filters' => [
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
            ],
            'availableTahunAjaran' => $this->reportCardService->getAvailableTahunAjaran() ?: [$this->reportCardService->getCurrentTahunAjaran()],
            'semesters' => [
                ['value' => '1', 'label' => 'Semester 1 (Ganjil)'],
                ['value' => '2', 'label' => 'Semester 2 (Genap)'],
            ],
        ]);
    }

    /**
     * Menampilkan list rapor anak per semester
     * Parent hanya bisa melihat rapor yang sudah RELEASED
     */
    public function reportCards(Request $request, Student $student): Response
    {
        // Authorize: pastikan student adalah anak dari parent ini
        $this->authorizeChild($request, $student);

        // Load student dengan kelas
        $student->load('kelas');

        // Get report cards yang sudah RELEASED untuk anak ini
        $reportCards = ReportCard::query()
            ->with('class:id,nama,tingkat')
            ->where('student_id', $student->id)
            ->released()
            ->orderByDesc('tahun_ajaran')
            ->orderByDesc('semester')
            ->get()
            ->map(fn ($rc) => [
                'id' => $rc->id,
                'tahun_ajaran' => $rc->tahun_ajaran,
                'semester' => $rc->semester,
                'semester_label' => $rc->semester === '1' ? 'Ganjil' : 'Genap',
                'class_name' => $rc->class?->nama_lengkap ?? '-',
                'status' => $rc->status,
                'status_label' => $rc->status_label,
                'status_color' => $rc->status_color,
                'average_score' => $rc->average_score,
                'rank' => $rc->rank,
                'released_at' => $rc->released_at?->format('d M Y'),
                'has_pdf' => $rc->pdf_path && Storage::exists($rc->pdf_path),
            ]);

        return Inertia::render('Parent/Children/ReportCards/Index', [
            'title' => 'Rapor - '.$student->nama_lengkap,
            'student' => [
                'id' => $student->id,
                'nama_lengkap' => $student->nama_lengkap,
                'nis' => $student->nis,
                'foto' => $student->foto,
                'kelas' => $student->kelas ? [
                    'id' => $student->kelas->id,
                    'nama_lengkap' => $student->kelas->nama_lengkap,
                ] : null,
            ],
            'reportCards' => $reportCards,
        ]);
    }

    /**
     * Menampilkan detail rapor anak (HTML view)
     * Parent hanya bisa melihat rapor yang sudah RELEASED
     */
    public function showReportCard(Request $request, Student $student, ReportCard $reportCard): Response|RedirectResponse
    {
        // Authorize: pastikan student adalah anak dari parent ini
        $this->authorizeChild($request, $student);

        // Pastikan report card milik student ini
        if ($reportCard->student_id !== $student->id) {
            abort(403, 'Rapor ini bukan milik anak Anda.');
        }

        // Pastikan report card sudah RELEASED
        if (! $reportCard->isReleased()) {
            return redirect()->route('parent.children.report-cards.index', $student->id)
                ->with('error', 'Rapor belum tersedia untuk dilihat.');
        }

        // Get full report card data
        $data = $this->reportCardService->getReportCardData($reportCard);

        return Inertia::render('Parent/Children/ReportCards/Show', [
            'title' => 'Rapor '.$data['academic']['semester_label'].' - '.$student->nama_lengkap,
            'student' => [
                'id' => $student->id,
                'nama_lengkap' => $student->nama_lengkap,
                'nis' => $student->nis,
            ],
            'reportCard' => array_merge($data, [
                'id' => $reportCard->id,
                'status' => $reportCard->status,
                'status_label' => $reportCard->status_label,
                'has_pdf' => $reportCard->pdf_path && Storage::exists($reportCard->pdf_path),
            ]),
        ]);
    }

    /**
     * Download PDF rapor anak
     * Parent hanya bisa download rapor yang sudah RELEASED
     */
    public function downloadReportCard(Request $request, Student $student, ReportCard $reportCard): BinaryFileResponse|RedirectResponse
    {
        // Authorize: pastikan student adalah anak dari parent ini
        $this->authorizeChild($request, $student);

        // Pastikan report card milik student ini
        if ($reportCard->student_id !== $student->id) {
            abort(403, 'Rapor ini bukan milik anak Anda.');
        }

        // Pastikan report card sudah RELEASED
        if (! $reportCard->isReleased()) {
            return redirect()->route('parent.children.report-cards.index', $student->id)
                ->with('error', 'Rapor belum tersedia untuk di-download.');
        }

        // Check PDF exists
        if (! $reportCard->pdf_path || ! Storage::exists($reportCard->pdf_path)) {
            return redirect()->route('parent.children.report-cards.show', [$student->id, $reportCard->id])
                ->with('error', 'File PDF tidak ditemukan.');
        }

        $filename = "rapor_{$student->nis}_{$student->nama_lengkap}_{$reportCard->tahun_ajaran}_sem{$reportCard->semester}.pdf";
        $filename = str_replace(['/', '\\', ' '], ['_', '_', '_'], $filename);

        return response()->download(
            Storage::path($reportCard->pdf_path),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Helper: Authorize bahwa student adalah anak dari parent yang login
     */
    protected function authorizeChild(Request $request, Student $student): void
    {
        $user = $request->user();
        $guardian = $user->guardian;

        if (! $guardian) {
            abort(403, 'Data orang tua tidak ditemukan.');
        }

        $isOwnChild = $guardian->students()->where('students.id', $student->id)->exists();

        if (! $isOwnChild) {
            abort(403, 'Anda tidak memiliki akses ke data siswa ini.');
        }
    }
}
