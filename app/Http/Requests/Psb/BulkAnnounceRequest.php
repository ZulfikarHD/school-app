<?php

namespace App\Http\Requests\Psb;

use Illuminate\Foundation\Http\FormRequest;

/**
 * BulkAnnounceRequest - Validasi form bulk announce pendaftaran PSB
 *
 * Form request ini bertujuan untuk memvalidasi array ID pendaftaran
 * yang akan diumumkan secara bulk oleh admin
 */
class BulkAnnounceRequest extends FormRequest
{
    /**
     * Authorization check untuk admin role
     * Hanya admin yang dapat melakukan bulk announce
     */
    public function authorize(): bool
    {
        if (! $this->user()) {
            return false;
        }

        return in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk bulk announce form
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'registration_ids' => ['required', 'array', 'min:1'],
            'registration_ids.*' => ['required', 'integer', 'exists:psb_registrations,id'],
            'send_notification' => ['boolean'],
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
            'registration_ids.required' => 'Pilih minimal satu pendaftaran untuk diumumkan.',
            'registration_ids.array' => 'Format data tidak valid.',
            'registration_ids.min' => 'Pilih minimal satu pendaftaran untuk diumumkan.',
            'registration_ids.*.exists' => 'Satu atau lebih pendaftaran tidak ditemukan.',
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
            'registration_ids' => 'daftar pendaftaran',
            'send_notification' => 'kirim notifikasi',
        ];
    }
}
