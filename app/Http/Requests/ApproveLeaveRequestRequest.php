<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveLeaveRequestRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya teacher (wali kelas),
     * admin, atau principal yang dapat approve/reject leave request
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        // Admin dan Principal selalu bisa approve/reject
        if (in_array($user->role, ['SUPERADMIN', 'ADMIN', 'PRINCIPAL'])) {
            return true;
        }

        // Teacher hanya bisa approve/reject untuk siswa di kelasnya
        if ($user->role === 'TEACHER') {
            $leaveRequest = $this->route('leaveRequest');

            return $leaveRequest->student->kelas->wali_kelas_id === $user->id;
        }

        return false;
    }

    /**
     * Validation rules untuk approve/reject leave request
     * dengan required rejection_reason jika action adalah reject
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'rejection_reason' => ['required_if:action,reject', 'string', 'min:10', 'max:500'],
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
            'action.required' => 'Action wajib dipilih.',
            'action.in' => 'Action hanya boleh approve atau reject.',
            'rejection_reason.required_if' => 'Alasan penolakan wajib diisi jika permohonan ditolak.',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ];
    }
}
