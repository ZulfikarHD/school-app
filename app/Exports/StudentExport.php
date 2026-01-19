<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
     * Constructor untuk menerima query builder yang sudah di-filter
     */
    public function __construct(
        protected $query
    ) {}

    /**
     * Collection untuk mengambil data students yang sudah di-filter
     * dari controller berdasarkan search dan filter parameters
     *
     * @return Collection
     */
    public function collection()
    {
        return $this->query
            ->with(['kelas', 'guardians'])
            ->get();
    }

    /**
     * Define headings untuk Excel export dengan kolom yang lengkap
     * untuk keperluan reporting dan backup data
     */
    public function headings(): array
    {
        return [
            'NIS',
            'NISN',
            'NIK',
            'Nama Lengkap',
            'Nama Panggilan',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'Alamat',
            'RT/RW',
            'Kelurahan/Desa',
            'Kecamatan',
            'Kota/Kabupaten',
            'Provinsi',
            'Kode Pos',
            'No HP Siswa',
            'Email Siswa',
            'Kelas',
            'Tahun Ajaran Masuk',
            'Tanggal Masuk',
            'Status',
            'Nama Ayah',
            'HP Ayah',
            'Nama Ibu',
            'HP Ibu',
            'Nama Wali',
            'HP Wali',
        ];
    }

    /**
     * Map setiap row data student ke format array untuk Excel export
     * dengan format yang readable dan lengkap
     *
     * @param  mixed  $student
     */
    public function map($student): array
    {
        // Get guardians data dengan mapping berdasarkan relasi
        $ayah = $student->guardians->where('relasi', 'Ayah')->first();
        $ibu = $student->guardians->where('relasi', 'Ibu')->first();
        $wali = $student->guardians->where('relasi', 'Wali')->first();

        return [
            $student->nis,
            $student->nisn,
            $student->nik,
            $student->nama_lengkap,
            $student->nama_panggilan,
            $student->jenis_kelamin,
            $student->tempat_lahir,
            $student->tanggal_lahir ? $student->tanggal_lahir->format('d/m/Y') : '',
            $student->agama,
            $student->alamat,
            $student->rt_rw,
            $student->kelurahan,
            $student->kecamatan,
            $student->kota,
            $student->provinsi,
            $student->kode_pos,
            $student->no_hp,
            $student->email,
            $student->kelas->nama_lengkap ?? '-',
            $student->tahun_ajaran_masuk,
            $student->tanggal_masuk ? $student->tanggal_masuk->format('d/m/Y') : '',
            ucfirst($student->status),
            $ayah->nama ?? '-',
            $ayah->no_hp ?? '-',
            $ibu->nama ?? '-',
            $ibu->no_hp ?? '-',
            $wali->nama ?? '-',
            $wali->no_hp ?? '-',
        ];
    }

    /**
     * Apply styling ke worksheet untuk tampilan yang professional
     * dengan header bold dan auto-sizing columns
     *
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Make header row bold
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Set title untuk worksheet Excel
     */
    public function title(): string
    {
        return 'Data Siswa';
    }
}
