<?php

namespace App\Http\Requests\Psb;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RejectRegistrationRequest - Validasi form reject pendaftaran PSB
 *
 * Form request ini bertujuan untuk memvalidasi input penolakan
 * dengan alasan wajib untuk transparansi ke orang tua
 */
class RejectRegistrationRequest extends FormRequest
{
    /**
     * Authorization check untuk admin role
     * Hanya admin yang dapat reject pendaftaran
     */
    public function authorize(): bool
    {
        if (! $this->user()) {
            return false;
        }

        return in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk reject form
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:1000'],
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
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ];
    }
}
