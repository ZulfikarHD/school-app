<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PsbSetting Model - Konfigurasi periode pendaftaran siswa baru
 *
 * Model ini bertujuan untuk menyimpan pengaturan PSB per tahun ajaran,
 * yaitu: tanggal buka/tutup pendaftaran, pengumuman, kuota, dan biaya
 *
 * @property int $id
 * @property int $academic_year_id
 * @property \Carbon\Carbon $registration_open_date
 * @property \Carbon\Carbon $registration_close_date
 * @property \Carbon\Carbon $announcement_date
 * @property int $re_registration_deadline_days
 * @property int $registration_fee
 * @property int $quota_per_class
 * @property bool $waiting_list_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PsbSetting extends Model
{
    /** @use HasFactory<\Database\Factories\PsbSettingFactory> */
    use HasFactory;

    /**
     * Daftar field yang dapat diisi secara mass assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'academic_year_id',
        'registration_open_date',
        'registration_close_date',
        'announcement_date',
        're_registration_deadline_days',
        'registration_fee',
        'quota_per_class',
        'waiting_list_enabled',
    ];

    /**
     * Definisi cast untuk kolom-kolom yang memerlukan transformasi tipe data
     * dengan tanggal-tanggal penting dan fee dalam format yang sesuai
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registration_open_date' => 'date',
            'registration_close_date' => 'date',
            'announcement_date' => 'date',
            're_registration_deadline_days' => 'integer',
            'registration_fee' => 'integer',
            'quota_per_class' => 'integer',
            'waiting_list_enabled' => 'boolean',
        ];
    }

    /**
     * Relasi belongs-to ke AcademicYear dimana setiap setting
     * terkait dengan satu tahun ajaran tertentu
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Check apakah periode pendaftaran sedang buka berdasarkan tanggal saat ini
     * dengan validasi tanggal buka dan tutup pendaftaran
     */
    public function isRegistrationOpen(): bool
    {
        $now = now()->startOfDay();

        return $now->greaterThanOrEqualTo($this->registration_open_date)
            && $now->lessThanOrEqualTo($this->registration_close_date);
    }

    /**
     * Get tanggal deadline daftar ulang yang dihitung dari tanggal pengumuman
     * ditambah jumlah hari yang dikonfigurasi
     */
    public function getReRegistrationDeadline(): \Carbon\Carbon
    {
        return $this->announcement_date->copy()->addDays($this->re_registration_deadline_days);
    }

    /**
     * Format biaya pendaftaran dalam format Rupiah untuk display
     */
    public function getFormattedRegistrationFee(): string
    {
        return 'Rp '.number_format($this->registration_fee, 0, ',', '.');
    }
}
