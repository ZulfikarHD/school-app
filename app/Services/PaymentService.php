<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengelola pembayaran siswa
 *
 * Menyediakan fitur pencatatan pembayaran, pencarian siswa,
 * dan generate kwitansi PDF
 */
class PaymentService
{
    /**
     * Record pembayaran baru
     *
     * @param  array  $data  Data pembayaran (bill_id, nominal, metode_pembayaran, tanggal_bayar, keterangan)
     * @return array{success: bool, payment: ?Payment, message: string}
     */
    public function recordPayment(array $data): array
    {
        $bill = Bill::with(['student', 'paymentCategory'])->findOrFail($data['bill_id']);

        // Validate bill can be paid
        if (! $bill->canBePaid()) {
            return [
                'success' => false,
                'payment' => null,
                'message' => 'Tagihan tidak dapat dibayar (status: '.$bill->status_label.').',
            ];
        }

        // Validate nominal doesn't exceed remaining
        if ($data['nominal'] > $bill->sisa_tagihan) {
            return [
                'success' => false,
                'payment' => null,
                'message' => 'Nominal pembayaran melebihi sisa tagihan.',
            ];
        }

        DB::beginTransaction();

        try {
            // Determine initial status based on payment method
            // Cash payments are auto-verified, transfer payments need verification
            $status = $data['metode_pembayaran'] === 'tunai' ? 'verified' : 'pending';

            $payment = Payment::create([
                'nomor_kwitansi' => Payment::generateNomorKwitansi(),
                'bill_id' => $bill->id,
                'student_id' => $bill->student_id,
                'nominal' => $data['nominal'],
                'metode_pembayaran' => $data['metode_pembayaran'],
                'tanggal_bayar' => $data['tanggal_bayar'],
                'waktu_bayar' => now()->format('H:i:s'),
                'status' => $status,
                'keterangan' => $data['keterangan'] ?? null,
                'verified_by' => $status === 'verified' ? auth()->id() : null,
                'verified_at' => $status === 'verified' ? now() : null,
            ]);

            DB::commit();

            // Load relationships for response
            $payment->load(['bill.paymentCategory', 'student.kelas', 'creator']);

            return [
                'success' => true,
                'payment' => $payment,
                'message' => 'Pembayaran berhasil dicatat.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record payment', [
                'bill_id' => $data['bill_id'],
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'payment' => null,
                'message' => 'Gagal mencatat pembayaran: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Search students untuk autocomplete
     *
     * @param  string  $query  Search query (nama, NIS, atau NISN)
     * @param  int  $limit  Maksimum hasil pencarian
     * @return Collection<Student>
     */
    public function searchStudents(string $query, int $limit = 10): Collection
    {
        if (strlen($query) < 2) {
            return collect();
        }

        return Student::query()
            ->active()
            ->search($query)
            ->with(['kelas'])
            ->orderBy('nama_lengkap')
            ->limit($limit)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'nama_lengkap' => $student->nama_lengkap,
                    'kelas' => $student->kelas?->nama_lengkap ?? '-',
                    'kelas_id' => $student->kelas_id,
                    'display_label' => "{$student->nama_lengkap} - {$student->nis}",
                    'total_tunggakan' => $student->total_tunggakan,
                    'formatted_tunggakan' => 'Rp '.number_format($student->total_tunggakan, 0, ',', '.'),
                ];
            });
    }

    /**
     * Get unpaid bills untuk student
     *
     * @param  int  $studentId  ID student
     * @return Collection<Bill>
     */
    public function getStudentUnpaidBills(int $studentId): Collection
    {
        return Bill::query()
            ->where('student_id', $studentId)
            ->unpaid()
            ->with(['paymentCategory'])
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->get()
            ->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'nomor_tagihan' => $bill->nomor_tagihan,
                    'category' => [
                        'id' => $bill->paymentCategory->id,
                        'nama' => $bill->paymentCategory->nama,
                        'kode' => $bill->paymentCategory->kode,
                    ],
                    'bulan' => $bill->bulan,
                    'tahun' => $bill->tahun,
                    'periode' => $bill->nama_bulan.' '.$bill->tahun,
                    'nominal' => (float) $bill->nominal,
                    'nominal_terbayar' => (float) $bill->nominal_terbayar,
                    'sisa_tagihan' => $bill->sisa_tagihan,
                    'formatted_nominal' => $bill->formatted_nominal,
                    'formatted_sisa' => $bill->formatted_sisa,
                    'status' => $bill->status,
                    'status_label' => $bill->status_label,
                    'is_overdue' => $bill->isOverdue(),
                    'tanggal_jatuh_tempo' => $bill->tanggal_jatuh_tempo?->format('Y-m-d'),
                    'formatted_due_date' => $bill->tanggal_jatuh_tempo?->format('d M Y'),
                ];
            });
    }

    /**
     * Get payment by ID with relationships
     */
    public function getPayment(int $paymentId): ?Payment
    {
        return Payment::query()
            ->with([
                'bill.paymentCategory',
                'student.kelas',
                'creator',
                'verifier',
            ])
            ->find($paymentId);
    }

    /**
     * Verify pending payment
     *
     * @param  Payment  $payment  Payment to verify
     * @param  int|null  $userId  User yang memverifikasi
     * @return array{success: bool, message: string}
     */
    public function verifyPayment(Payment $payment, ?int $userId = null): array
    {
        if (! $payment->canBeVerified()) {
            return [
                'success' => false,
                'message' => 'Pembayaran tidak dapat diverifikasi (status: '.$payment->status_label.').',
            ];
        }

        try {
            $payment->verify($userId);

            return [
                'success' => true,
                'message' => 'Pembayaran berhasil diverifikasi.',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to verify payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memverifikasi pembayaran.',
            ];
        }
    }

    /**
     * Cancel payment
     *
     * @param  Payment  $payment  Payment to cancel
     * @param  string  $reason  Alasan pembatalan
     * @param  int|null  $userId  User yang membatalkan
     * @return array{success: bool, message: string}
     */
    public function cancelPayment(Payment $payment, string $reason, ?int $userId = null): array
    {
        if (! $payment->canBeCancelled()) {
            return [
                'success' => false,
                'message' => 'Pembayaran tidak dapat dibatalkan.',
            ];
        }

        try {
            $payment->cancel($reason, $userId);

            return [
                'success' => true,
                'message' => 'Pembayaran berhasil dibatalkan.',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to cancel payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membatalkan pembayaran.',
            ];
        }
    }

    /**
     * Format payment data untuk response
     *
     * Menyertakan data pembayaran lengkap termasuk informasi verifikasi
     * dan pembatalan untuk ditampilkan di halaman detail
     */
    public function formatPaymentForResponse(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'nomor_kwitansi' => $payment->nomor_kwitansi,
            'bill' => [
                'id' => $payment->bill->id,
                'nomor_tagihan' => $payment->bill->nomor_tagihan,
                'category' => $payment->bill->paymentCategory->nama,
                'periode' => $payment->bill->nama_bulan.' '.$payment->bill->tahun,
            ],
            'student' => [
                'id' => $payment->student->id,
                'nama_lengkap' => $payment->student->nama_lengkap,
                'nis' => $payment->student->nis,
                'kelas' => $payment->student->kelas?->nama_lengkap ?? '-',
            ],
            'nominal' => (float) $payment->nominal,
            'formatted_nominal' => $payment->formatted_nominal,
            'metode_pembayaran' => $payment->metode_pembayaran,
            'metode_label' => $payment->metode_label,
            'tanggal_bayar' => $payment->tanggal_bayar?->format('Y-m-d'),
            'formatted_tanggal' => $payment->tanggal_bayar?->format('d M Y'),
            'waktu_bayar' => $payment->waktu_bayar?->format('H:i'),
            'status' => $payment->status,
            'status_label' => $payment->status_label,
            'keterangan' => $payment->keterangan,
            'creator' => $payment->creator ? [
                'id' => $payment->creator->id,
                'name' => $payment->creator->name,
            ] : null,
            'verifier' => $payment->verifier ? [
                'id' => $payment->verifier->id,
                'name' => $payment->verifier->name,
            ] : null,
            'verified_at' => $payment->verified_at?->format('d M Y H:i'),
            // Cancellation fields - untuk menampilkan detail pembatalan
            'canceller' => $payment->canceller ? [
                'id' => $payment->canceller->id,
                'name' => $payment->canceller->name,
            ] : null,
            'cancelled_at' => $payment->cancelled_at?->format('d M Y H:i'),
            'cancellation_reason' => $payment->cancellation_reason,
            'created_at' => $payment->created_at?->format('d M Y H:i'),
        ];
    }

    /**
     * Get payment statistics for dashboard
     *
     * @param  string|null  $date  Filter by date (Y-m-d format)
     */
    public function getPaymentStatistics(?string $date = null): array
    {
        $query = Payment::query()->active();

        if ($date) {
            $query->whereDate('tanggal_bayar', $date);
        } else {
            $query->whereDate('tanggal_bayar', now()->toDateString());
        }

        $payments = $query->get();

        return [
            'total_transactions' => $payments->count(),
            'total_amount' => $payments->where('status', 'verified')->sum('nominal'),
            'formatted_total' => 'Rp '.number_format($payments->where('status', 'verified')->sum('nominal'), 0, ',', '.'),
            'pending_count' => $payments->where('status', 'pending')->count(),
            'verified_count' => $payments->where('status', 'verified')->count(),
            'by_method' => [
                'tunai' => $payments->where('metode_pembayaran', 'tunai')->where('status', 'verified')->sum('nominal'),
                'transfer' => $payments->where('metode_pembayaran', 'transfer')->where('status', 'verified')->sum('nominal'),
                'qris' => $payments->where('metode_pembayaran', 'qris')->where('status', 'verified')->sum('nominal'),
            ],
        ];
    }

    /**
     * Generate receipt PDF
     *
     * @param  Payment  $payment  Payment to generate receipt for
     */
    public function generateReceiptPdf(Payment $payment): \Barryvdh\DomPDF\PDF
    {
        $payment->load(['bill.paymentCategory', 'student.kelas', 'creator']);

        $data = [
            'payment' => $payment,
            'school' => [
                'name' => config('app.school_name', 'Sekolah Dasar Negeri'),
                'address' => config('app.school_address', 'Jl. Pendidikan No. 1'),
                'phone' => config('app.school_phone', '(021) 123-4567'),
                'email' => config('app.school_email', 'info@sekolah.sch.id'),
            ],
            'generated_at' => now()->format('d M Y H:i'),
        ];

        return Pdf::loadView('receipts.payment', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Get receipt data untuk preview (tanpa generate PDF)
     */
    public function getReceiptData(Payment $payment): array
    {
        $payment->load(['bill.paymentCategory', 'student.kelas', 'creator']);

        return [
            'nomor_kwitansi' => $payment->nomor_kwitansi,
            'tanggal' => $payment->tanggal_bayar?->format('d M Y'),
            'waktu' => $payment->waktu_bayar?->format('H:i'),
            'student' => [
                'nama' => $payment->student->nama_lengkap,
                'nis' => $payment->student->nis,
                'kelas' => $payment->student->kelas?->nama_lengkap ?? '-',
            ],
            'bill' => [
                'nomor' => $payment->bill->nomor_tagihan,
                'kategori' => $payment->bill->paymentCategory->nama,
                'periode' => $payment->bill->nama_bulan.' '.$payment->bill->tahun,
                'nominal_tagihan' => $payment->bill->formatted_nominal,
                'sisa_sebelum' => 'Rp '.number_format($payment->bill->sisa_tagihan + $payment->nominal, 0, ',', '.'),
                'sisa_sesudah' => $payment->bill->formatted_sisa,
            ],
            'pembayaran' => [
                'nominal' => $payment->formatted_nominal,
                'metode' => $payment->metode_label,
            ],
            'petugas' => $payment->creator?->name ?? '-',
            'school' => [
                'name' => config('app.school_name', 'Sekolah Dasar Negeri'),
                'address' => config('app.school_address', 'Jl. Pendidikan No. 1'),
            ],
        ];
    }
}
