<?php

namespace App\Rules;

use App\Models\PasswordHistory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class NotInPasswordHistory implements ValidationRule
{
    /**
     * Create a new rule instance untuk check password history
     *
     * @param  int  $userId  User ID yang akan dicheck
     * @param  int  $historyCount  Jumlah password history yang akan dicheck (default: 3)
     */
    public function __construct(
        private int $userId,
        private int $historyCount = 3
    ) {}

    /**
     * Run the validation rule untuk memastikan password tidak sama
     * dengan N password terakhir yang digunakan, untuk enforce
     * password rotation dan prevent reuse password lama
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Ambil N password terakhir dari history
        $recentPasswords = PasswordHistory::where('user_id', $this->userId)
            ->latest()
            ->limit($this->historyCount)
            ->pluck('password');

        // Check apakah password baru sama dengan salah satu password lama
        foreach ($recentPasswords as $hashedPassword) {
            if (Hash::check($value, $hashedPassword)) {
                $fail("Password tidak boleh sama dengan {$this->historyCount} password terakhir Anda.");

                return;
            }
        }
    }
}
