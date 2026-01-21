<?php

namespace App\Http\Requests\Parent;

use App\Models\Bill;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi submit pembayaran dari orang tua
 *
 * Memvalidasi data pembayaran termasuk tagihan yang dipilih,
 * bukti transfer, dan tanggal bayar
 */
class SubmitPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization dilakukan melalui validation rules untuk menampilkan error yang lebih baik
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bill_ids' => [
                'required',
                'array',
                'min:1',
                // Authorization check - verify parent owns these bills
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    if (! $user || $user->role !== 'PARENT') {
                        $fail('Anda tidak memiliki akses untuk melakukan pembayaran.');

                        return;
                    }

                    $guardian = $user->guardian;
                    if (! $guardian) {
                        $fail('Data orang tua tidak ditemukan.');

                        return;
                    }

                    $studentIds = $guardian->students()
                        ->where('status', 'aktif')
                        ->pluck('students.id')
                        ->toArray();

                    if (empty($studentIds)) {
                        $fail('Tidak ada siswa aktif yang terdaftar.');

                        return;
                    }

                    $validBills = Bill::whereIn('id', $value)
                        ->whereIn('student_id', $studentIds)
                        ->count();

                    if ($validBills !== count($value)) {
                        $fail('Beberapa tagihan tidak valid atau bukan milik anak Anda.');
                    }
                },
            ],
            'bill_ids.*' => [
                'required',
                'exists:bills,id',
                function ($attribute, $value, $fail) {
                    $bill = Bill::find($value);
                    if (! $bill) {
                        $fail('Tagihan tidak ditemukan.');

                        return;
                    }
                    if ($bill->status === 'lunas') {
                        $fail("Tagihan {$bill->nomor_tagihan} sudah lunas.");
                    }
                    if ($bill->status === 'dibatalkan') {
                        $fail("Tagihan {$bill->nomor_tagihan} sudah dibatalkan.");
                    }
                    // Check if there's pending payment for this bill
                    $hasPendingPayment = $bill->payments()
                        ->where('status', 'pending')
                        ->exists();
                    if ($hasPendingPayment) {
                        $fail("Tagihan {$bill->nomor_tagihan} sudah memiliki pembayaran yang menunggu verifikasi.");
                    }
                },
            ],
            'bukti_transfer' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120', // 5MB
            ],
            'tanggal_bayar' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'catatan' => [
                'nullable',
                'string',
                'max:500',
            ],
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
            'bill_ids.required' => 'Pilih minimal satu tagihan untuk dibayar.',
            'bill_ids.array' => 'Format tagihan tidak valid.',
            'bill_ids.min' => 'Pilih minimal satu tagihan untuk dibayar.',
            'bill_ids.*.required' => 'ID tagihan tidak valid.',
            'bill_ids.*.exists' => 'Tagihan tidak ditemukan.',
            'bukti_transfer.required' => 'Bukti transfer harus diupload.',
            'bukti_transfer.file' => 'Bukti transfer harus berupa file.',
            'bukti_transfer.mimes' => 'Bukti transfer harus berupa file JPG, PNG, atau PDF.',
            'bukti_transfer.max' => 'Ukuran bukti transfer maksimal 5MB.',
            'tanggal_bayar.required' => 'Tanggal bayar harus diisi.',
            'tanggal_bayar.date' => 'Format tanggal tidak valid.',
            'tanggal_bayar.before_or_equal' => 'Tanggal bayar tidak boleh lebih dari hari ini.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
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
            'bill_ids' => 'tagihan',
            'bukti_transfer' => 'bukti transfer',
            'tanggal_bayar' => 'tanggal bayar',
            'catatan' => 'catatan',
        ];
    }
}
