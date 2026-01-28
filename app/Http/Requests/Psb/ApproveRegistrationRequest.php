<?php

namespace App\Http\Requests\Psb;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ApproveRegistrationRequest - Validasi form approve pendaftaran PSB
 *
 * Form request ini bertujuan untuk memvalidasi input approval
 * dengan notes opsional untuk catatan tambahan
 */
class ApproveRegistrationRequest extends FormRequest
{
    /**
     * Authorization check untuk admin role
     * Hanya admin yang dapat approve pendaftaran
     */
    public function authorize(): bool
    {
        if (! $this->user()) {
            return false;
        }

        return in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk approve form
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
