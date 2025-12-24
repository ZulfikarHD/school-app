<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherLeaveRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya teacher yang dapat
     * submit permohonan cuti
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->role === 'TEACHER';
    }

    /**
     * Validation rules untuk permohonan cuti guru dengan jenis cuti,
     * date range, dan attachment yang optional
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::in(['IZIN', 'SAKIT', 'CUTI'])],
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
            'jenis.required' => 'Jenis permohonan wajib dipilih.',
            'jenis.in' => 'Jenis permohonan hanya boleh Izin, Sakit, atau Cuti.',
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
