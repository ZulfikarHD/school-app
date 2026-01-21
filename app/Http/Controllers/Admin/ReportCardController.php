<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateReportCardRequest;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Services\ReportCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ReportCardController - Controller untuk Admin mengelola rapor siswa
 * yang mencakup generate rapor, preview, unlock, dan download bulk
 */
class ReportCardController extends Controller
{
    public function __construct(
        protected ReportCardService $reportCardService
    ) {}

    /**
     * Menampilkan halaman index dengan list semua rapor
     * yang sudah di-generate beserta filter dan statistik
     */
    public function index(Request $request): Response
    {
        $tahunAjaran = $request->input('tahun_ajaran', $this->reportCardService->getCurrentTahunAjaran());
        $semester = $request->input('semester');
        $classId = $request->input('class_id');
        $status = $request->input('status');
        $search = $request->input('search');

        // Build query untuk report cards
        $query = ReportCard::query()
            ->with(['student:id,nis,nama_lengkap', 'class', 'generatedByUser:id,name'])
            ->byTahunAjaran($tahunAjaran);

        if ($semester) {
            $query->bySemester($semester);
        }

        if ($classId) {
            $query->byClass($classId);
        }

        if ($status) {
            $query->byStatus($status);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $reportCards = $query
            ->orderByDesc('generated_at')
            ->paginate(20)
            ->withQueryString();

        // Transform data untuk frontend
        $reportCards->through(function ($rc) {
            return [
                'id' => $rc->id,
                'student_id' => $rc->student_id,
                'student_name' => $rc->student?->nama_lengkap ?? '-',
                'student_nis' => $rc->student?->nis ?? '-',
                'class_id' => $rc->class_id,
                'class_name' => $rc->class?->nama_lengkap ?? '-',
                'tahun_ajaran' => $rc->tahun_ajaran,
                'semester' => $rc->semester,
                'semester_label' => $rc->semester === '1' ? 'Ganjil' : 'Genap',
                'status' => $rc->status,
                'status_label' => $rc->status_label,
                'status_color' => $rc->status_color,
                'average_score' => $rc->average_score,
                'rank' => $rc->rank,
                'generated_at' => $rc->generated_at?->format('d M Y H:i'),
                'generated_by' => $rc->generatedByUser?->name ?? '-',
                'has_pdf' => $rc->pdf_path && Storage::exists($rc->pdf_path),
            ];
        });

        // Get filter options
        $classes = SchoolClass::query()
            ->active()
            ->byAcademicYear($tahunAjaran)
            ->withCount('students')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->nama_lengkap,
                'student_count' => $c->students_count,
            ]);

        // Get statistics
        $stats = $this->getReportCardStatistics($tahunAjaran, $semester, $classId);

