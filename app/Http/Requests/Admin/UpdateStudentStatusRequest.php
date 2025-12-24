<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentStatusRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya Super Admin dan TU
     * yang dapat mengubah status siswa
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk update status siswa dengan conditional rules
     * berdasarkan status yang dipilih
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'status' => ['required', Rule::in(['aktif', 'mutasi', 'do', 'lulus'])],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'alasan' => ['required', 'string', 'min:10'],
            'keterangan' => ['nullable', 'string'],
        ];

        // Conditional validation berdasarkan status
        if ($this->input('status') === 'mutasi') {
            $rules['sekolah_tujuan'] = ['required', 'string', 'min:5', 'max:200'];
        }

        if ($this->input('status') === 'lulus') {
            // Validasi hanya siswa kelas 6 yang bisa lulus
            $rules['tanggal'] = ['required', 'date'];
        }

        return $rules;
    }

    /**
     * Custom validation untuk check apakah siswa kelas 6 jika status lulus
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('status') === 'lulus') {
                $student = $this->route('student');
                // TODO: Check kelas_id when classes table exists
                // For now, skip this validation
            }
        });
    }

    /**
     * Custom error messages dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'alasan.required' => 'Alasan wajib diisi.',
            'alasan.min' => 'Alasan minimal 10 karakter.',
            'sekolah_tujuan.required' => 'Sekolah tujuan wajib diisi untuk status mutasi.',
            'sekolah_tujuan.min' => 'Nama sekolah tujuan minimal 5 karakter.',
        ];
    }
}
