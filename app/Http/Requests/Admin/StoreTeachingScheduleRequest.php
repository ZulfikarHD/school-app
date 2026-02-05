<?php

namespace App\Http\Requests\Admin;

use App\Enums\Hari;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreTeachingScheduleRequest - Validasi untuk pembuatan jadwal mengajar baru
 *
 * Form request ini bertujuan untuk memvalidasi semua field yang diperlukan
 * untuk membuat jadwal mengajar baru, termasuk constraint waktu sekolah
 */
class StoreTeachingScheduleRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya Super Admin dan Admin (TU)
     * yang dapat menambahkan jadwal mengajar baru
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk pembuatan jadwal mengajar baru
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'teacher_id' => [
                'required',
                'integer',
                'exists:teachers,id',
            ],
            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],
            'class_id' => [
                'required',
                'integer',
                'exists:classes,id',
            ],
            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],
            'hari' => [
                'required',
                Rule::in(Hari::values()),
            ],
            'jam_mulai' => [
                'required',
                'date_format:H:i',
                'after_or_equal:07:00',
                'before:16:00',
            ],
            'jam_selesai' => [
                'required',
                'date_format:H:i',
                'after:jam_mulai',
                'before_or_equal:16:00',
            ],
            'ruangan' => [
                'nullable',
                'string',
                'max:50',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
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
            // Teacher
            'teacher_id.required' => 'Guru wajib dipilih.',
            'teacher_id.integer' => 'ID guru tidak valid.',
            'teacher_id.exists' => 'Guru yang dipilih tidak ditemukan.',

            // Subject
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.integer' => 'ID mata pelajaran tidak valid.',
            'subject_id.exists' => 'Mata pelajaran yang dipilih tidak ditemukan.',

            // Class
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.integer' => 'ID kelas tidak valid.',
            'class_id.exists' => 'Kelas yang dipilih tidak ditemukan.',

            // Academic Year
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.integer' => 'ID tahun ajaran tidak valid.',
            'academic_year_id.exists' => 'Tahun ajaran yang dipilih tidak ditemukan.',

            // Day
            'hari.required' => 'Hari wajib dipilih.',
            'hari.in' => 'Hari yang dipilih tidak valid.',

            // Time
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid (HH:MM).',
            'jam_mulai.after_or_equal' => 'Jam mulai minimal pukul 07:00.',
            'jam_mulai.before' => 'Jam mulai harus sebelum pukul 16:00.',

            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid (HH:MM).',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'jam_selesai.before_or_equal' => 'Jam selesai maksimal pukul 16:00.',

            // Room
            'ruangan.string' => 'Ruangan harus berupa teks.',
            'ruangan.max' => 'Nama ruangan maksimal 50 karakter.',
        ];
    }

    /**
     * Get custom attributes for validator errors
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'teacher_id' => 'guru',
            'subject_id' => 'mata pelajaran',
            'class_id' => 'kelas',
            'academic_year_id' => 'tahun ajaran',
            'hari' => 'hari',
            'jam_mulai' => 'jam mulai',
            'jam_selesai' => 'jam selesai',
            'ruangan' => 'ruangan',
        ];
    }

    /**
     * Prepare the data for validation
     * Format jam dari input form ke format yang konsisten
     */
    protected function prepareForValidation(): void
    {
        // Pastikan jam dalam format H:i
        if ($this->jam_mulai && strlen($this->jam_mulai) === 5) {
            $this->merge(['jam_mulai' => $this->jam_mulai]);
        }

        if ($this->jam_selesai && strlen($this->jam_selesai) === 5) {
            $this->merge(['jam_selesai' => $this->jam_selesai]);
        }

        // Default is_active ke true jika tidak diset
        if (! $this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
