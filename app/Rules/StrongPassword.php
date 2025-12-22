<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule untuk memastikan password complexity
     * sesuai best practice security yaitu: minimal 8 karakter, kombinasi
     * huruf besar/kecil, angka, dan karakter khusus untuk prevent brute force
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check minimum length
        if (strlen($value) < 8) {
            $fail('Password minimal 8 karakter.');

            return;
        }

        // Check untuk huruf besar
        if (! preg_match('/[A-Z]/', $value)) {
            $fail('Password harus mengandung minimal 1 huruf besar.');

            return;
        }

        // Check untuk huruf kecil
        if (! preg_match('/[a-z]/', $value)) {
            $fail('Password harus mengandung minimal 1 huruf kecil.');

            return;
        }

        // Check untuk angka
        if (! preg_match('/[0-9]/', $value)) {
            $fail('Password harus mengandung minimal 1 angka.');

            return;
        }

        // Check untuk special character
        if (! preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
            $fail('Password harus mengandung minimal 1 karakter khusus (!@#$%^&*(),.?":{}|<>).');

            return;
        }

        // Check common passwords untuk prevent weak passwords
        $commonPasswords = [
            'password', '12345678', 'qwerty123', 'password123',
            'admin123', 'welcome123', 'letmein123', '11111111',
        ];

        if (in_array(strtolower($value), $commonPasswords)) {
            $fail('Password terlalu umum. Gunakan kombinasi yang lebih unik.');

            return;
        }
    }
}
