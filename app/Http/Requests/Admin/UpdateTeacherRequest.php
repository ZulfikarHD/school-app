<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusKepegawaian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya Super Admin dan Admin (TU)
     * yang dapat mengupdate data guru
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk update data guru dengan handling unique constraint
     * yang mengecualikan record saat ini
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $teacherId = $this->route('teacher')?->id ?? $this->route('teacher');

        return [
            // Nomor Identitas
            'nip' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('teachers', 'nip')->ignore($teacherId),
            ],
            'nik' => [
                'required',
                'digits:16',
                Rule::unique('teachers', 'nik')->ignore($teacherId),
            ],

            // Biodata Pribadi
            'nama_lengkap' => ['required', 'string', 'min:3', 'max:150'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'min:10', 'max:20'],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('teachers', 'email')->ignore($teacherId),
            ],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            // Data Kepegawaian
            'status_kepegawaian' => ['required', Rule::in(StatusKepegawaian::values())],
            'tanggal_mulai_kerja' => ['nullable', 'date'],
            'tanggal_berakhir_kontrak' => [
                'nullable',
                'date',
                'after:tanggal_mulai_kerja',
                'required_if:status_kepegawaian,kontrak',
            ],

            // Kualifikasi Akademik
            'kualifikasi_pendidikan' => ['nullable', 'string', 'max:50'],

            // Mata Pelajaran
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['integer', 'exists:subjects,id'],
            'tahun_ajaran' => ['nullable', 'string', 'regex:/^\d{4}\/\d{4}$/'],
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
            // NIP & NIK
            'nip.unique' => 'NIP sudah terdaftar di sistem.',
            'nip.max' => 'NIP maksimal 30 karakter.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar di sistem.',

            // Biodata
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min' => 'Nama lengkap minimal 3 karakter.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 150 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',

            // Kontak
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar di sistem.',
            'no_hp.min' => 'Nomor HP minimal 10 digit.',
            'no_hp.max' => 'Nomor HP maksimal 20 digit.',

            // Foto
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus jpg, jpeg, atau png.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',

            // Data Kepegawaian
            'status_kepegawaian.required' => 'Status kepegawaian wajib dipilih.',
            'status_kepegawaian.in' => 'Status kepegawaian tidak valid.',
            'tanggal_mulai_kerja.date' => 'Format tanggal mulai kerja tidak valid.',
            'tanggal_berakhir_kontrak.date' => 'Format tanggal berakhir kontrak tidak valid.',
            'tanggal_berakhir_kontrak.after' => 'Tanggal berakhir kontrak harus setelah tanggal mulai kerja.',
            'tanggal_berakhir_kontrak.required_if' => 'Tanggal berakhir kontrak wajib diisi untuk guru kontrak.',

            // Kualifikasi
            'kualifikasi_pendidikan.max' => 'Kualifikasi pendidikan maksimal 50 karakter.',

            // Mata Pelajaran
            'subjects.array' => 'Format mata pelajaran tidak valid.',
            'subjects.*.integer' => 'ID mata pelajaran harus berupa angka.',
            'subjects.*.exists' => 'Mata pelajaran yang dipilih tidak ditemukan.',
            'tahun_ajaran.regex' => 'Format tahun ajaran tidak valid (contoh: 2024/2025).',
        ];
    }

    /**
     * Prepare the data for validation
     * Menghandle conditional requirement untuk NIP berdasarkan status kepegawaian
     */
    protected function prepareForValidation(): void
    {
        // NIP wajib untuk guru tetap dan kontrak, tidak wajib untuk honorer
        if ($this->status_kepegawaian === 'honorer') {
            $this->merge(['nip' => null]);
        }
    }
}
