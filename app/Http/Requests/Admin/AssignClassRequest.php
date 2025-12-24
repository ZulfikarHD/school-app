<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['SUPERADMIN', 'ADMIN', 'TU']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'exists:students,id'],
            'kelas_id' => ['required', 'integer', 'exists:classes,id'],
            'tahun_ajaran' => ['nullable', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'student_ids.required' => 'Pilih minimal satu siswa.',
            'student_ids.min' => 'Pilih minimal satu siswa.',
            'student_ids.*.exists' => 'Data siswa tidak valid.',
            'kelas_id.required' => 'Pilih kelas tujuan.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak ditemukan.',
            'tahun_ajaran.regex' => 'Format tahun ajaran tidak valid (contoh: 2024/2025).',
        ];
    }
}
