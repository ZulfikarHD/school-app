<?php

namespace App\Http\Requests\Psb;

use App\Services\PsbService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SubmitReRegistrationRequest - Validasi form daftar ulang PSB
 *
 * Form request ini bertujuan untuk memvalidasi data tambahan
 * yang disubmit oleh parent saat proses daftar ulang
 */
class SubmitReRegistrationRequest extends FormRequest
{
    /**
     * Authorization check untuk parent role dengan status announced
     * Hanya parent dengan pendaftaran yang sudah diumumkan yang dapat daftar ulang
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || $user->role !== 'PARENT') {
            return false;
        }

        // Check apakah parent memiliki pendaftaran yang bisa daftar ulang
        $psbService = app(PsbService::class);
        $registration = $psbService->getParentRegistration($user);

        if (! $registration) {
            return false;
        }

        return $registration->canReRegister();
    }

    /**
     * Validation rules untuk submit re-registration form
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Data tambahan siswa jika diperlukan
            'student_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact' => ['nullable', 'string', 'max:100'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
            'blood_type' => ['nullable', 'string', 'in:A,B,AB,O'],
            'medical_conditions' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'student_phone.max' => 'Nomor telepon siswa maksimal 20 karakter.',
            'emergency_contact.max' => 'Nama kontak darurat maksimal 100 karakter.',
            'emergency_phone.max' => 'Nomor telepon darurat maksimal 20 karakter.',
            'blood_type.in' => 'Golongan darah harus A, B, AB, atau O.',
            'medical_conditions.max' => 'Riwayat kesehatan maksimal 500 karakter.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }

    /**
     * Custom attribute names dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_phone' => 'nomor telepon siswa',
            'emergency_contact' => 'kontak darurat',
            'emergency_phone' => 'nomor telepon darurat',
            'blood_type' => 'golongan darah',
            'medical_conditions' => 'riwayat kesehatan',
            'notes' => 'catatan',
        ];
    }
}