        return Inertia::render('Admin/ReportCards/Index', [
            'reportCards' => $reportCards,
            'filters' => [
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'class_id' => $classId,
                'status' => $status,
                'search' => $search,
            ],
            'classes' => $classes,
            'statusOptions' => ReportCard::getStatusOptions(),
            'statistics' => $stats,
            'availableTahunAjaran' => $this->reportCardService->getAvailableTahunAjaran() ?: [$this->reportCardService->getCurrentTahunAjaran()],
        ]);
    }

    /**
     * Menampilkan halaman wizard untuk generate rapor
     */
    public function generate(Request $request): Response
    {
        $tahunAjaran = $request->input('tahun_ajaran', $this->reportCardService->getCurrentTahunAjaran());
        $semester = $request->input('semester', $this->reportCardService->getCurrentSemester());

        // Get classes with student count dan wali kelas
        $classes = SchoolClass::query()
            ->active()
            ->byAcademicYear($tahunAjaran)
            ->with('waliKelas:id,name')
            ->withCount(['students' => function ($q) {
                $q->where('status', 'aktif');
            }])
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->nama_lengkap,
                'tingkat' => $c->tingkat,
                'wali_kelas' => $c->waliKelas?->name ?? '-',
                'student_count' => $c->students_count,
            ]);

        return Inertia::render('Admin/ReportCards/Generate', [
            'classes' => $classes,
            'defaultFilters' => [
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
            ],
            'availableTahunAjaran' => $this->reportCardService->getAvailableTahunAjaran() ?: [$this->reportCardService->getCurrentTahunAjaran()],
        ]);
    }

    /**
     * API: Validasi kelengkapan data sebelum generate
     */
    public function validateCompleteness(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'tahun_ajaran' => 'required|string',
            'semester' => 'required|in:1,2',
        ]);

        $validation = $this->reportCardService->validateCompleteness(
            $request->input('class_id'),
            $request->input('tahun_ajaran'),
            $request->input('semester')
        );

        return response()->json($validation);
    }

    /**
     * Process generate rapor untuk kelas yang dipilih
     */
    public function processGenerate(GenerateReportCardRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        $results = [];
        $totalGenerated = 0;
        $totalFailed = 0;

        foreach ($validated['class_ids'] as $classId) {
            $result = $this->reportCardService->generateBulk(
                $classId,
                $validated['tahun_ajaran'],
                $validated['semester'],
                $user->id
            );

            $class = SchoolClass::find($classId);

            $results[] = [
                'class_id' => $classId,
                'class_name' => $class?->nama_lengkap ?? '-',
                'generated_count' => $result['generated_count'],
                'failed_count' => $result['failed_count'],
                'success' => $result['success'],
            ];

            $totalGenerated += $result['generated_count'];
            $totalFailed += $result['failed_count'];
        }

        return response()->json([
            'success' => $totalFailed === 0,
            'message' => "{$totalGenerated} rapor berhasil di-generate".($totalFailed > 0 ? ", {$totalFailed} gagal" : ''),
            'total_generated' => $totalGenerated,
            'total_failed' => $totalFailed,
            'results' => $results,
        ]);
    }

    /**
     * Menampilkan preview rapor siswa (HTML)
     */
    public function show(ReportCard $reportCard): Response
    {
        $data = $this->reportCardService->getReportCardData($reportCard);

        return Inertia::render('Admin/ReportCards/Show', [
            'reportCard' => array_merge($data, [
                'id' => $reportCard->id,
                'status' => $reportCard->status,
                'status_label' => $reportCard->status_label,
                'status_color' => $reportCard->status_color,
                'is_editable' => $reportCard->isEditable(),
                'has_pdf' => $reportCard->pdf_path && Storage::exists($reportCard->pdf_path),
            ]),
        ]);
    }

    /**
     * Download PDF rapor siswa
     */
    public function downloadPdf(ReportCard $reportCard): BinaryFileResponse|RedirectResponse
    {
        if (! $reportCard->pdf_path || ! Storage::exists($reportCard->pdf_path)) {
            return back()->with('error', 'File PDF tidak ditemukan.');
        }

        $filename = "rapor_{$reportCard->student->nis}_{$reportCard->student->nama_lengkap}.pdf";
        $filename = str_replace(['/', '\\', ' '], ['_', '_', '_'], $filename);

        return response()->download(
            Storage::path($reportCard->pdf_path),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Unlock rapor untuk koreksi nilai
     */
    public function unlock(ReportCard $reportCard): RedirectResponse
    {
        if ($reportCard->status !== ReportCard::STATUS_DRAFT) {
            return back()->with('error', 'Hanya rapor dengan status Draft yang dapat di-unlock.');
        }

        $this->reportCardService->unlockReportCard($reportCard);

        return back()->with('success', 'Rapor berhasil di-unlock. Nilai dapat dikoreksi kembali.');
    }

    /**
     * Download bulk rapor dalam format ZIP
     */
    public function downloadZip(Request $request): StreamedResponse|RedirectResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'tahun_ajaran' => 'required|string',
            'semester' => 'required|in:1,2',
        ]);

        $zipPath = $this->reportCardService->createBulkZip(
            $request->input('class_id'),
            $request->input('tahun_ajaran'),
            $request->input('semester')
        );

        if (! $zipPath) {
            return back()->with('error', 'Tidak ada rapor PDF yang tersedia untuk di-download.');
        }

        $class = SchoolClass::find($request->input('class_id'));
        $filename = "rapor_{$class->nama_lengkap}_{$request->input('tahun_ajaran')}_{$request->input('semester')}.zip";
        $filename = str_replace(['/', ' '], ['_', '_'], $filename);

        return response()->streamDownload(function () use ($zipPath) {
            readfile(Storage::path($zipPath));
        }, $filename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(false);
    }

    /**
     * Regenerate PDF untuk rapor tertentu
     */
    public function regenerate(ReportCard $reportCard): RedirectResponse
    {
        if ($reportCard->status !== ReportCard::STATUS_DRAFT) {
            return back()->with('error', 'Hanya rapor dengan status Draft yang dapat di-regenerate.');
        }

        try {
            $pdfPath = $this->reportCardService->generatePDF($reportCard);
            $reportCard->update([
                'pdf_path' => $pdfPath,
                'generated_at' => now(),
                'generated_by' => auth()->id(),
            ]);

            return back()->with('success', 'PDF rapor berhasil di-regenerate.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal regenerate PDF: '.$e->getMessage());
        }
    }

    /**
     * Helper: Mendapatkan statistik rapor
     */
    protected function getReportCardStatistics(string $tahunAjaran, ?string $semester, ?int $classId): array
    {
        $query = ReportCard::query()->byTahunAjaran($tahunAjaran);

        if ($semester) {
            $query->bySemester($semester);
        }

        if ($classId) {
            $query->byClass($classId);
        }

        $total = (clone $query)->count();
        $draft = (clone $query)->where('status', ReportCard::STATUS_DRAFT)->count();
        $pending = (clone $query)->where('status', ReportCard::STATUS_PENDING_APPROVAL)->count();
        $approved = (clone $query)->where('status', ReportCard::STATUS_APPROVED)->count();
        $released = (clone $query)->where('status', ReportCard::STATUS_RELEASED)->count();

        return [
            'total' => $total,
            'draft' => $draft,
            'pending_approval' => $pending,
            'approved' => $approved,
            'released' => $released,
        ];
    }
}
