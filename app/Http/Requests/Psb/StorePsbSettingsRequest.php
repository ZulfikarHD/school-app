<?php

namespace App\Http\Requests\Psb;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

/**
 * StorePsbSettingsRequest - Validasi form settings PSB
 *
 * Form request ini bertujuan untuk memvalidasi data pengaturan PSB
 * yang meliputi periode pendaftaran, tanggal pengumuman, biaya, dan kuota
 */
class StorePsbSettingsRequest extends FormRequest
{
    /**
     * Authorization check untuk admin role
     * Hanya admin yang dapat mengelola settings PSB
     */
    public function authorize(): bool
    {
        if (! $this->user()) {
            return false;
        }

        return UserRole::isAdmin($this->user()->role);
    }

    /**
     * Validation rules untuk PSB settings form
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'registration_open_date' => ['required', 'date'],
            'registration_close_date' => ['required', 'date', 'after:registration_open_date'],
            'announcement_date' => ['required', 'date', 'after:registration_close_date'],
            're_registration_deadline_days' => ['required', 'integer', 'min:7', 'max:30'],
            'registration_fee' => ['required', 'numeric', 'min:0'],
            'quota_per_class' => ['required', 'integer', 'min:1'],
            'waiting_list_enabled' => ['boolean'],
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
            'academic_year_id.required' => 'Tahun ajaran harus dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak valid.',
            'registration_open_date.required' => 'Tanggal buka pendaftaran harus diisi.',
            'registration_open_date.date' => 'Format tanggal buka pendaftaran tidak valid.',
            'registration_close_date.required' => 'Tanggal tutup pendaftaran harus diisi.',
            'registration_close_date.date' => 'Format tanggal tutup pendaftaran tidak valid.',
            'registration_close_date.after' => 'Tanggal tutup harus setelah tanggal buka pendaftaran.',
            'announcement_date.required' => 'Tanggal pengumuman harus diisi.',
            'announcement_date.date' => 'Format tanggal pengumuman tidak valid.',
            'announcement_date.after' => 'Tanggal pengumuman harus setelah tanggal tutup pendaftaran.',
            're_registration_deadline_days.required' => 'Batas waktu daftar ulang harus diisi.',
            're_registration_deadline_days.integer' => 'Batas waktu daftar ulang harus berupa angka.',
            're_registration_deadline_days.min' => 'Batas waktu daftar ulang minimal 7 hari.',
            're_registration_deadline_days.max' => 'Batas waktu daftar ulang maksimal 30 hari.',
            'registration_fee.required' => 'Biaya pendaftaran harus diisi.',
            'registration_fee.numeric' => 'Biaya pendaftaran harus berupa angka.',
            'registration_fee.min' => 'Biaya pendaftaran tidak boleh negatif.',
            'quota_per_class.required' => 'Kuota per kelas harus diisi.',
            'quota_per_class.integer' => 'Kuota per kelas harus berupa angka.',
            'quota_per_class.min' => 'Kuota per kelas minimal 1 siswa.',
            'waiting_list_enabled.boolean' => 'Format waiting list tidak valid.',
        ];
    }

    /**
     * Custom attribute names dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'academic_year_id' => 'tahun ajaran',
            'registration_open_date' => 'tanggal buka pendaftaran',
            'registration_close_date' => 'tanggal tutup pendaftaran',
            'announcement_date' => 'tanggal pengumuman',
            're_registration_deadline_days' => 'batas waktu daftar ulang',
            'registration_fee' => 'biaya pendaftaran',
            'quota_per_class' => 'kuota per kelas',
            'waiting_list_enabled' => 'waiting list',
        ];
    }
}
