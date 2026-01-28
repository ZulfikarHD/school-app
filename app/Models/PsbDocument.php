<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PsbDocument Model - Dokumen persyaratan pendaftaran PSB
 *
 * Model ini bertujuan untuk menyimpan data dokumen yang diupload
 * sebagai persyaratan pendaftaran, yaitu: akte, KK, KTP, dan foto
 *
 * @property int $id
 * @property int $psb_registration_id
 * @property string $document_type
 * @property string $file_path
 * @property string $original_name
 * @property string $status
 * @property string|null $revision_note
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PsbDocument extends Model
{
    /** @use HasFactory<\Database\Factories\PsbDocumentFactory> */
    use HasFactory;

    /**
     * Konstanta untuk jenis dokumen yang dapat diupload
     */
    public const TYPE_BIRTH_CERTIFICATE = 'birth_certificate';

    public const TYPE_FAMILY_CARD = 'family_card';

    public const TYPE_PARENT_ID = 'parent_id';

    public const TYPE_PHOTO = 'photo';

    public const TYPE_OTHER = 'other';

    /**
     * Konstanta untuk status verifikasi dokumen
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /**
     * Daftar field yang dapat diisi secara mass assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'psb_registration_id',
        'document_type',
        'file_path',
        'original_name',
        'status',
        'revision_note',
    ];

    /**
     * Relasi belongs-to ke PsbRegistration dimana setiap dokumen
     * terkait dengan satu pendaftaran tertentu
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(PsbRegistration::class, 'psb_registration_id');
    }

    /**
     * Get semua jenis dokumen yang tersedia dengan label dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public static function getDocumentTypes(): array
    {
        return [
            self::TYPE_BIRTH_CERTIFICATE => 'Akte Kelahiran',
            self::TYPE_FAMILY_CARD => 'Kartu Keluarga',
            self::TYPE_PARENT_ID => 'KTP Orang Tua',
            self::TYPE_PHOTO => 'Pas Foto 3x4',
            self::TYPE_OTHER => 'Dokumen Lainnya',
        ];
    }

    /**
     * Get daftar dokumen wajib yang harus diupload
     *
     * @return array<string>
     */
    public static function getRequiredDocuments(): array
    {
        return [
            self::TYPE_BIRTH_CERTIFICATE,
            self::TYPE_FAMILY_CARD,
            self::TYPE_PARENT_ID,
            self::TYPE_PHOTO,
        ];
    }

    /**
     * Get label jenis dokumen dalam Bahasa Indonesia untuk display
     */
    public function getDocumentTypeLabel(): string
    {
        return self::getDocumentTypes()[$this->document_type] ?? $this->document_type;
    }

    /**
     * Get semua status yang tersedia dengan label dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
        ];
    }

    /**
     * Get label status dalam Bahasa Indonesia untuk display
     */
    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get URL untuk akses file dokumen
     */
    public function getFileUrl(): string
    {
        return asset('storage/'.$this->file_path);
    }
}
