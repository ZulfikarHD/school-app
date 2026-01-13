<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateLeaveRequestRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya parent yang dapat update
     * leave request miliknya sendiri dan hanya untuk PENDING status
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $leaveRequest = $this->route('leave_request');

        if (! $user || $user->role !== 'PARENT' || ! $leaveRequest) {
            return false;
        }

        // Hanya PENDING requests yang dapat diubah
        if ($leaveRequest->status !== 'PENDING') {
            return false;
        }

        // Validate student_id adalah anak dari parent ini
        $isParent = DB::table('student_guardian')
            ->join('guardians', 'student_guardian.guardian_id', '=', 'guardians.id')
            ->where('guardians.user_id', $user->id)
            ->where('student_guardian.student_id', $leaveRequest->student_id)
            ->exists();

        return $isParent;
    }

    /**
     * Validation rules untuk update permohonan izin/sakit
     * dengan overlap validation yang exclude current request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'jenis' => ['required', Rule::in(['IZIN', 'SAKIT'])],
            'tanggal_mulai' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $this->validateNoOverlap($value, $this->input('tanggal_selesai'), $fail);
                },
            ],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * Validasi untuk memastikan tidak ada overlap dengan leave request lain
     * dengan mengecualikan current request dari pengecekan
     */
    protected function validateNoOverlap(string $tanggalMulai, ?string $tanggalSelesai, callable $fail): void
    {
        if (! $tanggalSelesai || ! $this->input('student_id')) {
            return;
        }

        $leaveRequest = $this->route('leave_request');

        // Check untuk overlapping leave requests dengan status PENDING atau APPROVED
        // exclude current request dari pengecekan
        $hasOverlap = DB::table('leave_requests')
            ->where('student_id', $this->input('student_id'))
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->where('id', '!=', $leaveRequest->id) // Exclude current request
            ->where(function ($query) use ($tanggalMulai, $tanggalSelesai) {
                // Date range overlap logic: (new_start <= existing_end) AND (new_end >= existing_start)
                $query->whereRaw('? <= tanggal_selesai AND ? >= tanggal_mulai', [
                    $tanggalMulai,
                    $tanggalSelesai,
                ]);
            })
            ->exists();

        if ($hasOverlap) {
            $fail('Sudah ada permohonan izin untuk tanggal yang sama atau berdekatan. Silakan pilih tanggal lain.');
        }
    }

    /**
     * Custom error messages dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Siswa wajib dipilih.',
            'student_id.exists' => 'Siswa yang dipilih tidak valid.',
            'jenis.required' => 'Jenis permohonan wajib dipilih.',
            'jenis.in' => 'Jenis permohonan hanya boleh Izin atau Sakit.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'alasan.required' => 'Alasan wajib diisi.',
            'alasan.min' => 'Alasan minimal 10 karakter.',
            'alasan.max' => 'Alasan maksimal 1000 karakter.',
            'attachment.file' => 'Attachment harus berupa file.',
            'attachment.mimes' => 'Attachment hanya boleh berupa PDF, JPG, JPEG, atau PNG.',
            'attachment.max' => 'Ukuran attachment maksimal 2MB.',
        ];
    }
}
