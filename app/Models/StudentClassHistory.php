<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentClassHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_class_history';

    /**
     * Mass assignable attributes untuk riwayat kelas siswa
     * yang mencakup kelas, tahun ajaran, dan wali kelas
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'kelas_id',
        'tahun_ajaran',
        'wali_kelas',
    ];

    /**
     * Relationship dengan Student untuk link history ke siswa tertentu
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Scope query untuk filter history berdasarkan tahun ajaran
     */
    public function scopeByAcademicYear($query, $tahunAjaran)
    {
        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Scope query untuk filter history berdasarkan kelas
     */
    public function scopeByClass($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }
}
