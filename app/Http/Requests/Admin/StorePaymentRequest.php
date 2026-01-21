<?php

namespace App\Http\Requests\Admin;

use App\Models\Bill;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi pencatatan pembayaran
 *
 * Memvalidasi data pembayaran termasuk tagihan, nominal,
 * metode pembayaran, dan tanggal bayar
 */
class StorePaymentRequest extends FormRequest
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
            'bill_id' => [
                'required',
                'exists:bills,id',
                function ($attribute, $value, $fail) {
                    $bill = Bill::find($value);
                    if (! $bill) {
                        $fail('Tagihan tidak ditemukan.');

                        return;
                    }
                    if ($bill->status === 'lunas') {
                        $fail('Tagihan sudah lunas.');
                    }
                    if ($bill->status === 'dibatalkan') {
                        $fail('Tagihan sudah dibatalkan.');
                    }
                },
            ],
            'nominal' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    $billId = $this->input('bill_id');
                    if ($billId) {
                        $bill = Bill::find($billId);
                        if ($bill && $value > $bill->sisa_tagihan) {
                            $fail('Nominal pembayaran tidak boleh melebihi sisa tagihan (Rp '.number_format($bill->sisa_tagihan, 0, ',', '.').').');
                        }
                    }
                },
            ],
            'metode_pembayaran' => 'required|in:tunai,transfer,qris',
            'tanggal_bayar' => 'required|date|before_or_equal:today',
            'keterangan' => 'nullable|string|max:500',
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
            'bill_id.required' => 'Tagihan harus dipilih.',
            'bill_id.exists' => 'Tagihan tidak valid.',
            'nominal.required' => 'Nominal pembayaran harus diisi.',
            'nominal.numeric' => 'Nominal pembayaran harus berupa angka.',
            'nominal.min' => 'Nominal pembayaran minimal Rp 1.',
            'metode_pembayaran.required' => 'Metode pembayaran harus dipilih.',
            'metode_pembayaran.in' => 'Metode pembayaran tidak valid.',
            'tanggal_bayar.required' => 'Tanggal bayar harus diisi.',
            'tanggal_bayar.date' => 'Format tanggal tidak valid.',
            'tanggal_bayar.before_or_equal' => 'Tanggal bayar tidak boleh lebih dari hari ini.',
            'keterangan.max' => 'Keterangan maksimal 500 karakter.',
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
            'bill_id' => 'tagihan',
            'nominal' => 'nominal pembayaran',
            'metode_pembayaran' => 'metode pembayaran',
            'tanggal_bayar' => 'tanggal bayar',
            'keterangan' => 'keterangan',
        ];
    }
}
