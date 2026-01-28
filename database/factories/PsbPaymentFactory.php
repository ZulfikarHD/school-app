<?php

namespace Database\Factories;

use App\Models\PsbPayment;
use App\Models\PsbRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk model PsbPayment
 * untuk generate data pembayaran PSB dalam testing
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PsbPayment>
 */
class PsbPaymentFactory extends Factory
{
    /**
     * Define state default untuk pembayaran PSB
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'psb_registration_id' => PsbRegistration::factory(),
            'payment_type' => PsbPayment::TYPE_REGISTRATION_FEE,
            'amount' => fake()->randomElement([100000, 150000, 200000]),
            'payment_method' => fake()->randomElement([
                PsbPayment::METHOD_TRANSFER,
                PsbPayment::METHOD_CASH,
                PsbPayment::METHOD_QRIS,
            ]),
            'proof_file_path' => 'psb/payments/'.fake()->uuid().'.jpg',
            'status' => PsbPayment::STATUS_PENDING,
            'verified_by' => null,
            'verified_at' => null,
            'notes' => null,
        ];
    }

    /**
     * State untuk biaya pendaftaran
     */
    public function registrationFee(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => PsbPayment::TYPE_REGISTRATION_FEE,
        ]);
    }

    /**
     * State untuk biaya daftar ulang
     */
    public function reRegistrationFee(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => PsbPayment::TYPE_RE_REGISTRATION_FEE,
            'amount' => fake()->randomElement([500000, 750000, 1000000]),
        ]);
    }

    /**
     * State untuk pembayaran via transfer
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PsbPayment::METHOD_TRANSFER,
        ]);
    }

    /**
     * State untuk pembayaran tunai
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PsbPayment::METHOD_CASH,
            'proof_file_path' => null,
        ]);
    }

    /**
     * State untuk pembayaran yang sudah diverifikasi
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PsbPayment::STATUS_VERIFIED,
            'verified_by' => User::factory(),
            'verified_at' => now(),
        ]);
    }

    /**
     * State untuk pembayaran yang ditolak
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PsbPayment::STATUS_REJECTED,
            'notes' => fake()->sentence(),
        ]);
    }
}
