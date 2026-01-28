<?php

namespace App\Exports;

use App\Models\PsbPayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * PsbRegistrationsExport - Export data pendaftaran PSB ke Excel
 *
 * Class ini bertujuan untuk mengekspor data pendaftaran siswa baru
 * dengan format kolom yang lengkap untuk keperluan reporting
 */
class PsbRegistrationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
     * Constructor untuk menerima query builder yang sudah di-filter
     */
    public function __construct(
        protected Builder $query
    ) {}

    /**
     * Collection untuk mengambil data registrations yang sudah di-filter
     * dari controller berdasarkan search dan filter parameters
     *
     * @return Collection
     */
    public function collection()
    {
        return $this->query
            ->with(['academicYear', 'payments'])
            ->get();
    }

    /**
     * Define headings untuk Excel export dengan kolom yang lengkap
     * sesuai spesifikasi Epic-4
     */
    public function headings(): array
    {
        return [
            'No. Registrasi',
            'Nama Siswa',
            'NIK',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Agama',
            'Alamat',
            'Anak Ke',
            'Asal Sekolah',
            'Nama Ayah',
            'NIK Ayah',
            'Pekerjaan Ayah',
            'No. HP Ayah',
            'Email Ayah',
            'Nama Ibu',
            'NIK Ibu',
            'Pekerjaan Ibu',
            'No. HP Ibu',
            'Email Ibu',
            'Status',
            'Tanggal Daftar',
            'Tanggal Verifikasi',
            'Tanggal Pengumuman',
            'Status Pembayaran',
            'Catatan',
        ];
    }

    /**
     * Map setiap row data registration ke format array untuk Excel export
     * dengan format yang readable dan lengkap
     *
     * @param  mixed  $registration
     */
    public function map($registration): array
    {
        // Get payment status
        $paymentStatus = $this->getPaymentStatusLabel($registration);

        return [
            $registration->registration_number,
            $registration->student_name,
            $registration->student_nik,
            $registration->birth_place,
            $registration->birth_date ? $registration->birth_date->format('d/m/Y') : '',
            $registration->gender === 'male' ? 'Laki-laki' : 'Perempuan',
            ucfirst($registration->religion),
            $registration->address,
            $registration->child_order,
            $registration->origin_school ?? '-',
            $registration->father_name,
            $registration->father_nik,
            $registration->father_occupation,
            $registration->father_phone,
            $registration->father_email ?? '-',
            $registration->mother_name,
            $registration->mother_nik,
            $registration->mother_occupation,
            $registration->mother_phone ?? '-',
            $registration->mother_email ?? '-',
            $registration->getStatusLabel(),
            $registration->created_at ? $registration->created_at->format('d/m/Y H:i') : '',
            $registration->verified_at ? $registration->verified_at->format('d/m/Y H:i') : '-',
            $registration->announced_at ? $registration->announced_at->format('d/m/Y H:i') : '-',
            $paymentStatus,
            $registration->notes ?? '-',
        ];
    }

    /**
     * Get payment status label berdasarkan payments yang ada
     *
     * @param  mixed  $registration
     * @return string Label status pembayaran
     */
    protected function getPaymentStatusLabel($registration): string
    {
        $payments = $registration->payments;

        if ($payments->isEmpty()) {
            return 'Belum Ada Pembayaran';
        }

        $pendingCount = $payments->where('status', PsbPayment::STATUS_PENDING)->count();
        $verifiedCount = $payments->where('status', PsbPayment::STATUS_VERIFIED)->count();
        $rejectedCount = $payments->where('status', PsbPayment::STATUS_REJECTED)->count();

        if ($rejectedCount > 0) {
            return 'Ditolak';
        }

        if ($pendingCount > 0 && $verifiedCount === 0) {
            return 'Menunggu Verifikasi';
        }

        if ($pendingCount > 0 && $verifiedCount > 0) {
            return 'Sebagian Terverifikasi';
        }

        if ($verifiedCount > 0 && $pendingCount === 0) {
            return 'Lunas';
        }

        return 'Belum Ada Pembayaran';
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
        return 'Data Pendaftaran PSB';
    }
}
