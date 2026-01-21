<?php

namespace App\Http\Requests;

use App\Models\AttitudeGrade;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttitudeGradeRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya wali kelas
     * yang dapat input nilai sikap untuk kelasnya
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || $user->role !== 'TEACHER') {
            return false;
        }

        // Check apakah teacher adalah wali kelas dari class_id yang diminta
        $classId = $this->input('class_id');
        $tahunAjaran = $this->input('tahun_ajaran');

        $isWaliKelas = SchoolClass::where('id', $classId)
            ->where('wali_kelas_id', $user->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('is_active', true)
            ->exists();

        return $isWaliKelas;
    }

    /**
     * Validation rules untuk input nilai sikap
     * dengan validasi enum grades A/B/C/D
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $gradeOptions = array_keys(AttitudeGrade::getGradeOptions());

        return [
            'class_id' => ['required', 'exists:classes,id'],
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => ['required', Rule::in(['1', '2'])],
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.student_id' => ['required', 'exists:students,id'],
            'grades.*.spiritual_grade' => ['required', Rule::in($gradeOptions)],
            'grades.*.spiritual_description' => ['nullable', 'string', 'max:200'],
            'grades.*.social_grade' => ['required', Rule::in($gradeOptions)],
            'grades.*.social_description' => ['nullable', 'string', 'max:200'],
            'grades.*.homeroom_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom validation untuk memastikan semua siswa berada dalam kelas yang dipilih
     * dan tidak ada duplicate student_id
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $classId = $this->input('class_id');
            $grades = $this->input('grades', []);

            $studentIds = collect($grades)->pluck('student_id');

            // Check duplicate student_id
            if ($studentIds->count() !== $studentIds->unique()->count()) {
                $validator->errors()->add('grades', 'Terdapat siswa yang duplikat dalam daftar nilai sikap.');
            }

            // Validate all students belong to the class
            $validStudents = Student::where('kelas_id', $classId)
                ->whereIn('id', $studentIds)
                ->pluck('id');

            if ($validStudents->count() !== $studentIds->unique()->count()) {
                $validator->errors()->add('grades', 'Beberapa siswa tidak terdaftar di kelas yang dipilih.');
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
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists' => 'Kelas yang dipilih tidak valid.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.regex' => 'Format tahun ajaran tidak valid (contoh: 2024/2025).',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Semester hanya boleh 1 atau 2.',
            'grades.required' => 'Data nilai sikap wajib diisi.',
            'grades.array' => 'Format data nilai sikap tidak valid.',
            'grades.min' => 'Minimal harus ada 1 siswa yang dinilai.',
            'grades.*.student_id.required' => 'ID siswa wajib diisi.',
            'grades.*.student_id.exists' => 'Siswa tidak ditemukan.',
            'grades.*.spiritual_grade.required' => 'Nilai spiritual wajib dipilih.',
            'grades.*.spiritual_grade.in' => 'Nilai spiritual hanya boleh A, B, C, atau D.',
            'grades.*.spiritual_description.max' => 'Deskripsi spiritual maksimal 200 karakter.',
            'grades.*.social_grade.required' => 'Nilai sosial wajib dipilih.',
            'grades.*.social_grade.in' => 'Nilai sosial hanya boleh A, B, C, atau D.',
            'grades.*.social_description.max' => 'Deskripsi sosial maksimal 200 karakter.',
            'grades.*.homeroom_notes.max' => 'Catatan wali kelas maksimal 500 karakter.',
        ];
    }
}
