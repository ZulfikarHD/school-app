<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreLeaveRequestRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya parent yang dapat submit
     * leave request dan hanya untuk anaknya sendiri
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || $user->role !== 'PARENT') {
            return false;
        }

        // Validate student_id adalah anak dari parent ini
        $isParent = DB::table('student_guardian')
            ->join('guardians', 'student_guardian.guardian_id', '=', 'guardians.id')
            ->where('guardians.user_id', $user->id)
            ->where('student_guardian.student_id', $this->input('student_id'))
            ->exists();

        return $isParent;
    }

    /**
     * Validation rules untuk permohonan izin/sakit dengan upload attachment
     * dan validasi date range yang reasonable
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'jenis' => ['required', Rule::in(['IZIN', 'SAKIT'])],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
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
            'student_id.required' => 'Siswa wajib dipilih.',
            'student_id.exists' => 'Siswa yang dipilih tidak valid.',
            'jenis.required' => 'Jenis permohonan wajib dipilih.',
            'jenis.in' => 'Jenis permohonan hanya boleh Izin atau Sakit.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini.',
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
