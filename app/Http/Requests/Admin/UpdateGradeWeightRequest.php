<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateGradeWeightRequest - Validasi untuk update konfigurasi bobot nilai
 * dengan validasi total bobot harus = 100%
 */
class UpdateGradeWeightRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Hanya ADMIN yang bisa mengakses fitur ini
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->role === 'ADMIN';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'uh_weight' => ['required', 'integer', 'min:0', 'max:100'],
            'uts_weight' => ['required', 'integer', 'min:0', 'max:100'],
            'uas_weight' => ['required', 'integer', 'min:0', 'max:100'],
            'praktik_weight' => ['required', 'integer', 'min:0', 'max:100'],
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
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'tahun_ajaran.regex' => 'Format tahun ajaran tidak valid. Gunakan format: 2024/2025',
            'uh_weight.required' => 'Bobot Ulangan Harian harus diisi.',
            'uh_weight.integer' => 'Bobot Ulangan Harian harus berupa angka.',
            'uh_weight.min' => 'Bobot Ulangan Harian minimal 0%.',
            'uh_weight.max' => 'Bobot Ulangan Harian maksimal 100%.',
            'uts_weight.required' => 'Bobot UTS harus diisi.',
            'uts_weight.integer' => 'Bobot UTS harus berupa angka.',
            'uts_weight.min' => 'Bobot UTS minimal 0%.',
            'uts_weight.max' => 'Bobot UTS maksimal 100%.',
            'uas_weight.required' => 'Bobot UAS harus diisi.',
            'uas_weight.integer' => 'Bobot UAS harus berupa angka.',
            'uas_weight.min' => 'Bobot UAS minimal 0%.',
            'uas_weight.max' => 'Bobot UAS maksimal 100%.',
            'praktik_weight.required' => 'Bobot Praktik harus diisi.',
            'praktik_weight.integer' => 'Bobot Praktik harus berupa angka.',
            'praktik_weight.min' => 'Bobot Praktik minimal 0%.',
            'praktik_weight.max' => 'Bobot Praktik maksimal 100%.',
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
            'tahun_ajaran' => 'Tahun Ajaran',
            'uh_weight' => 'Bobot Ulangan Harian',
            'uts_weight' => 'Bobot UTS',
            'uas_weight' => 'Bobot UAS',
            'praktik_weight' => 'Bobot Praktik',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $total = (int) $this->uh_weight
                + (int) $this->uts_weight
                + (int) $this->uas_weight
                + (int) $this->praktik_weight;

            if ($total !== 100) {
                $validator->errors()->add(
                    'total_weight',
                    "Total bobot harus 100%, saat ini: {$total}%"
                );
            }
        });
    }
}
