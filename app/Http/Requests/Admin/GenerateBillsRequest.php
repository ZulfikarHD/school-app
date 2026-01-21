<?php

namespace App\Http\Requests\Admin;

use App\Models\PaymentCategory;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi generate tagihan bulk
 */
class GenerateBillsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_category_id' => [
                'required',
                'exists:payment_categories,id',
                function ($attribute, $value, $fail) {
                    $category = PaymentCategory::find($value);
                    if ($category && ! $category->is_active) {
                        $fail('Kategori pembayaran yang dipilih tidak aktif.');
                    }
                },
            ],
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:classes,id',
            'skip_duplicates' => 'nullable|boolean',
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
            'payment_category_id.required' => 'Kategori pembayaran harus dipilih.',
            'payment_category_id.exists' => 'Kategori pembayaran tidak valid.',
            'bulan.required' => 'Bulan harus dipilih.',
            'bulan.min' => 'Bulan tidak valid.',
            'bulan.max' => 'Bulan tidak valid.',
            'tahun.required' => 'Tahun harus dipilih.',
            'tahun.min' => 'Tahun minimal 2020.',
            'tahun.max' => 'Tahun maksimal 2100.',
            'class_ids.*.exists' => 'Salah satu kelas yang dipilih tidak valid.',
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
            'payment_category_id' => 'kategori pembayaran',
            'bulan' => 'bulan',
            'tahun' => 'tahun',
            'class_ids' => 'kelas',
        ];
    }
}
