<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya Super Admin dan TU
     * yang dapat mengupdate user account
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk update user dengan unique constraint yang ignore
     * current user ID untuk allow user keep their current email/username
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'username' => ['required', 'string', 'min:3', 'max:50', Rule::unique('users', 'username')->ignore($userId), 'regex:/^[a-zA-Z0-9._]+$/'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(['SUPERADMIN', 'ADMIN', 'PRINCIPAL', 'TEACHER', 'PARENT'])],
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }

    /**
     * Custom error messages dalam Bahasa Indonesia untuk better user experience
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.unique' => 'Username sudah digunakan oleh user lain.',
            'username.regex' => 'Username hanya boleh mengandung huruf, angka, titik, dan underscore.',
            'phone_number.max' => 'Nomor telepon maksimal 20 karakter.',
            'role.required' => 'Role user wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak valid.',
            'status.required' => 'Status user wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ];
    }
}
