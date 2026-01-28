<?php

namespace Database\Factories;

use App\Models\PsbDocument;
use App\Models\PsbRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk model PsbDocument
 * untuk generate dokumen pendaftaran dalam testing
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PsbDocument>
 */
class PsbDocumentFactory extends Factory
{
    /**
     * Define state default untuk dokumen PSB
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            PsbDocument::TYPE_BIRTH_CERTIFICATE,
            PsbDocument::TYPE_FAMILY_CARD,
            PsbDocument::TYPE_PARENT_ID,
            PsbDocument::TYPE_PHOTO,
        ]);

        return [
            'psb_registration_id' => PsbRegistration::factory(),
            'document_type' => $type,
            'file_path' => 'psb/documents/'.fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'status' => PsbDocument::STATUS_PENDING,
            'revision_note' => null,
        ];
    }

    /**
     * State untuk akte kelahiran
     */
    public function birthCertificate(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => PsbDocument::TYPE_BIRTH_CERTIFICATE,
            'original_name' => 'akte_kelahiran.pdf',
        ]);
    }

    /**
     * State untuk kartu keluarga
     */
    public function familyCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => PsbDocument::TYPE_FAMILY_CARD,
            'original_name' => 'kartu_keluarga.pdf',
        ]);
    }

    /**
     * State untuk KTP orang tua
     */
    public function parentId(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => PsbDocument::TYPE_PARENT_ID,
            'original_name' => 'ktp_ortu.pdf',
        ]);
    }

    /**
     * State untuk pas foto
     */
    public function photo(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => PsbDocument::TYPE_PHOTO,
            'file_path' => 'psb/documents/'.fake()->uuid().'.jpg',
            'original_name' => 'pas_foto_3x4.jpg',
        ]);
    }

    /**
     * State untuk dokumen yang sudah disetujui
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PsbDocument::STATUS_APPROVED,
        ]);
    }

    /**
     * State untuk dokumen yang ditolak
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PsbDocument::STATUS_REJECTED,
            'revision_note' => fake()->sentence(),
        ]);
    }
}
