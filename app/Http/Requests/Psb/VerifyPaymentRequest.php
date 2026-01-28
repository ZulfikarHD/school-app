<?php

namespace App\Http\Requests\Psb;

use Illuminate\Foundation\Http\FormRequest;

/**
 * VerifyPaymentRequest - Validasi form verifikasi pembayaran PSB
 *
 * Form request ini bertujuan untuk memvalidasi keputusan verifikasi
 * pembayaran daftar ulang oleh admin (approve/reject)
 */
class VerifyPaymentRequest extends FormRequest
{
    /**
     * Authorization check untuk admin role
     * Hanya admin yang dapat memverifikasi pembayaran
     */
    public function authorize(): bool
    {
        if (! $this->user()) {
            return false;
        }

        return in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk verify payment form
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'approved' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'rejection_reason' => [
                'required_if:approved,false',
                'nullable',
                'string',
                'min:10',
                'max:500',
            ],
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
            'approved.required' => 'Keputusan verifikasi wajib diisi.',
            'approved.boolean' => 'Keputusan verifikasi tidak valid.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
            'rejection_reason.required_if' => 'Alasan penolakan wajib diisi jika pembayaran ditolak.',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 500 karakter.',
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
            'approved' => 'keputusan verifikasi',
            'notes' => 'catatan',
            'rejection_reason' => 'alasan penolakan',
        ];
    }
}
