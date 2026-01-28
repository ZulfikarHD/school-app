<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Psb\BulkAnnounceRequest;
use App\Models\PsbRegistration;
use App\Notifications\PsbAnnouncementNotification;
use App\Services\PsbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

/**
 * AdminPsbAnnouncementController - Controller untuk mengelola pengumuman PSB
 *
 * Controller ini bertujuan untuk menyediakan fungsi admin dalam mengumumkan
 * pendaftaran siswa baru yang sudah disetujui secara bulk
 */
class AdminPsbAnnouncementController extends Controller
{
    /**
     * Constructor untuk inject PsbService dependency
     * yang digunakan untuk business logic pengumuman PSB
     */
    public function __construct(
        protected PsbService $psbService
    ) {}

    /**
     * Display list pendaftaran yang sudah approved dan siap diumumkan
     * untuk Admin melakukan bulk announce
     */
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->input('search'),
            'announced' => $request->input('announced'),
        ];

        $registrations = $this->psbService->getApprovedRegistrations($filters);
        $stats = $this->psbService->getRegistrationStats();

        return Inertia::render('Admin/Psb/Announcements/Index', [
            'title' => 'Pengumuman PSB',
            'registrations' => $registrations,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    /**
     * Bulk announce registrations
     * Update announced_at dan kirim notifikasi ke semua yang dipilih
     */
    public function bulkAnnounce(BulkAnnounceRequest $request): RedirectResponse
    {
        try {
            $registrationIds = $request->validated('registration_ids');
            $sendNotification = $request->validated('send_notification', true);

            // Get registrations before announce untuk notifikasi
            $registrations = PsbRegistration::whereIn('id', $registrationIds)
                ->where('status', PsbRegistration::STATUS_APPROVED)
                ->whereNull('announced_at')
                ->get();

            if ($registrations->isEmpty()) {
                return back()->with('error', 'Tidak ada pendaftaran yang dapat diumumkan. Pastikan pendaftaran belum diumumkan sebelumnya.');
            }

            // Bulk update announced_at
            $count = $this->psbService->bulkAnnounce($registrationIds);

            // Send notifications jika diminta
            if ($sendNotification && $count > 0) {
                $this->sendBulkNotifications($registrations);
            }

            return redirect()
                ->route('admin.psb.announcements.index')
                ->with('success', "{$count} pendaftaran berhasil diumumkan.");
        } catch (\Exception $e) {
            Log::error('Failed to bulk announce registrations', [
                'registration_count' => count($request->validated('registration_ids')),
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengumumkan pendaftaran. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Send bulk notifications ke semua pendaftar yang diumumkan
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $registrations  Collection of PsbRegistration
     */
    protected function sendBulkNotifications($registrations): void
    {
        foreach ($registrations as $registration) {
            // Prioritas kirim ke email ayah, fallback ke email ibu
            $email = $registration->father_email ?? $registration->mother_email;

            if ($email) {
                try {
                    Notification::route('mail', $email)
                        ->notify(new PsbAnnouncementNotification($registration));
                } catch (\Exception $e) {
                    Log::warning('Failed to send announcement notification', [
                        'registration_id' => $registration->id,
                        'email' => $email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
