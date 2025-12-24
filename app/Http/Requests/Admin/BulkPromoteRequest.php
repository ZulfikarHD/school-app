<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkPromoteRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya Super Admin dan TU
     * yang dapat melakukan bulk naik kelas
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk bulk promote students dengan array of student IDs
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'exists:students,id'],
            'kelas_id_baru' => ['required', 'integer'],
            'tahun_ajaran_baru' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'wali_kelas' => ['nullable', 'string', 'max:100'],
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
            'student_ids.required' => 'Pilih minimal 1 siswa untuk dipromosikan.',
            'student_ids.array' => 'Data siswa tidak valid.',
            'student_ids.min' => 'Pilih minimal 1 siswa.',
            'student_ids.*.exists' => 'Salah satu siswa yang dipilih tidak ditemukan.',
            'kelas_id_baru.required' => 'Kelas tujuan wajib dipilih.',
            'tahun_ajaran_baru.required' => 'Tahun ajaran baru wajib diisi.',
            'tahun_ajaran_baru.regex' => 'Format tahun ajaran tidak valid (contoh: 2024/2025).',
        ];
    }
}
