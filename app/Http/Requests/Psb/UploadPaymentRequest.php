<?php

namespace App\Http\Requests\Psb;

use App\Models\PsbPayment;
use App\Services\PsbService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UploadPaymentRequest - Validasi form upload bukti pembayaran PSB
 *
 * Form request ini bertujuan untuk memvalidasi file bukti pembayaran
 * dan data pembayaran yang disubmit oleh parent saat daftar ulang
 */
class UploadPaymentRequest extends FormRequest
{
    /**
     * Authorization check untuk parent role dengan status re_registration
     * Hanya parent dengan pendaftaran yang sedang daftar ulang yang dapat upload pembayaran
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || $user->role !== 'PARENT') {
            return false;
        }

        // Check apakah parent memiliki pendaftaran yang bisa upload payment
        $psbService = app(PsbService::class);
        $registration = $psbService->getParentRegistration($user);

        if (! $registration) {
            return false;
        }

        // Parent dapat upload payment jika status re_registration atau sudah upload tapi belum verified
        return in_array($registration->status, ['approved', 're_registration']);
    }

    /**
     * Validation rules untuk upload payment form
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_type' => [
                'required',
                'string',
                Rule::in([
                    PsbPayment::TYPE_REGISTRATION_FEE,
                    PsbPayment::TYPE_RE_REGISTRATION_FEE,
                ]),
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_method' => [
                'required',
                'string',
                Rule::in([
                    PsbPayment::METHOD_TRANSFER,
                    PsbPayment::METHOD_CASH,
                    PsbPayment::METHOD_QRIS,
                ]),
            ],
            'proof_file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120', // 5MB max
            ],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom error messages dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_type.required' => 'Jenis pembayaran wajib dipilih.',
            'payment_type.in' => 'Jenis pembayaran tidak valid.',
            'amount.required' => 'Jumlah pembayaran wajib diisi.',
            'amount.integer' => 'Jumlah pembayaran harus berupa angka.',
            'amount.min' => 'Jumlah pembayaran minimal Rp 1.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'proof_file.required' => 'Bukti pembayaran wajib diupload.',
            'proof_file.file' => 'Format file tidak valid.',
            'proof_file.mimes' => 'Format file harus JPG, PNG, atau PDF.',
            'proof_file.max' => 'Ukuran file maksimal 5MB.',
            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
            'payment_date.date' => 'Format tanggal tidak valid.',
            'payment_date.before_or_equal' => 'Tanggal pembayaran tidak boleh lebih dari hari ini.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }

    /**
     * Custom attribute names dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'payment_type' => 'jenis pembayaran',
            'amount' => 'jumlah pembayaran',
            'payment_method' => 'metode pembayaran',
            'proof_file' => 'bukti pembayaran',
            'payment_date' => 'tanggal pembayaran',
            'notes' => 'catatan',
        ];
    }
}
