<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Services\NotificationService;
use App\Services\ReportCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * ReportCardController - Controller untuk Principal mengelola approval rapor
 * yang mencakup review, approve, reject, dan bulk approve rapor siswa
 */
class ReportCardController extends Controller
{
    public function __construct(
        protected ReportCardService $reportCardService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Menampilkan list rapor yang menunggu approval
     * dengan grouping per kelas dan statistik
     */
    public function index(Request $request): Response
    {
        $tahunAjaran = $request->input('tahun_ajaran', $this->reportCardService->getCurrentTahunAjaran());
        $semester = $request->input('semester');
        $classId = $request->input('class_id');
        $status = $request->input('status', ReportCard::STATUS_PENDING_APPROVAL);

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

        $reportCards = $query
            ->orderBy('class_id')
            ->orderBy('rank')
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

        // Get filter options - kelas aktif
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

        // Get statistics per status
        $stats = $this->getStatistics($tahunAjaran, $semester, $classId);

        // Get pending per kelas untuk summary
        $pendingByClass = $this->getPendingByClass($tahunAjaran, $semester);

        return Inertia::render('Principal/ReportCards/Index', [
            'reportCards' => $reportCards,
            'filters' => [
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'class_id' => $classId,
                'status' => $status,
            ],
            'classes' => $classes,
            'statusOptions' => ReportCard::getStatusOptions(),
            'statistics' => $stats,
            'pendingByClass' => $pendingByClass,
            'availableTahunAjaran' => $this->reportCardService->getAvailableTahunAjaran() ?: [$this->reportCardService->getCurrentTahunAjaran()],
            'semesters' => [
                ['value' => '1', 'label' => 'Semester 1 (Ganjil)'],
                ['value' => '2', 'label' => 'Semester 2 (Genap)'],
            ],
        ]);
    }

    /**
     * Menampilkan preview rapor untuk approval
     */
    public function show(ReportCard $reportCard): Response
    {
        $data = $this->reportCardService->getReportCardData($reportCard);

        // Get list siswa di kelas yang sama untuk navigasi
        $classmateReportCards = ReportCard::query()
            ->with('student:id,nis,nama_lengkap')
            ->byClass($reportCard->class_id)
            ->byTahunAjaran($reportCard->tahun_ajaran)
            ->bySemester($reportCard->semester)
            ->orderBy('rank')
            ->get()
            ->map(fn ($rc) => [
                'id' => $rc->id,
                'student_name' => $rc->student?->nama_lengkap ?? '-',
                'student_nis' => $rc->student?->nis ?? '-',
                'status' => $rc->status,
                'status_label' => $rc->status_label,
                'is_current' => $rc->id === $reportCard->id,
            ]);

        return Inertia::render('Principal/ReportCards/Show', [
            'reportCard' => array_merge($data, [
                'id' => $reportCard->id,
                'status' => $reportCard->status,
                'status_label' => $reportCard->status_label,
                'status_color' => $reportCard->status_color,
                'is_approvable' => $reportCard->isApprovable(),
                'approval_notes' => $reportCard->approval_notes,
                'has_pdf' => $reportCard->pdf_path && Storage::exists($reportCard->pdf_path),
            ]),
            'classmateReportCards' => $classmateReportCards,
        ]);
    }

    /**
     * Approve rapor dan release ke parent
     * Status: PENDING_APPROVAL -> APPROVED -> RELEASED
     */
    public function approve(Request $request, ReportCard $reportCard): RedirectResponse
    {
        if (! $reportCard->isApprovable()) {
            return back()->with('error', 'Rapor ini tidak dapat di-approve. Status saat ini: '.$reportCard->status_label);
        }

        $notes = $request->input('notes');
        $principal = auth()->user();

        DB::beginTransaction();
        try {
            // Approve rapor
            $reportCard->approve($principal->id, $notes);

            // Release rapor langsung setelah approve
            $reportCard->release();

            // Queue notification untuk parent
            $this->notificationService->queueReportCardReleasedNotification($reportCard);

            DB::commit();

            return back()->with('success', 'Rapor berhasil di-approve dan dirilis ke orang tua.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', 'Gagal approve rapor: '.$e->getMessage());
        }
    }

    /**
     * Reject rapor dengan notes (kembali ke DRAFT)
     */
    public function reject(Request $request, ReportCard $reportCard): RedirectResponse
    {
        $request->validate([
            'notes' => 'required|string|max:500',
        ], [
            'notes.required' => 'Catatan alasan penolakan wajib diisi.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ]);

        if (! $reportCard->isApprovable()) {
            return back()->with('error', 'Rapor ini tidak dapat di-reject. Status saat ini: '.$reportCard->status_label);
        }

        $principal = auth()->user();

        $reportCard->reject($principal->id, $request->input('notes'));

        return back()->with('success', 'Rapor dikembalikan ke wali kelas untuk diperbaiki.');
    }

    /**
     * Bulk approve rapor per kelas
     * Approve semua rapor dengan status PENDING_APPROVAL dalam satu kelas
     */
    public function bulkApprove(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'tahun_ajaran' => 'required|string',
            'semester' => 'required|in:1,2',
        ]);

        $classId = $request->input('class_id');
        $tahunAjaran = $request->input('tahun_ajaran');
        $semester = $request->input('semester');
        $principal = auth()->user();

        $reportCards = ReportCard::query()
            ->byClass($classId)
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester)
            ->pendingApproval()
            ->get();

        if ($reportCards->isEmpty()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada rapor yang menunggu approval.',
                ], 400);
            }

            return back()->with('error', 'Tidak ada rapor yang menunggu approval.');
        }

        DB::beginTransaction();
        try {
            $approvedCount = 0;

            foreach ($reportCards as $reportCard) {
                $reportCard->approve($principal->id);
                $reportCard->release();

                // Queue notification
                $this->notificationService->queueReportCardReleasedNotification($reportCard);

                $approvedCount++;
            }

            DB::commit();

            $message = "{$approvedCount} rapor berhasil di-approve dan dirilis ke orang tua.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'approved_count' => $approvedCount,
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal approve rapor: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal approve rapor: '.$e->getMessage());
        }
    }

    /**
     * Helper: Mendapatkan statistik rapor
     */
    protected function getStatistics(string $tahunAjaran, ?string $semester, ?int $classId): array
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

    /**
     * Helper: Mendapatkan jumlah rapor pending per kelas
     */
    protected function getPendingByClass(string $tahunAjaran, ?string $semester): array
    {
        $query = ReportCard::query()
            ->select('class_id', DB::raw('COUNT(*) as pending_count'))
            ->byTahunAjaran($tahunAjaran)
            ->pendingApproval()
            ->groupBy('class_id');

        if ($semester) {
            $query->bySemester($semester);
        }

        $results = $query->get();

        // Join with class names
        $classIds = $results->pluck('class_id')->toArray();
        $classes = SchoolClass::whereIn('id', $classIds)->get()->keyBy('id');

        return $results->map(fn ($item) => [
            'class_id' => $item->class_id,
            'class_name' => $classes[$item->class_id]?->nama_lengkap ?? '-',
            'pending_count' => $item->pending_count,
        ])->toArray();
    }
}
