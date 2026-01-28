<?php

namespace App\Http\Requests\Psb;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RequestRevisionRequest - Validasi form request revisi dokumen PSB
 *
 * Form request ini bertujuan untuk memvalidasi input permintaan revisi
 * dengan array dokumen yang memerlukan revisi beserta catatan masing-masing
 */
class RequestRevisionRequest extends FormRequest
{
    /**
     * Authorization check untuk admin role
     * Hanya admin yang dapat request revision dokumen
     */
    public function authorize(): bool
    {
        if (! $this->user()) {
            return false;
        }

        return in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk revision form
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $registrationId = $this->route('registration')->id;

        return [
            'documents' => ['required', 'array', 'min:1'],
            'documents.*.id' => [
                'required',
                'exists:psb_documents,id,psb_registration_id,'.$registrationId,
            ],
            'documents.*.revision_note' => ['required', 'string', 'max:500'],
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
            'documents.required' => 'Pilih minimal satu dokumen untuk revisi.',
            'documents.min' => 'Pilih minimal satu dokumen untuk revisi.',
            'documents.*.id.required' => 'ID dokumen wajib diisi.',
            'documents.*.id.exists' => 'Dokumen tidak ditemukan.',
            'documents.*.revision_note.required' => 'Catatan revisi wajib diisi untuk setiap dokumen.',
            'documents.*.revision_note.max' => 'Catatan revisi maksimal 500 karakter.',
        ];
    }
}
