<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendance extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherAttendanceFactory> */
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara mass assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'teacher_id',
        'tanggal',
        'clock_in',
        'clock_out',
        'latitude_in',
        'longitude_in',
        'latitude_out',
        'longitude_out',
        'is_late',
        'status',
        'keterangan',
    ];

    /**
     * Cast atribut ke tipe data native
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'is_late' => 'boolean',
            'latitude_in' => 'decimal:8',
            'longitude_in' => 'decimal:8',
            'latitude_out' => 'decimal:8',
            'longitude_out' => 'decimal:8',
        ];
    }

    /**
     * Relasi ke guru yang melakukan presensi
     *
     * @return BelongsTo<User, TeacherAttendance>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Clock in guru dengan GPS coordinates
     * dan check apakah terlambat berdasarkan jam masuk sekolah (07:30)
     */
    public function clockIn(float $lat, float $lng): void
    {
        $clockInTime = Carbon::now();
        $schoolStartTime = Carbon::today()->setTime(7, 30);

        $isLate = $clockInTime->isAfter($schoolStartTime);

        $this->update([
            'clock_in' => $clockInTime->format('H:i:s'),
            'latitude_in' => $lat,
            'longitude_in' => $lng,
            'is_late' => $isLate,
            'status' => $isLate ? 'TERLAMBAT' : 'HADIR',
        ]);
    }

    /**
     * Clock out guru dengan GPS coordinates
     * untuk mencatat waktu pulang
     */
    public function clockOut(float $lat, float $lng): void
    {
        $clockOutTime = Carbon::now();

        $this->update([
            'clock_out' => $clockOutTime->format('H:i:s'),
            'latitude_out' => $lat,
            'longitude_out' => $lng,
        ]);
    }

    /**
     * Accessor untuk mendapatkan durasi kerja dalam format human-readable
     * yaitu: "8 jam 15 menit"
     *
     * @return Attribute<string|null, never>
     */
    protected function duration(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->clock_in || ! $this->clock_out) {
                    return null;
                }

                $clockIn = Carbon::parse($this->clock_in);
                $clockOut = Carbon::parse($this->clock_out);

                $diffInMinutes = $clockIn->diffInMinutes($clockOut);
                $hours = floor($diffInMinutes / 60);
                $minutes = $diffInMinutes % 60;

                if ($hours > 0 && $minutes > 0) {
                    return "{$hours} jam {$minutes} menit";
                } elseif ($hours > 0) {
                    return "{$hours} jam";
                } else {
                    return "{$minutes} menit";
                }
            },
        );
    }
}
