<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateHomeroomNotesRequest;
use App\Models\AttitudeGrade;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Services\ReportCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ReportCardController - Controller untuk Wali Kelas mengelola rapor
 * kelas yang mereka pegang, termasuk input catatan dan submit approval
 */
class ReportCardController extends Controller
{
    public function __construct(
        protected ReportCardService $reportCardService
    ) {}

    /**
     * Menampilkan list rapor untuk kelas yang dipegang wali kelas
     */
    public function index(Request $request): Response
    {
        $teacher = auth()->user();
        $tahunAjaran = $request->input('tahun_ajaran', $this->reportCardService->getCurrentTahunAjaran());
        $semester = $request->input('semester', $this->reportCardService->getCurrentSemester());

        // Get kelas yang dipegang sebagai wali kelas
        $waliKelasClass = $this->getWaliKelasClass($teacher);

        if (! $waliKelasClass) {
            return Inertia::render('Teacher/ReportCards/Index', [
                'title' => 'Rapor Kelas',
                'isWaliKelas' => false,
                'classData' => null,
                'reportCards' => [],
                'filters' => [
                    'tahun_ajaran' => $tahunAjaran,
                    'semester' => $semester,
                ],
                'statistics' => null,
            ]);
        }

        // Get report cards untuk kelas ini
        $reportCards = ReportCard::query()
            ->with(['student:id,nis,nama_lengkap,foto'])
            ->byClass($waliKelasClass->id)
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester)
            ->orderBy('rank')
            ->get()
            ->map(function ($rc) use ($tahunAjaran, $semester) {
                return [
                    'id' => $rc->id,
                    'student_id' => $rc->student_id,
                    'student_name' => $rc->student?->nama_lengkap ?? '-',
                    'student_nis' => $rc->student?->nis ?? '-',
                    'student_foto' => $rc->student?->foto ?? null,
                    'status' => $rc->status,
                    'status_label' => $rc->status_label,
                    'status_color' => $rc->status_color,
                    'average_score' => $rc->average_score,
                    'rank' => $rc->rank,
                    'has_notes' => $this->studentHasNotes($rc->student_id, $tahunAjaran, $semester),
                    'generated_at' => $rc->generated_at?->format('d M Y H:i'),
                ];
            });

        // Statistics
        $stats = [
            'total' => $reportCards->count(),
            'draft' => $reportCards->where('status', ReportCard::STATUS_DRAFT)->count(),
            'pending_approval' => $reportCards->where('status', ReportCard::STATUS_PENDING_APPROVAL)->count(),
            'approved' => $reportCards->where('status', ReportCard::STATUS_APPROVED)->count(),
            'released' => $reportCards->where('status', ReportCard::STATUS_RELEASED)->count(),
            'with_notes' => $reportCards->where('has_notes', true)->count(),
        ];

