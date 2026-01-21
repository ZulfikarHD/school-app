<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * GenerateReportCardRequest - Form request untuk validasi
 * generate rapor bulk dengan validasi kelas dan periode
 */
class GenerateReportCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && in_array($user->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['required', 'integer', 'exists:classes,id'],
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => ['required', 'in:1,2'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'class_ids.required' => 'Pilih minimal satu kelas untuk generate rapor.',
            'class_ids.array' => 'Format kelas tidak valid.',
            'class_ids.min' => 'Pilih minimal satu kelas untuk generate rapor.',
            'class_ids.*.exists' => 'Kelas yang dipilih tidak valid.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.regex' => 'Format tahun ajaran tidak valid (contoh: 2025/2026).',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Semester harus 1 atau 2.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'class_ids' => 'Kelas',
            'tahun_ajaran' => 'Tahun Ajaran',
            'semester' => 'Semester',
        ];
    }
}
