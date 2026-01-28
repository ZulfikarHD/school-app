<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PsbPayment Model - Data pembayaran PSB
 *
 * Model ini bertujuan untuk menyimpan data pembayaran biaya pendaftaran
 * dan daftar ulang PSB dengan status verifikasi
 *
 * @property int $id
 * @property int $psb_registration_id
 * @property string $payment_type
 * @property int $amount
 * @property string $payment_method
 * @property string|null $proof_file_path
 * @property string $status
 * @property int|null $verified_by
 * @property \Carbon\Carbon|null $verified_at
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PsbPayment extends Model
{
    /** @use HasFactory<\Database\Factories\PsbPaymentFactory> */
    use HasFactory;

    /**
     * Konstanta untuk jenis pembayaran PSB
     */
    public const TYPE_REGISTRATION_FEE = 'registration_fee';

    public const TYPE_RE_REGISTRATION_FEE = 're_registration_fee';

    /**
     * Konstanta untuk metode pembayaran
     */
    public const METHOD_TRANSFER = 'transfer';

    public const METHOD_CASH = 'cash';

    public const METHOD_QRIS = 'qris';

    /**
     * Konstanta untuk status pembayaran
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    /**
     * Daftar field yang dapat diisi secara mass assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'psb_registration_id',
        'payment_type',
        'amount',
        'payment_method',
        'proof_file_path',
        'status',
        'verified_by',
        'verified_at',
        'notes',
    ];

    /**
     * Definisi cast untuk kolom-kolom yang memerlukan transformasi tipe data
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Relasi belongs-to ke PsbRegistration dimana setiap pembayaran
     * terkait dengan satu pendaftaran tertentu
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(PsbRegistration::class, 'psb_registration_id');
    }

    /**
     * Relasi belongs-to ke User untuk verifikator
     * yang memverifikasi pembayaran ini
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get semua jenis pembayaran yang tersedia dengan label dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public static function getPaymentTypes(): array
    {
        return [
            self::TYPE_REGISTRATION_FEE => 'Biaya Pendaftaran',
            self::TYPE_RE_REGISTRATION_FEE => 'Biaya Daftar Ulang',
        ];
    }

    /**
     * Get semua metode pembayaran yang tersedia dengan label dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_TRANSFER => 'Transfer Bank',
            self::METHOD_CASH => 'Tunai',
            self::METHOD_QRIS => 'QRIS',
        ];
    }

    /**
     * Get semua status pembayaran yang tersedia dengan label dalam Bahasa Indonesia
     *
     * @return array<string, string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_VERIFIED => 'Terverifikasi',
            self::STATUS_REJECTED => 'Ditolak',
        ];
    }

    /**
     * Get label jenis pembayaran dalam Bahasa Indonesia untuk display
     */
    public function getPaymentTypeLabel(): string
    {
        return self::getPaymentTypes()[$this->payment_type] ?? $this->payment_type;
    }

    /**
     * Get label metode pembayaran dalam Bahasa Indonesia untuk display
     */
    public function getPaymentMethodLabel(): string
    {
        return self::getPaymentMethods()[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get label status dalam Bahasa Indonesia untuk display
     */
    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Format jumlah pembayaran dalam format Rupiah untuk display
     */
    public function getFormattedAmount(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get URL untuk akses bukti pembayaran
     */
    public function getProofUrl(): ?string
    {
        if (! $this->proof_file_path) {
            return null;
        }

        return asset('storage/'.$this->proof_file_path);
    }
}
