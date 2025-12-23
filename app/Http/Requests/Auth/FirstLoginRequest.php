<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class FirstLoginRequest extends FormRequest
{
    /**
     * Authorize request - hanya user yang authenticated dan is_first_login = true
     * yang dapat mengakses endpoint ini
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->is_first_login;
    }

    /**
     * Validation rules untuk update password pada first login
     * dengan password strength requirements untuk security
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ];
    }

    /**
     * Custom error messages dalam Bahasa Indonesia untuk user experience
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.required' => 'Password baru wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal harus 8 karakter.',
        ];
    }

    /**
     * Custom attribute names untuk pesan error yang lebih readable
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'password' => 'Password',
        ];
    }
}
