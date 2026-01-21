<?php

namespace App\Http\Requests;

use App\Models\Grade;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya teacher yang menginput
     * nilai yang dapat mengedit, dan nilai belum dikunci
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $grade = $this->route('grade');

        if (! $user || $user->role !== 'TEACHER') {
            return false;
        }

        // Check apakah teacher yang menginput nilai
        if ($grade->teacher_id !== $user->id) {
            return false;
        }

        // Check apakah nilai sudah dikunci
        if ($grade->is_locked) {
            return false;
        }

        return true;
    }

    /**
     * Validation rules untuk update nilai dengan validasi
     * score 0-100 dan required fields
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'assessment_date' => ['required', 'date', 'before_or_equal:today'],
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.id' => ['required', 'exists:grades,id'],
            'grades.*.score' => ['required', 'numeric', 'min:0', 'max:100'],
            'grades.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom validation untuk memastikan semua grade IDs
     * adalah milik teacher yang sedang login
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $grades = $this->input('grades', []);
            $gradeIds = collect($grades)->pluck('id');

            // Verify all grades belong to the teacher
            $validGrades = Grade::whereIn('id', $gradeIds)
                ->where('teacher_id', $user->id)
                ->count();

            if ($validGrades !== $gradeIds->count()) {
                $validator->errors()->add('grades', 'Beberapa data nilai tidak valid atau bukan milik Anda.');
            }

            // Check if any grade is locked
            $lockedGrades = Grade::whereIn('id', $gradeIds)
                ->where('is_locked', true)
                ->exists();

            if ($lockedGrades) {
                $validator->errors()->add('grades', 'Beberapa nilai sudah dikunci dan tidak dapat diedit.');
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
            'title.required' => 'Judul penilaian wajib diisi.',
            'title.max' => 'Judul penilaian maksimal 200 karakter.',
            'assessment_date.required' => 'Tanggal penilaian wajib diisi.',
            'assessment_date.date' => 'Format tanggal tidak valid.',
            'assessment_date.before_or_equal' => 'Tanggal penilaian tidak boleh lebih dari hari ini.',
            'grades.required' => 'Data nilai wajib diisi.',
            'grades.array' => 'Format data nilai tidak valid.',
            'grades.min' => 'Minimal harus ada 1 siswa yang dinilai.',
            'grades.*.id.required' => 'ID nilai wajib diisi.',
            'grades.*.id.exists' => 'Data nilai tidak ditemukan.',
            'grades.*.score.required' => 'Nilai wajib diisi.',
            'grades.*.score.numeric' => 'Nilai harus berupa angka.',
            'grades.*.score.min' => 'Nilai minimal 0.',
            'grades.*.score.max' => 'Nilai maksimal 100.',
            'grades.*.notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
