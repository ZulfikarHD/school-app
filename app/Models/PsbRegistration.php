<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PsbRegistration Model - Data pendaftaran calon siswa baru
 *
 * Model ini bertujuan untuk menyimpan data lengkap pendaftaran PSB,
 * yaitu: biodata siswa, data orang tua, dan status proses pendaftaran
 *
 * @property int $id
 * @property string $registration_number
 * @property int $academic_year_id
 * @property string $status
 * @property string $student_name
 * @property string $student_nik
 * @property string $birth_place
 * @property \Carbon\Carbon $birth_date
 * @property string $gender
 * @property string $religion
 * @property string $address
 * @property int $child_order
 * @property string|null $origin_school
 * @property string $father_name
 * @property string $father_nik
 * @property string $father_occupation
 * @property string $father_phone
 * @property string|null $father_email
 * @property string $mother_name
 * @property string $mother_nik
 * @property string $mother_occupation
 * @property string|null $mother_phone
 * @property string|null $mother_email
 * @property string|null $notes
 * @property string|null $rejection_reason
 * @property int|null $verified_by
 * @property \Carbon\Carbon|null $verified_at
 * @property \Carbon\Carbon|null $announced_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PsbRegistration extends Model
{
    /** @use HasFactory<\Database\Factories\PsbRegistrationFactory> */
    use HasFactory;

    /**
     * Konstanta untuk status pendaftaran yang tersedia
     * dengan deskripsi masing-masing tahapan proses
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_DOCUMENT_REVIEW = 'document_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_WAITING_LIST = 'waiting_list';

    public const STATUS_RE_REGISTRATION = 're_registration';

    public const STATUS_COMPLETED = 'completed';

    /**
     * Daftar field yang dapat diisi secara mass assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registration_number',
        'academic_year_id',
        'status',
        'student_name',
        'student_nik',
        'birth_place',
        'birth_date',
        'gender',
        'religion',
        'address',
        'child_order',
        'origin_school',
        'father_name',
        'father_nik',
        'father_occupation',
        'father_phone',
        'father_email',
        'mother_name',
        'mother_nik',
        'mother_occupation',
        'mother_phone',
        'mother_email',
        'notes',
        'rejection_reason',
        'verified_by',
        'verified_at',
        'announced_at',
    ];

    /**
     * Definisi cast untuk kolom-kolom yang memerlukan transformasi tipe data
     * dengan tanggal lahir dan timestamps dalam format Carbon
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'child_order' => 'integer',
            'verified_at' => 'datetime',
            'announced_at' => 'datetime',
        ];
    }

    /**
     * Relasi belongs-to ke AcademicYear dimana setiap pendaftaran
     * terkait dengan satu tahun ajaran tertentu
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relasi belongs-to ke User untuk verifikator
     * yang memverifikasi pendaftaran ini
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Relasi one-to-many ke PsbDocument untuk dokumen-dokumen
     * yang diupload sebagai syarat pendaftaran
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PsbDocument::class);
    }

    /**
     * Relasi one-to-many ke PsbPayment untuk pembayaran
     * biaya pendaftaran dan daftar ulang
     */
    public function payments(): HasMany
    {
        return $this->hasMany(PsbPayment::class);
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
            self::STATUS_DOCUMENT_REVIEW => 'Verifikasi Dokumen',
            self::STATUS_APPROVED => 'Diterima',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_WAITING_LIST => 'Waiting List',
            self::STATUS_RE_REGISTRATION => 'Daftar Ulang',
            self::STATUS_COMPLETED => 'Selesai',
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
     * Scope untuk filter berdasarkan tahun ajaran
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PsbRegistration>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PsbRegistration>
     */
    public function scopeForAcademicYear($query, int $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope untuk filter berdasarkan status
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PsbRegistration>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PsbRegistration>
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk mencari berdasarkan nomor pendaftaran atau nama siswa
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PsbRegistration>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PsbRegistration>
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('registration_number', 'like', "%{$search}%")
                ->orWhere('student_name', 'like', "%{$search}%")
                ->orWhere('student_nik', 'like', "%{$search}%");
        });
    }

    /**
     * Check apakah pendaftaran dapat melakukan daftar ulang
     * yaitu jika status approved dan sudah diumumkan
     *
     * @return bool True jika dapat daftar ulang
     */
    public function canReRegister(): bool
    {
        return $this->status === self::STATUS_APPROVED
            && $this->announced_at !== null;
    }

    /**
     * Check apakah semua pembayaran sudah terverifikasi
     * untuk menentukan apakah sudah siap untuk create student
     *
     * @return bool True jika semua pembayaran terverifikasi
     */
    public function allPaymentsVerified(): bool
    {
        // Harus ada minimal satu pembayaran
        if ($this->payments()->count() === 0) {
            return false;
        }

        // Semua pembayaran harus status verified
        return $this->payments()
            ->where('status', '!=', PsbPayment::STATUS_VERIFIED)
            ->doesntExist();
    }

    /**
     * Check apakah ada pembayaran yang pending verifikasi
     *
     * @return bool True jika ada pembayaran pending
     */
    public function hasPendingPayment(): bool
    {
        return $this->payments()
            ->where('status', PsbPayment::STATUS_PENDING)
            ->exists();
    }

    /**
     * Get total pembayaran yang sudah terverifikasi
     *
     * @return int Total dalam rupiah
     */
    public function getTotalVerifiedPayment(): int
    {
        return (int) $this->payments()
            ->where('status', PsbPayment::STATUS_VERIFIED)
            ->sum('amount');
    }
}
