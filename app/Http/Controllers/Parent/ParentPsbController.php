<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Psb\SubmitReRegistrationRequest;
use App\Http\Requests\Psb\UploadPaymentRequest;
use App\Models\PsbPayment;
use App\Models\PsbRegistration;
use App\Services\PsbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * ParentPsbController - Controller untuk fitur daftar ulang PSB untuk Parent
 *
 * Controller ini bertujuan untuk menyediakan fungsi parent dalam
 * melakukan daftar ulang setelah pendaftaran diumumkan diterima
 */
class ParentPsbController extends Controller
{
    /**
     * Constructor untuk inject PsbService dependency
     * yang digunakan untuk business logic daftar ulang PSB
     */
    public function __construct(
        protected PsbService $psbService
    ) {}

    /**
     * Display halaman daftar ulang
     * Hanya dapat diakses jika pendaftaran sudah diumumkan (announced)
     */
    public function reRegister(): Response|RedirectResponse
    {
        $registration = $this->psbService->getParentRegistration(auth()->user());

        if (! $registration) {
            return redirect()
                ->route('parent.dashboard')
                ->with('error', 'Anda tidak memiliki pendaftaran PSB yang dapat diakses.');
        }

        // If already completed, redirect to welcome
        if ($registration->status === PsbRegistration::STATUS_COMPLETED) {
            return redirect()->route('parent.psb.welcome');
        }

        // Get payment info from PSB settings
        $settings = $this->psbService->getActiveSettings();

        // Get existing payments
        $payments = $registration->payments()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($payment) => [
                'id' => $payment->id,
                'payment_type' => $payment->payment_type,
                'payment_type_label' => $payment->getPaymentTypeLabel(),
                'amount' => $payment->amount,
                'formatted_amount' => $payment->getFormattedAmount(),
                'payment_method' => $payment->payment_method,
                'payment_method_label' => $payment->getPaymentMethodLabel(),
                'status' => $payment->status,
                'status_label' => $payment->getStatusLabel(),
                'proof_url' => $payment->getProofUrl(),
                'notes' => $payment->notes,
                'created_at' => $payment->created_at->format('d M Y H:i'),
                'verified_at' => $payment->verified_at?->format('d M Y H:i'),
            ]);

        // Build timeline
        $timeline = $this->buildReRegistrationTimeline($registration);

