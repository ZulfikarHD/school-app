<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreSubjectAttendanceRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan teacher mengajar mata pelajaran
     * di kelas yang dimaksud sesuai dengan data di teacher_subjects
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || $user->role !== 'TEACHER') {
            return false;
        }

        // Check teacher mengajar subject di kelas ini
        return DB::table('teacher_subjects')
            ->where('teacher_id', $user->id)
            ->where('subject_id', $this->input('subject_id'))
            ->where('class_id', $this->input('class_id'))
            ->exists();
    }

    /**
     * Validation rules untuk input attendance per mata pelajaran
     * dengan validasi jam ke dan relasi subject, class, student
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'jam_ke' => ['required', 'integer', 'min:1', 'max:10'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'exists:students,id'],
            'attendances.*.status' => ['required', Rule::in(['H', 'I', 'S', 'A'])],
            'attendances.*.keterangan' => ['nullable', 'string', 'max:500'],
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
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists' => 'Kelas yang dipilih tidak valid.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
            'jam_ke.required' => 'Jam ke berapa wajib diisi.',
            'jam_ke.integer' => 'Jam ke harus berupa angka.',
            'jam_ke.min' => 'Jam ke minimal 1.',
            'jam_ke.max' => 'Jam ke maksimal 10.',
            'attendances.required' => 'Data attendance wajib diisi.',
            'attendances.*.student_id.required' => 'ID siswa wajib diisi.',
            'attendances.*.student_id.exists' => 'Siswa tidak ditemukan.',
            'attendances.*.status.required' => 'Status attendance wajib diisi.',
            'attendances.*.status.in' => 'Status hanya boleh H, I, S, atau A.',
            'attendances.*.keterangan.max' => 'Keterangan maksimal 500 karakter.',
        ];
    }
}
