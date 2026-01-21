<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya Super Admin dan TU
     * yang dapat menambahkan data siswa baru
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk pembuatan siswa baru dengan field lengkap
     * yang mencakup biodata, alamat, dan data orang tua/wali
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Nomor Identitas
            'nisn' => ['required', 'digits:10', 'unique:students,nisn'],
            'nik' => ['required', 'digits:16', 'unique:students,nik'],

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
            'tahun_ajaran_masuk' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'tanggal_masuk' => ['required', 'date', 'before_or_equal:today'],

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
     * Custom error messages dalam Bahasa Indonesia untuk better user experience
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // NIK & NISN
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.digits' => 'NISN harus 10 digit.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar.',

            // Biodata
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min' => 'Nama lengkap minimal 3 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.after' => 'Umur siswa tidak sesuai untuk jenjang SD (maksimal 15 tahun).',
            'agama.required' => 'Agama wajib dipilih.',

            // Alamat
            'alamat.required' => 'Alamat lengkap wajib diisi.',
            'kelurahan.required' => 'Kelurahan/Desa wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'kota.required' => 'Kota/Kabupaten wajib diisi.',
            'provinsi.required' => 'Provinsi wajib diisi.',

            // Foto
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus jpg, jpeg, atau png.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',

            // Data Akademik
            'tahun_ajaran_masuk.required' => 'Tahun ajaran masuk wajib diisi.',
            'tahun_ajaran_masuk.regex' => 'Format tahun ajaran tidak valid (contoh: 2024/2025).',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',

            // Data Ayah
            'ayah.nik.required' => 'NIK ayah wajib diisi.',
            'ayah.nik.digits' => 'NIK ayah harus 16 digit.',
            'ayah.nama_lengkap.required' => 'Nama ayah wajib diisi.',
            'ayah.pekerjaan.required' => 'Pekerjaan ayah wajib diisi.',
            'ayah.pendidikan.required' => 'Pendidikan ayah wajib dipilih.',
            'ayah.penghasilan.required' => 'Penghasilan ayah wajib dipilih.',
            'ayah.no_hp.required' => 'Nomor HP ayah wajib diisi.',
            'ayah.no_hp.min' => 'Nomor HP ayah minimal 10 digit.',

            // Data Ibu
            'ibu.nik.required' => 'NIK ibu wajib diisi.',
            'ibu.nik.digits' => 'NIK ibu harus 16 digit.',
            'ibu.nama_lengkap.required' => 'Nama ibu wajib diisi.',
            'ibu.pekerjaan.required' => 'Pekerjaan ibu wajib diisi.',
            'ibu.pendidikan.required' => 'Pendidikan ibu wajib dipilih.',
            'ibu.penghasilan.required' => 'Penghasilan ibu wajib dipilih.',
        ];
    }
}