        return Inertia::render('Parent/Psb/ReRegister', [
            'title' => 'Daftar Ulang PSB',
            'registration' => [
                'id' => $registration->id,
                'registration_number' => $registration->registration_number,
                'student_name' => $registration->student_name,
                'status' => $registration->status,
                'status_label' => $registration->getStatusLabel(),
                'announced_at' => $registration->announced_at?->format('d M Y'),
                'academic_year' => $registration->academicYear?->name,
                'can_re_register' => $registration->canReRegister(),
                'can_upload_payment' => in_array($registration->status, [
                    PsbRegistration::STATUS_APPROVED,
                    PsbRegistration::STATUS_RE_REGISTRATION,
                ]),
            ],
            'payments' => $payments,
            'timeline' => $timeline,
            'paymentInfo' => $settings ? [
                'registration_fee' => $settings->registration_fee,
                'formatted_fee' => $settings->formatted_fee,
                're_registration_deadline_days' => $settings->re_registration_deadline_days,
            ] : null,
            'paymentTypes' => PsbPayment::getPaymentTypes(),
            'paymentMethods' => PsbPayment::getPaymentMethods(),
        ]);
    }

    /**
     * Submit re-registration data
     * Update data tambahan dan ubah status ke re_registration
     */
    public function submitReRegister(SubmitReRegistrationRequest $request): RedirectResponse
    {
        $registration = $this->psbService->getParentRegistration(auth()->user());

        if (! $registration || ! $registration->canReRegister()) {
            return back()->with('error', 'Pendaftaran tidak dapat melakukan daftar ulang.');
        }

        try {
            $this->psbService->submitReRegistration(
                $registration,
                $request->validated()
            );

            return redirect()
                ->route('parent.psb.re-register')
                ->with('success', 'Data daftar ulang berhasil disimpan. Silakan upload bukti pembayaran.');
        } catch (\Exception $e) {
            Log::error('Failed to submit re-registration', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }
    }

    /**
     * Upload bukti pembayaran
     * Create payment record dengan file bukti transfer
     */
    public function uploadPayment(UploadPaymentRequest $request): RedirectResponse
    {
        $registration = $this->psbService->getParentRegistration(auth()->user());

        if (! $registration) {
            return back()->with('error', 'Pendaftaran tidak ditemukan.');
        }

        // Check if status allows payment upload
        if (! in_array($registration->status, [
            PsbRegistration::STATUS_APPROVED,
            PsbRegistration::STATUS_RE_REGISTRATION,
        ])) {
            return back()->with('error', 'Status pendaftaran tidak memungkinkan upload pembayaran.');
        }

        try {
            $this->psbService->uploadPaymentProof(
                $registration,
                $request->file('proof_file'),
                $request->validated()
            );

            return redirect()
                ->route('parent.psb.re-register')
                ->with('success', 'Bukti pembayaran berhasil diupload. Mohon tunggu verifikasi dari admin.');
        } catch (\Exception $e) {
            Log::error('Failed to upload payment proof', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengupload bukti pembayaran. Silakan coba lagi.');
        }
    }

    /**
     * Display welcome page setelah pendaftaran selesai
     * Hanya dapat diakses jika status completed
     */
    public function welcome(): Response|RedirectResponse
    {
        $registration = $this->psbService->getParentRegistration(auth()->user());

        if (! $registration) {
            return redirect()
                ->route('parent.dashboard')
                ->with('error', 'Anda tidak memiliki pendaftaran PSB yang dapat diakses.');
        }

        // If not completed yet, redirect to re-register
        if ($registration->status !== PsbRegistration::STATUS_COMPLETED) {
            return redirect()->route('parent.psb.re-register');
        }

        return Inertia::render('Parent/Psb/Welcome', [
            'title' => 'Selamat Datang',
            'registration' => [
                'student_name' => $registration->student_name,
                'registration_number' => $registration->registration_number,
                'academic_year' => $registration->academicYear?->name,
            ],
        ]);
    }

    /**
     * Build timeline untuk tracking progress daftar ulang
     *
     * @param  PsbRegistration  $registration  Data pendaftaran
     * @return array Timeline steps
     */
    protected function buildReRegistrationTimeline(PsbRegistration $registration): array
    {
        $hasPayment = $registration->payments()->exists();
        $hasPendingPayment = $registration->payments()
            ->where('status', PsbPayment::STATUS_PENDING)
            ->exists();
        $hasVerifiedPayment = $registration->payments()
            ->where('status', PsbPayment::STATUS_VERIFIED)
            ->exists();
        $isCompleted = $registration->status === PsbRegistration::STATUS_COMPLETED;

        return [
            [
                'step' => 'announced',
                'label' => 'Pengumuman Diterima',
                'description' => 'Pendaftaran Anda telah diterima',
                'completed' => true,
                'current' => ! $hasPayment && $registration->canReRegister(),
                'date' => $registration->announced_at?->format('d M Y'),
            ],
            [
                'step' => 'payment_uploaded',
                'label' => 'Upload Bukti Pembayaran',
                'description' => 'Bukti pembayaran telah diupload',
                'completed' => $hasPayment,
                'current' => $hasPayment && $hasPendingPayment,
                'date' => null,
            ],
            [
                'step' => 'payment_verified',
                'label' => 'Verifikasi Pembayaran',
                'description' => 'Pembayaran telah diverifikasi admin',
                'completed' => $hasVerifiedPayment,
                'current' => $hasVerifiedPayment && ! $isCompleted,
                'date' => null,
            ],
            [
                'step' => 'completed',
                'label' => 'Daftar Ulang Selesai',
                'description' => 'Selamat! Anda resmi terdaftar',
                'completed' => $isCompleted,
                'current' => $isCompleted,
                'date' => null,
            ],
        ];
    }
}
