<?php

namespace App\Http\Requests;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreGradeRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya teacher yang dapat input nilai
     * dan teacher tersebut berhak mengakses kelas dan mapel yang dimaksud
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || $user->role !== 'TEACHER') {
            return false;
        }

        // Check apakah teacher mengajar mapel di kelas tersebut
        $classId = $this->input('class_id');
        $subjectId = $this->input('subject_id');
        $tahunAjaran = $this->input('tahun_ajaran');

        $hasAccess = DB::table('teacher_subjects')
            ->where('teacher_id', $user->id)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->exists();

        return $hasAccess;
    }

    /**
     * Validation rules untuk input nilai dengan validasi
     * score 0-100 dan required fields
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => ['required', Rule::in(['1', '2'])],
            'assessment_type' => ['required', Rule::in(array_keys(Grade::getAssessmentTypes()))],
            'assessment_number' => ['nullable', 'integer', 'min:1', 'max:10'],
            'title' => ['required', 'string', 'max:200'],
            'assessment_date' => ['required', 'date', 'before_or_equal:today'],
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.student_id' => ['required', 'exists:students,id'],
            'grades.*.score' => ['required', 'numeric', 'min:0', 'max:100'],
            'grades.*.notes' => ['nullable', 'string', 'max:500'],
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
                $validator->errors()->add('grades', 'Terdapat siswa yang duplikat dalam daftar nilai.');
            }

            // Validate all students belong to the class
            $validStudents = Student::where('kelas_id', $classId)
                ->whereIn('id', $studentIds)
                ->pluck('id');

            if ($validStudents->count() !== $studentIds->unique()->count()) {
                $validator->errors()->add('grades', 'Beberapa siswa tidak terdaftar di kelas yang dipilih.');
            }

            // Check for duplicate assessment (same class, subject, type, number, title, date)
            $assessmentType = $this->input('assessment_type');
            $assessmentNumber = $this->input('assessment_number');
            $title = $this->input('title');
            $assessmentDate = $this->input('assessment_date');
            $subjectId = $this->input('subject_id');
            $tahunAjaran = $this->input('tahun_ajaran');
            $semester = $this->input('semester');

            $existingAssessment = Grade::where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester)
                ->where('assessment_type', $assessmentType)
                ->where('title', $title)
                ->where('assessment_date', $assessmentDate)
                ->exists();

            if ($existingAssessment) {
                $validator->errors()->add('title', 'Penilaian dengan judul dan tanggal yang sama sudah ada.');
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
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran yang dipilih tidak valid.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.regex' => 'Format tahun ajaran tidak valid (contoh: 2024/2025).',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Semester hanya boleh 1 atau 2.',
            'assessment_type.required' => 'Jenis penilaian wajib dipilih.',
            'assessment_type.in' => 'Jenis penilaian tidak valid.',
            'assessment_number.integer' => 'Nomor penilaian harus berupa angka.',
            'assessment_number.min' => 'Nomor penilaian minimal 1.',
            'assessment_number.max' => 'Nomor penilaian maksimal 10.',
            'title.required' => 'Judul penilaian wajib diisi.',
            'title.max' => 'Judul penilaian maksimal 200 karakter.',
            'assessment_date.required' => 'Tanggal penilaian wajib diisi.',
            'assessment_date.date' => 'Format tanggal tidak valid.',
            'assessment_date.before_or_equal' => 'Tanggal penilaian tidak boleh lebih dari hari ini.',
            'grades.required' => 'Data nilai wajib diisi.',
            'grades.array' => 'Format data nilai tidak valid.',
            'grades.min' => 'Minimal harus ada 1 siswa yang dinilai.',
            'grades.*.student_id.required' => 'ID siswa wajib diisi.',
            'grades.*.student_id.exists' => 'Siswa tidak ditemukan.',
            'grades.*.score.required' => 'Nilai wajib diisi.',
            'grades.*.score.numeric' => 'Nilai harus berupa angka.',
            'grades.*.score.min' => 'Nilai minimal 0.',
            'grades.*.score.max' => 'Nilai maksimal 100.',
            'grades.*.notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
