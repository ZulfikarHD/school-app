<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Authorization untuk forgot password - hanya guest yang dapat request
     * karena user yang sudah login bisa langsung change password
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules untuk forgot password request dengan email validation
     * dan check existence dalam database
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
        ];
    }

    /**
     * Custom error messages dalam Bahasa Indonesia untuk better UX
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak terdaftar dalam sistem.',
        ];
    }
}
