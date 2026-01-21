<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya Super Admin dan TU
     * yang dapat mengupdate data siswa
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk update siswa dengan unique validation yang exclude current student
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $studentId = $this->route('student')->id;

        return [
            // Nomor Identitas - NIS tidak bisa diubah, hanya NISN dan NIK
            'nisn' => ['required', 'digits:10', Rule::unique('students', 'nisn')->ignore($studentId)],
            'nik' => ['required', 'digits:16', Rule::unique('students', 'nik')->ignore($studentId)],

            // Biodata Pribadi
            'nama_lengkap' => ['required', 'string', 'min:3', 'max:100'],
            'nama_panggilan' => ['nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today', 'after:'.now()->subYears(15)->format('Y-m-d')],
            'agama' => ['required', Rule::in(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'])],
            'anak_ke' => ['required', 'integer', 'min:1', 'max:20'],
            'jumlah_saudara' => ['required', 'integer', 'min:0', 'max:20'],
            'status_keluarga' => ['required', Rule::in(['Anak Kandung', 'Anak Tiri', 'Anak Angkat', 'Lainnya'])],

            // Alamat
            'alamat' => ['required', 'string'],
            'rt_rw' => ['nullable', 'string', 'max:10'],
            'kelurahan' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'string', 'max:10'],

            // Kontak
            'no_hp' => ['nullable', 'string', 'min:10', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            // Data Akademik
            'kelas_id' => ['nullable', 'integer'],

            // Data Ayah
            'ayah.nik' => ['required', 'digits:16'],
            'ayah.nama_lengkap' => ['required', 'string', 'max:100'],
            'ayah.pekerjaan' => ['required', 'string', 'max:100'],
            'ayah.pendidikan' => ['required', Rule::in(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Tidak Sekolah', 'Lainnya'])],
            'ayah.penghasilan' => ['required', Rule::in(['<1jt', '1-3jt', '3-5jt', '5-10jt', '>10jt'])],
            'ayah.no_hp' => ['required', 'string', 'min:10', 'max:20'],
            'ayah.email' => ['nullable', 'email', 'max:100'],
            'ayah.alamat' => ['nullable', 'string'],
            'ayah.is_primary_contact' => ['nullable', 'boolean'],

            // Data Ibu
            'ibu.nik' => ['required', 'digits:16'],
            'ibu.nama_lengkap' => ['required', 'string', 'max:100'],
            'ibu.pekerjaan' => ['required', 'string', 'max:100'],
            'ibu.pendidikan' => ['required', Rule::in(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Tidak Sekolah', 'Lainnya'])],
            'ibu.penghasilan' => ['required', Rule::in(['<1jt', '1-3jt', '3-5jt', '5-10jt', '>10jt'])],
            'ibu.no_hp' => ['nullable', 'string', 'min:10', 'max:20'],
            'ibu.email' => ['nullable', 'email', 'max:100'],
            'ibu.alamat' => ['nullable', 'string'],
            'ibu.is_primary_contact' => ['nullable', 'boolean'],

            // Data Wali (optional)
            'wali.nik' => ['nullable', 'digits:16'],
            'wali.nama_lengkap' => ['nullable', 'string', 'max:100'],
            'wali.pekerjaan' => ['nullable', 'string', 'max:100'],
            'wali.pendidikan' => ['nullable', Rule::in(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Tidak Sekolah', 'Lainnya'])],
            'wali.penghasilan' => ['nullable', Rule::in(['<1jt', '1-3jt', '3-5jt', '5-10jt', '>10jt'])],
            'wali.no_hp' => ['nullable', 'string', 'min:10', 'max:20'],
            'wali.email' => ['nullable', 'email', 'max:100'],
            'wali.alamat' => ['nullable', 'string'],
            'wali.is_primary_contact' => ['nullable', 'boolean'],
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
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.digits' => 'NISN harus 10 digit.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min' => 'Nama lengkap minimal 3 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.after' => 'Umur siswa tidak sesuai untuk jenjang SD.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
            'ayah.nama_lengkap.required' => 'Nama ayah wajib diisi.',
            'ibu.nama_lengkap.required' => 'Nama ibu wajib diisi.',
        ];
    }
}
