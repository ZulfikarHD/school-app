<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Psb\VerifyPaymentRequest;
use App\Models\PsbPayment;
use App\Notifications\PsbPaymentVerified;
use App\Services\PsbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

/**
 * AdminPsbPaymentController - Controller untuk mengelola verifikasi pembayaran PSB
 *
 * Controller ini bertujuan untuk menyediakan fungsi admin dalam memverifikasi
 * pembayaran daftar ulang siswa baru (approve/reject)
 */
class AdminPsbPaymentController extends Controller
{
    /**
     * Constructor untuk inject PsbService dependency
     * yang digunakan untuk business logic verifikasi pembayaran PSB
     */
    public function __construct(
        protected PsbService $psbService
    ) {}

    /**
     * Display list pembayaran PSB untuk verifikasi admin
     * dengan filter status dan pencarian
     */
    public function index(Request $request): Response
    {
        $filters = [
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        $payments = $this->psbService->getPendingPayments($filters);
        $stats = $this->psbService->getRegistrationStats();

        // Get status options
        $statuses = PsbPayment::getStatuses();

        return Inertia::render('Admin/Psb/Payments/Index', [
            'title' => 'Verifikasi Pembayaran PSB',
            'payments' => $payments,
            'stats' => $stats,
            'filters' => $filters,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Verify payment (approve/reject)
     * Update status pembayaran dan trigger student creation jika approved
     */
    public function verify(VerifyPaymentRequest $request, PsbPayment $payment): RedirectResponse
    {
        // Pastikan payment masih pending
        if ($payment->status !== PsbPayment::STATUS_PENDING) {
            return back()->with('error', 'Pembayaran sudah diverifikasi sebelumnya.');
        }

        try {
            $approved = $request->validated('approved');
            $notes = $approved
                ? $request->validated('notes')
                : $request->validated('rejection_reason');

            $this->psbService->verifyPayment(
                $payment,
                auth()->user(),
                $approved,
                $notes
            );

            // Send notification
            $this->sendVerificationNotification($payment->fresh());

            $message = $approved
                ? 'Pembayaran berhasil diverifikasi.'
                : 'Pembayaran berhasil ditolak.';

            return redirect()
                ->route('admin.psb.payments.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to verify payment', [
                'payment_id' => $payment->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal memverifikasi pembayaran. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Send verification notification ke email orang tua
     *
     * @param  PsbPayment  $payment  Payment record
     */
    protected function sendVerificationNotification(PsbPayment $payment): void
    {
        $registration = $payment->registration;
        $email = $registration->father_email ?? $registration->mother_email;

        if ($email) {
            try {
                Notification::route('mail', $email)
                    ->notify(new PsbPaymentVerified($payment));
            } catch (\Exception $e) {
                Log::warning('Failed to send payment verification notification', [
                    'payment_id' => $payment->id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
