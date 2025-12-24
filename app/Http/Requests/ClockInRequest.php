<?php

namespace App\Http\Requests;

use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ClockInRequest extends FormRequest
{
    /**
     * Authorization check untuk memastikan hanya teacher yang dapat clock in
     * dan belum clock in hari ini
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || $user->role !== 'TEACHER') {
            return false;
        }

        return true;
    }

    /**
     * Validation rules untuk clock in dengan GPS coordinates
     * yang memastikan latitude dan longitude dalam range valid
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Custom validation untuk check teacher belum clock in hari ini
     * untuk mencegah duplicate clock in
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $service = app(AttendanceService::class);
            $today = Carbon::today()->format('Y-m-d');

            if ($service->isAlreadyClockedIn($this->user(), $today)) {
                $validator->errors()->add('clock_in', 'Anda sudah clock in hari ini.');
            }
        });
    }

    /**
     * Custom error messages dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.required' => 'Latitude GPS wajib diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus antara -90 sampai 90.',
            'longitude.required' => 'Longitude GPS wajib diisi.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus antara -180 sampai 180.',
        ];
    }
}
