<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model PaymentCategoryClassPrice untuk konfigurasi harga per kelas
 *
 * Tabel pivot yang menyimpan nominal khusus untuk kategori pembayaran
 * berdasarkan kelas tertentu (jika berbeda dari nominal_default)
 */
class PaymentCategoryClassPrice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_category_class_prices';

    /**
     * Mass assignable attributes
     *
     * @var list<string>
     */
    protected $fillable = [
        'payment_category_id',
        'class_id',
        'nominal',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
        ];
    }

    /**
     * Relationship many-to-one dengan PaymentCategory
     */
    public function paymentCategory(): BelongsTo
    {
        return $this->belongsTo(PaymentCategory::class);
    }

    /**
     * Relationship many-to-one dengan SchoolClass
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Helper method untuk format nominal ke Rupiah
     */
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp '.number_format($this->nominal, 0, ',', '.');
    }
}
