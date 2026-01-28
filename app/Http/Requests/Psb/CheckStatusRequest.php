<?php

namespace App\Http\Requests\Psb;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CheckStatusRequest - Validasi form tracking status pendaftaran PSB
 *
 * Form request ini bertujuan untuk memvalidasi input nomor pendaftaran
 * pada halaman tracking status dengan rate limiting protection
 */
class CheckStatusRequest extends FormRequest
{
    /**
     * Authorization untuk tracking publik
     * Semua user (tanpa login) dapat mengakses
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules untuk nomor pendaftaran
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'registration_number' => ['required', 'string', 'max:50', 'regex:/^PSB\/\d{4}\/\d{4}$/'],
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
            'registration_number.required' => 'Nomor pendaftaran wajib diisi.',
            'registration_number.regex' => 'Format nomor pendaftaran tidak valid.',
        ];
    }
}
