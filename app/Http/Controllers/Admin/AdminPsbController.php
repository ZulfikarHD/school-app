<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Psb\ApproveRegistrationRequest;
use App\Http\Requests\Psb\RejectRegistrationRequest;
use App\Http\Requests\Psb\RequestRevisionRequest;
use App\Models\AcademicYear;
use App\Models\PsbRegistration;
use App\Notifications\PsbDocumentRevisionRequested;
use App\Notifications\PsbRegistrationApproved;
use App\Notifications\PsbRegistrationRejected;
use App\Services\PsbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

/**
 * AdminPsbController - Controller untuk mengelola verifikasi pendaftaran PSB
 *
 * Controller ini bertujuan untuk menyediakan fungsi admin dalam memverifikasi
 * pendaftaran siswa baru, yaitu: dashboard, list registrations, approve,
 * reject, dan request revision dokumen
 */
class AdminPsbController extends Controller
{
    /**
     * Constructor untuk inject PsbService dependency
     * yang digunakan untuk business logic verifikasi PSB
     */
    public function __construct(
        protected PsbService $psbService
    ) {}

    /**
     * Dashboard PSB dengan statistik ringkasan pendaftaran
     * untuk overview status pendaftaran siswa baru
     */
    public function index(): Response
    {
        $stats = $this->psbService->getRegistrationStats();

        return Inertia::render('Admin/Psb/Index', [
            'title' => 'Dashboard PSB',
            'stats' => $stats,
        ]);
    }

    /**
     * Display list semua pendaftaran PSB dengan filter dan pagination
     * untuk Admin/TU melakukan verifikasi
     */
    public function registrations(Request $request): Response
    {
        $filters = [
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $registrations = $this->psbService->getRegistrations($filters);
        $stats = $this->psbService->getRegistrationStats();

        return Inertia::render('Admin/Psb/Registrations/Index', [
            'title' => 'Daftar Pendaftaran PSB',
            'registrations' => $registrations,
            'stats' => $stats,
            'filters' => $filters,
            'statuses' => PsbRegistration::getStatuses(),
        ]);
    }

    /**
     * Display detail pendaftaran dengan dokumen dan timeline
     * untuk review lengkap sebelum verifikasi
     */
    public function show(PsbRegistration $registration): Response
    {
        // Verifikasi registration dapat diakses oleh admin
        // Pastikan registration dari academic year yang aktif atau dapat diakses
        $activeYear = AcademicYear::where('is_active', true)->first();

        if ($activeYear && $registration->academic_year_id !== $activeYear->id) {
            abort(403, 'Anda tidak memiliki akses ke pendaftaran ini.');
        }

        $detail = $this->psbService->getRegistrationDetail($registration);

        return Inertia::render('Admin/Psb/Registrations/Show', [
            'title' => 'Detail Pendaftaran',
            'registration' => $detail['registration'],
            'documents' => $detail['documents'],
            'timeline' => $detail['timeline'],
        ]);
    }

    /**
     * Approve pendaftaran siswa baru
     * dengan update status dan kirim notifikasi ke orang tua
     */
    public function approve(ApproveRegistrationRequest $request, PsbRegistration $registration): RedirectResponse
    {
        try {
            $this->psbService->approveRegistration(
                $registration,
                auth()->user(),
                $request->validated('notes')
            );

            // Kirim notifikasi ke email orang tua
            $this->sendNotification($registration, new PsbRegistrationApproved($registration));

            return redirect()
                ->route('admin.psb.registrations.index')
                ->with('success', 'Pendaftaran berhasil disetujui.');
        } catch (\Exception $e) {
            Log::error('Failed to approve registration', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal menyetujui pendaftaran. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Reject pendaftaran siswa baru
     * dengan alasan penolakan dan kirim notifikasi ke orang tua
     */
    public function reject(RejectRegistrationRequest $request, PsbRegistration $registration): RedirectResponse
    {
        try {
            $this->psbService->rejectRegistration(
                $registration,
                auth()->user(),
                $request->validated('rejection_reason')
            );

            // Kirim notifikasi ke email orang tua
            $this->sendNotification($registration, new PsbRegistrationRejected($registration));

            return redirect()
                ->route('admin.psb.registrations.index')
                ->with('success', 'Pendaftaran berhasil ditolak.');
        } catch (\Exception $e) {
            Log::error('Failed to reject registration', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal menolak pendaftaran. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Request revision dokumen pendaftaran
     * dengan catatan revisi per dokumen dan kirim notifikasi ke orang tua
     */
    public function requestRevision(RequestRevisionRequest $request, PsbRegistration $registration): RedirectResponse
    {
        try {
            $this->psbService->requestDocumentRevision(
                $registration,
                $request->validated('documents')
            );

            // Kirim notifikasi ke email orang tua
            $this->sendNotification($registration, new PsbDocumentRevisionRequested($registration));

            return redirect()
                ->route('admin.psb.registrations.index')
                ->with('success', 'Permintaan revisi dokumen berhasil dikirim.');
        } catch (\Exception $e) {
            Log::error('Failed to request document revision', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengirim permintaan revisi. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Helper untuk kirim notifikasi ke email orang tua
     * menggunakan on-demand notification karena PSB tidak punya User account
     *
     * @param  PsbRegistration  $registration  Data pendaftaran
     * @param  mixed  $notification  Instance notification yang akan dikirim
     */
    protected function sendNotification(PsbRegistration $registration, $notification): void
    {
        // Prioritas kirim ke email ayah, fallback ke email ibu
        $email = $registration->father_email ?? $registration->mother_email;

        if ($email) {
            Notification::route('mail', $email)->notify($notification);
        }
    }
}
