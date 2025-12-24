<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Services\AttendanceService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentAttendanceRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya teacher yang dapat input attendance
     * dan teacher tersebut berhak mengakses kelas yang dimaksud
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || $user->role !== 'TEACHER') {
            return false;
        }

        // Check via service apakah teacher bisa record attendance untuk kelas ini
        $service = app(AttendanceService::class);

        return $service->canRecordAttendance($user, $this->input('class_id'), $this->input('tanggal'));
    }

    /**
     * Validation rules untuk input attendance harian dengan validasi
     * untuk memastikan data siswa, kelas, dan status sesuai requirement
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:classes,id'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'exists:students,id'],
            'attendances.*.status' => ['required', Rule::in(['H', 'I', 'S', 'A'])],
            'attendances.*.keterangan' => ['nullable', 'string', 'max:500'],
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
            $attendances = $this->input('attendances', []);

            $studentIds = collect($attendances)->pluck('student_id');

            // Check duplicate student_id
            if ($studentIds->count() !== $studentIds->unique()->count()) {
                $validator->errors()->add('attendances', 'Terdapat siswa yang duplikat dalam daftar attendance.');
            }

            // Validate all students belong to the class
            $validStudents = Student::where('kelas_id', $classId)
                ->whereIn('id', $studentIds)
                ->pluck('id');

            if ($validStudents->count() !== $studentIds->count()) {
                $validator->errors()->add('attendances', 'Beberapa siswa tidak terdaftar di kelas yang dipilih.');
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
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
            'attendances.required' => 'Data attendance wajib diisi.',
            'attendances.array' => 'Format data attendance tidak valid.',
            'attendances.min' => 'Minimal harus ada 1 siswa yang diabsen.',
            'attendances.*.student_id.required' => 'ID siswa wajib diisi.',
            'attendances.*.student_id.exists' => 'Siswa tidak ditemukan.',
            'attendances.*.status.required' => 'Status attendance wajib diisi.',
            'attendances.*.status.in' => 'Status hanya boleh H (Hadir), I (Izin), S (Sakit), atau A (Alpha).',
            'attendances.*.keterangan.max' => 'Keterangan maksimal 500 karakter.',
        ];
    }
}
