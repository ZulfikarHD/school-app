<?php

namespace App\Http\Requests\Psb;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StorePsbRegistrationRequest - Validasi form pendaftaran PSB
 *
 * Form request ini bertujuan untuk memvalidasi seluruh data yang diperlukan
 * dalam proses pendaftaran siswa baru, yaitu: biodata siswa, data orang tua,
 * dan dokumen persyaratan
 */
class StorePsbRegistrationRequest extends FormRequest
{
    /**
     * Authorization untuk pendaftaran publik
     * Semua user (tanpa login) dapat mengakses
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules untuk pendaftaran PSB
     * dengan field lengkap data siswa dan orang tua
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Data Calon Siswa
            'student_name' => ['required', 'string', 'min:3', 'max:100'],
            'student_nik' => ['required', 'digits:16'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => [
                'required',
                'date',
                'before:today',
                'after:'.now()->subYears(10)->format('Y-m-d'),
            ],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'religion' => [
                'required',
                Rule::in(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya']),
            ],
            'address' => ['required', 'string', 'min:10'],
            'child_order' => ['required', 'integer', 'min:1', 'max:20'],
            'origin_school' => ['nullable', 'string', 'max:150'],

            // Data Ayah
            'father_name' => ['required', 'string', 'min:3', 'max:100'],
            'father_nik' => ['required', 'digits:16'],
            'father_occupation' => ['required', 'string', 'max:100'],
            'father_phone' => ['required', 'string', 'min:10', 'max:20'],
            'father_email' => ['nullable', 'email', 'max:100'],

            // Data Ibu
            'mother_name' => ['required', 'string', 'min:3', 'max:100'],
            'mother_nik' => ['required', 'digits:16'],
            'mother_occupation' => ['required', 'string', 'max:100'],
            'mother_phone' => ['nullable', 'string', 'min:10', 'max:20'],
            'mother_email' => ['nullable', 'email', 'max:100'],

            // Catatan tambahan
            'notes' => ['nullable', 'string', 'max:500'],

            // Dokumen (file upload)
            'birth_certificate' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'family_card' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'parent_id' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
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
            // Data Siswa
            'student_name.required' => 'Nama lengkap calon siswa wajib diisi.',
            'student_name.min' => 'Nama lengkap minimal 3 karakter.',
            'student_nik.required' => 'NIK calon siswa wajib diisi.',
            'student_nik.digits' => 'NIK harus 16 digit.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.before' => 'Tanggal lahir tidak valid.',
            'birth_date.after' => 'Usia calon siswa maksimal 10 tahun.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin tidak valid.',
            'religion.required' => 'Agama wajib dipilih.',
            'religion.in' => 'Agama tidak valid.',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'address.min' => 'Alamat minimal 10 karakter.',
            'child_order.required' => 'Anak ke- wajib diisi.',
            'child_order.min' => 'Anak ke- minimal 1.',

            // Data Ayah
            'father_name.required' => 'Nama ayah wajib diisi.',
            'father_name.min' => 'Nama ayah minimal 3 karakter.',
            'father_nik.required' => 'NIK ayah wajib diisi.',
            'father_nik.digits' => 'NIK ayah harus 16 digit.',
            'father_occupation.required' => 'Pekerjaan ayah wajib diisi.',
            'father_phone.required' => 'Nomor HP ayah wajib diisi.',
            'father_phone.min' => 'Nomor HP ayah minimal 10 digit.',
            'father_email.email' => 'Format email ayah tidak valid.',

            // Data Ibu
            'mother_name.required' => 'Nama ibu wajib diisi.',
            'mother_name.min' => 'Nama ibu minimal 3 karakter.',
            'mother_nik.required' => 'NIK ibu wajib diisi.',
            'mother_nik.digits' => 'NIK ibu harus 16 digit.',
            'mother_occupation.required' => 'Pekerjaan ibu wajib diisi.',
            'mother_phone.min' => 'Nomor HP ibu minimal 10 digit.',
            'mother_email.email' => 'Format email ibu tidak valid.',

            // Dokumen
            'birth_certificate.required' => 'Akte kelahiran wajib diupload.',
            'birth_certificate.mimes' => 'Akte kelahiran harus berupa file PDF, JPG, atau PNG.',
            'birth_certificate.max' => 'Ukuran file akte kelahiran maksimal 5MB.',
            'family_card.required' => 'Kartu keluarga wajib diupload.',
            'family_card.mimes' => 'Kartu keluarga harus berupa file PDF, JPG, atau PNG.',
            'family_card.max' => 'Ukuran file kartu keluarga maksimal 5MB.',
            'parent_id.required' => 'KTP orang tua wajib diupload.',
            'parent_id.mimes' => 'KTP orang tua harus berupa file PDF, JPG, atau PNG.',
            'parent_id.max' => 'Ukuran file KTP orang tua maksimal 5MB.',
            'photo.required' => 'Pas foto 3x4 wajib diupload.',
            'photo.mimes' => 'Pas foto harus berupa file JPG atau PNG.',
            'photo.max' => 'Ukuran file pas foto maksimal 2MB.',
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
            'student_name' => 'nama lengkap',
            'student_nik' => 'NIK siswa',
            'birth_place' => 'tempat lahir',
            'birth_date' => 'tanggal lahir',
            'gender' => 'jenis kelamin',
            'religion' => 'agama',
            'address' => 'alamat',
            'child_order' => 'anak ke',
            'origin_school' => 'asal sekolah',
            'father_name' => 'nama ayah',
            'father_nik' => 'NIK ayah',
            'father_occupation' => 'pekerjaan ayah',
            'father_phone' => 'no HP ayah',
            'father_email' => 'email ayah',
            'mother_name' => 'nama ibu',
            'mother_nik' => 'NIK ibu',
            'mother_occupation' => 'pekerjaan ibu',
            'mother_phone' => 'no HP ibu',
            'mother_email' => 'email ibu',
            'birth_certificate' => 'akte kelahiran',
            'family_card' => 'kartu keluarga',
            'parent_id' => 'KTP orang tua',
            'photo' => 'pas foto',
        ];
    }
}
