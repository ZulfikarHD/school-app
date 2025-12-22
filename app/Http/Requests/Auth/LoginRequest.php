<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     * untuk login, semua user boleh mengakses (guest middleware sudah handle)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules yang apply untuk login request
     * dengan sanitization dan strict validation untuk security
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'remember' => ['boolean'],
        ];
    }

    /**
     * Get custom messages untuk validation errors dengan
     * pesan yang user-friendly dalam Bahasa Indonesia
     */
    public function messages(): array
    {
        return [
            'identifier.required' => 'Username atau email wajib diisi.',
            'identifier.string' => 'Username atau email harus berupa teks.',
            'identifier.max' => 'Username atau email maksimal 255 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
            'remember.boolean' => 'Remember me harus berupa boolean.',
        ];
    }

    /**
     * Prepare the data untuk validation dengan sanitization
     * untuk prevent injection dan ensure data integrity
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'identifier' => trim($this->identifier ?? ''),
            'remember' => $this->boolean('remember'),
        ]);
    }
}
