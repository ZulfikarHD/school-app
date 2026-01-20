<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request untuk validasi pembuatan kategori pembayaran baru
 *
 * Memastikan data kategori pembayaran valid sebelum disimpan ke database
 */
class StorePaymentCategoryRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya Super Admin dan Admin (TU)
     * yang dapat membuat kategori pembayaran baru
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Validation rules untuk kategori pembayaran baru
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'min:2', 'max:100'],
            'kode' => ['required', 'string', 'min:2', 'max:20', 'alpha_dash', 'unique:payment_categories,kode'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'tipe' => ['required', Rule::in(['bulanan', 'tahunan', 'insidental'])],
            'nominal_default' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'is_active' => ['boolean'],
            'is_mandatory' => ['boolean'],
            'due_day' => ['nullable', 'integer', 'min:1', 'max:28'],
            'tahun_ajaran' => ['nullable', 'string', 'regex:/^\d{4}\/\d{4}$/'],

            // Optional: Class-specific pricing
            'class_prices' => ['nullable', 'array'],
            'class_prices.*.class_id' => ['required_with:class_prices', 'integer', 'exists:classes,id'],
            'class_prices.*.nominal' => ['required_with:class_prices', 'numeric', 'min:0'],
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
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.min' => 'Nama kategori minimal 2 karakter.',
            'nama.max' => 'Nama kategori maksimal 100 karakter.',

            'kode.required' => 'Kode kategori wajib diisi.',
            'kode.min' => 'Kode kategori minimal 2 karakter.',
            'kode.max' => 'Kode kategori maksimal 20 karakter.',
            'kode.alpha_dash' => 'Kode hanya boleh berisi huruf, angka, dash, dan underscore.',
            'kode.unique' => 'Kode kategori sudah digunakan.',

            'deskripsi.max' => 'Deskripsi maksimal 500 karakter.',

            'tipe.required' => 'Tipe pembayaran wajib dipilih.',
            'tipe.in' => 'Tipe pembayaran tidak valid.',

            'nominal_default.required' => 'Nominal default wajib diisi.',
            'nominal_default.numeric' => 'Nominal harus berupa angka.',
            'nominal_default.min' => 'Nominal tidak boleh negatif.',

            'due_day.integer' => 'Tanggal jatuh tempo harus berupa angka.',
            'due_day.min' => 'Tanggal jatuh tempo minimal 1.',
            'due_day.max' => 'Tanggal jatuh tempo maksimal 28.',

            'tahun_ajaran.regex' => 'Format tahun ajaran tidak valid (contoh: 2025/2026).',

            'class_prices.*.class_id.required_with' => 'Kelas wajib dipilih untuk harga khusus.',
            'class_prices.*.class_id.exists' => 'Kelas tidak ditemukan.',
            'class_prices.*.nominal.required_with' => 'Nominal wajib diisi untuk harga khusus.',
            'class_prices.*.nominal.numeric' => 'Nominal harus berupa angka.',
        ];
    }

    /**
     * Custom attribute names untuk pesan error yang lebih readable
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama' => 'nama kategori',
            'kode' => 'kode kategori',
            'deskripsi' => 'deskripsi',
            'tipe' => 'tipe pembayaran',
            'nominal_default' => 'nominal default',
            'due_day' => 'tanggal jatuh tempo',
            'tahun_ajaran' => 'tahun ajaran',
        ];
    }
}