        return Inertia::render('Teacher/ReportCards/Index', [
            'title' => 'Rapor Kelas',
            'isWaliKelas' => true,
            'classData' => [
                'id' => $waliKelasClass->id,
                'nama_lengkap' => $waliKelasClass->nama_lengkap,
                'tingkat' => $waliKelasClass->tingkat,
                'tahun_ajaran' => $waliKelasClass->tahun_ajaran,
            ],
            'reportCards' => $reportCards,
            'filters' => [
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
            ],
            'statistics' => $stats,
            'semesters' => [
                ['value' => '1', 'label' => 'Semester 1 (Ganjil)'],
                ['value' => '2', 'label' => 'Semester 2 (Genap)'],
            ],
        ]);
    }

    /**
     * Menampilkan detail rapor siswa dengan form input catatan
     */
    public function show(ReportCard $reportCard): Response
    {
        $teacher = auth()->user();
        $waliKelasClass = $this->getWaliKelasClass($teacher);

        // Authorization: hanya wali kelas yang bisa akses
        if (! $waliKelasClass || $reportCard->class_id !== $waliKelasClass->id) {
            abort(403, 'Anda tidak memiliki akses ke rapor ini.');
        }

        $data = $this->reportCardService->getReportCardData($reportCard);

        // Get existing attitude grade untuk catatan
        $attitudeGrade = AttitudeGrade::query()
            ->byStudent($reportCard->student_id)
            ->byTahunAjaran($reportCard->tahun_ajaran)
            ->bySemester($reportCard->semester)
            ->first();

        return Inertia::render('Teacher/ReportCards/Show', [
            'title' => 'Detail Rapor - '.$data['student']['nama_lengkap'],
            'reportCard' => array_merge($data, [
                'id' => $reportCard->id,
                'status' => $reportCard->status,
                'status_label' => $reportCard->status_label,
                'status_color' => $reportCard->status_color,
                'is_editable' => $reportCard->isEditable(),
                'has_pdf' => $reportCard->pdf_path && Storage::exists($reportCard->pdf_path),
            ]),
            'attitudeGradeId' => $attitudeGrade?->id,
            'currentNotes' => $attitudeGrade?->homeroom_notes ?? '',
            'classData' => [
                'id' => $waliKelasClass->id,
                'nama_lengkap' => $waliKelasClass->nama_lengkap,
            ],
        ]);
    }

    /**
     * Update catatan wali kelas untuk rapor siswa
     */
    public function updateNotes(UpdateHomeroomNotesRequest $request, ReportCard $reportCard): RedirectResponse
    {
        $teacher = auth()->user();
        $waliKelasClass = $this->getWaliKelasClass($teacher);

        // Authorization
        if (! $waliKelasClass || $reportCard->class_id !== $waliKelasClass->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah catatan ini.');
        }

        // Update homeroom notes di attitude_grades
        AttitudeGrade::updateOrCreate(
            [
                'student_id' => $reportCard->student_id,
                'tahun_ajaran' => $reportCard->tahun_ajaran,
                'semester' => $reportCard->semester,
            ],
            [
                'class_id' => $reportCard->class_id,
                'teacher_id' => $teacher->id,
                'homeroom_notes' => $request->validated()['homeroom_notes'],
            ]
        );

        // Regenerate PDF jika rapor sudah di-generate
        if ($reportCard->pdf_path) {
            try {
                $pdfPath = $this->reportCardService->generatePDF($reportCard);
                $reportCard->update(['pdf_path' => $pdfPath]);
            } catch (\Exception $e) {
                // Log error but don't fail the request
                report($e);
            }
        }

        return back()->with('success', 'Catatan wali kelas berhasil disimpan.');
    }

    /**
     * Submit rapor untuk approval oleh kepala sekolah
     */
    public function submitForApproval(ReportCard $reportCard): RedirectResponse
    {
        $teacher = auth()->user();
        $waliKelasClass = $this->getWaliKelasClass($teacher);

        // Authorization
        if (! $waliKelasClass || $reportCard->class_id !== $waliKelasClass->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk submit rapor ini.');
        }

        if ($reportCard->status !== ReportCard::STATUS_DRAFT) {
            return back()->with('error', 'Hanya rapor dengan status Draft yang dapat disubmit.');
        }

        $reportCard->submitForApproval($teacher->id);

        return back()->with('success', 'Rapor berhasil disubmit untuk persetujuan kepala sekolah.');
    }

    /**
     * Submit semua rapor kelas untuk approval
     */
    public function submitAllForApproval(Request $request): RedirectResponse
    {
        $teacher = auth()->user();
        $waliKelasClass = $this->getWaliKelasClass($teacher);

        if (! $waliKelasClass) {
            return back()->with('error', 'Anda bukan wali kelas aktif.');
        }

        $tahunAjaran = $request->input('tahun_ajaran', $this->reportCardService->getCurrentTahunAjaran());
        $semester = $request->input('semester', $this->reportCardService->getCurrentSemester());

        $updated = ReportCard::query()
            ->byClass($waliKelasClass->id)
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester)
            ->where('status', ReportCard::STATUS_DRAFT)
            ->update([
                'status' => ReportCard::STATUS_PENDING_APPROVAL,
                'generated_at' => now(),
                'generated_by' => $teacher->id,
            ]);

        if ($updated === 0) {
            return back()->with('error', 'Tidak ada rapor Draft yang dapat disubmit.');
        }

        return back()->with('success', "{$updated} rapor berhasil disubmit untuk persetujuan kepala sekolah.");
    }

    /**
     * Download PDF rapor siswa
     */
    public function downloadPdf(ReportCard $reportCard): BinaryFileResponse|RedirectResponse
    {
        $teacher = auth()->user();
        $waliKelasClass = $this->getWaliKelasClass($teacher);

        // Authorization
        if (! $waliKelasClass || $reportCard->class_id !== $waliKelasClass->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk download rapor ini.');
        }

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
     * Get kelas dimana teacher menjadi wali kelas
     */
    protected function getWaliKelasClass($teacher): ?SchoolClass
    {
        $tahunAjaran = $this->reportCardService->getCurrentTahunAjaran();

        return SchoolClass::where('wali_kelas_id', $teacher->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Check apakah siswa sudah memiliki catatan wali kelas
     */
    protected function studentHasNotes(int $studentId, string $tahunAjaran, string $semester): bool
    {
        return AttitudeGrade::query()
            ->byStudent($studentId)
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester)
            ->whereNotNull('homeroom_notes')
            ->where('homeroom_notes', '!=', '')
            ->exists();
    }
}
