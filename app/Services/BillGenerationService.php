<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\PaymentCategory;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengelola generate tagihan siswa
 *
 * Menyediakan fitur preview, generate bulk, dan pengecekan duplikat
 * untuk membuat tagihan berdasarkan kategori pembayaran
 */
class BillGenerationService
{
    /**
     * Preview tagihan sebelum generate
     * Menampilkan daftar siswa, nominal, dan status duplikat
     *
     * @param  int  $paymentCategoryId  ID kategori pembayaran
     * @param  int  $bulan  Bulan tagihan (1-12)
     * @param  int  $tahun  Tahun tagihan
     * @param  array|null  $classIds  Filter berdasarkan kelas (null = semua kelas)
     * @return array{students: Collection, summary: array, duplicates: Collection}
     */
    public function preview(
        int $paymentCategoryId,
        int $bulan,
        int $tahun,
        ?array $classIds = null
    ): array {
        $category = PaymentCategory::with('classPrices')->findOrFail($paymentCategoryId);

        // Get active students, optionally filtered by class
        $studentsQuery = Student::query()
            ->active()
            ->with(['kelas'])
            ->orderBy('kelas_id')
            ->orderBy('nama_lengkap');

        if (! empty($classIds)) {
            $studentsQuery->whereIn('kelas_id', $classIds);
        }

        $students = $studentsQuery->get();

        // Check for existing bills (duplicates)
        $existingBills = Bill::query()
            ->where('payment_category_id', $paymentCategoryId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->whereIn('student_id', $students->pluck('id'))
            ->where('status', '!=', 'dibatalkan')
            ->pluck('student_id');

        // Calculate nominal per student
        $previewData = $students->map(function ($student) use ($category, $existingBills, $bulan, $tahun) {
            $nominal = $category->getPriceForClass($student->kelas_id);
            $isDuplicate = $existingBills->contains($student->id);

            return [
                'student_id' => $student->id,
                'nis' => $student->nis,
                'nama_lengkap' => $student->nama_lengkap,
                'kelas' => $student->kelas ? $student->kelas->nama_lengkap : '-',
                'kelas_id' => $student->kelas_id,
                'nominal' => $nominal,
                'formatted_nominal' => 'Rp '.number_format($nominal, 0, ',', '.'),
                'is_duplicate' => $isDuplicate,
                'bulan' => $bulan,
                'tahun' => $tahun,
            ];
        });

        // Filter out duplicates for new generation
        $newStudents = $previewData->where('is_duplicate', false);
        $duplicates = $previewData->where('is_duplicate', true);

        // Calculate summary
        $summary = [
            'total_students' => $newStudents->count(),
            'total_nominal' => $newStudents->sum('nominal'),
            'formatted_total' => 'Rp '.number_format($newStudents->sum('nominal'), 0, ',', '.'),
            'duplicate_count' => $duplicates->count(),
            'category_name' => $category->nama,
            'category_type' => $category->tipe,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ];

        return [
            'students' => $previewData,
            'summary' => $summary,
            'duplicates' => $duplicates,
            'new_students' => $newStudents,
        ];
    }

    /**
     * Generate tagihan bulk untuk siswa
     *
     * @param  int  $paymentCategoryId  ID kategori pembayaran
     * @param  int  $bulan  Bulan tagihan (1-12)
     * @param  int  $tahun  Tahun tagihan
     * @param  array|null  $classIds  Filter berdasarkan kelas (null = semua kelas)
     * @param  bool  $skipDuplicates  Skip siswa yang sudah punya tagihan
     * @return array{success: bool, generated_count: int, skipped_count: int, total_nominal: float, errors: array}
     */
    public function generate(
        int $paymentCategoryId,
        int $bulan,
        int $tahun,
        ?array $classIds = null,
        bool $skipDuplicates = true
    ): array {
        $category = PaymentCategory::with('classPrices')->findOrFail($paymentCategoryId);

        // Validate category is active
        if (! $category->is_active) {
            return [
                'success' => false,
                'generated_count' => 0,
                'skipped_count' => 0,
                'total_nominal' => 0,
                'errors' => ['Kategori pembayaran tidak aktif.'],
            ];
        }

        DB::beginTransaction();

        try {
            // Get active students
            $studentsQuery = Student::query()
                ->active()
                ->with(['kelas']);

            if (! empty($classIds)) {
                $studentsQuery->whereIn('kelas_id', $classIds);
            }

            $students = $studentsQuery->get();

            // Get existing bills if skip duplicates
            $existingStudentIds = collect();
            if ($skipDuplicates) {
                $existingStudentIds = Bill::query()
                    ->where('payment_category_id', $paymentCategoryId)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->where('status', '!=', 'dibatalkan')
                    ->pluck('student_id');
            }

            // Calculate due date
            $dueDay = $category->due_day ?? 10;
            $dueDate = sprintf('%04d-%02d-%02d', $tahun, $bulan, min($dueDay, 28));

            // Calculate tahun ajaran from month/year
            $tahunAjaran = $this->calculateTahunAjaran($bulan, $tahun);

            $generatedCount = 0;
            $skippedCount = 0;
            $totalNominal = 0;
            $errors = [];

            foreach ($students as $student) {
                // Skip duplicates
                if ($existingStudentIds->contains($student->id)) {
                    $skippedCount++;

                    continue;
                }

                try {
                    $nominal = $category->getPriceForClass($student->kelas_id);

                    Bill::create([
                        'nomor_tagihan' => Bill::generateNomorTagihan(),
                        'student_id' => $student->id,
                        'payment_category_id' => $paymentCategoryId,
                        'tahun_ajaran' => $tahunAjaran,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'nominal' => $nominal,
                        'nominal_terbayar' => 0,
                        'status' => 'belum_bayar',
                        'tanggal_jatuh_tempo' => $dueDate,
                        'keterangan' => "{$category->nama} - ".ucfirst($this->getMonthName($bulan))." {$tahun}",
                    ]);

                    $generatedCount++;
                    $totalNominal += $nominal;
                } catch (\Exception $e) {
                    $errors[] = "Gagal membuat tagihan untuk {$student->nama_lengkap}: {$e->getMessage()}";
                    Log::error('Failed to create bill', [
                        'student_id' => $student->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'generated_count' => $generatedCount,
                'skipped_count' => $skippedCount,
                'total_nominal' => $totalNominal,
                'formatted_total' => 'Rp '.number_format($totalNominal, 0, ',', '.'),
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bill generation failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'generated_count' => 0,
                'skipped_count' => 0,
                'total_nominal' => 0,
                'errors' => ['Gagal generate tagihan: '.$e->getMessage()],
            ];
        }
    }

    /**
     * Check apakah ada duplikat tagihan
     *
     * @param  int  $paymentCategoryId  ID kategori pembayaran
     * @param  int  $bulan  Bulan tagihan
     * @param  int  $tahun  Tahun tagihan
     * @param  array|null  $classIds  Filter kelas
     * @return array{has_duplicates: bool, count: int, students: Collection}
     */
    public function checkDuplicates(
        int $paymentCategoryId,
        int $bulan,
        int $tahun,
        ?array $classIds = null
    ): array {
        $studentsQuery = Student::query()->active();

        if (! empty($classIds)) {
            $studentsQuery->whereIn('kelas_id', $classIds);
        }

        $studentIds = $studentsQuery->pluck('id');

        $duplicates = Bill::query()
            ->where('payment_category_id', $paymentCategoryId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->whereIn('student_id', $studentIds)
            ->where('status', '!=', 'dibatalkan')
            ->with(['student', 'student.kelas'])
            ->get();

        return [
            'has_duplicates' => $duplicates->isNotEmpty(),
            'count' => $duplicates->count(),
            'students' => $duplicates->map(fn ($bill) => [
                'student_id' => $bill->student_id,
                'nama_lengkap' => $bill->student->nama_lengkap,
                'nis' => $bill->student->nis,
                'kelas' => $bill->student->kelas?->nama_lengkap ?? '-',
                'nomor_tagihan' => $bill->nomor_tagihan,
                'status' => $bill->status,
            ]),
        ];
    }

    /**
     * Get available classes untuk filter dropdown
     *
     * @return Collection<SchoolClass>
     */
    public function getAvailableClasses(?string $tahunAjaran = null): Collection
    {
        $query = SchoolClass::query()
            ->active()
            ->orderBy('tingkat')
            ->orderBy('nama');

        if ($tahunAjaran) {
            $query->where('tahun_ajaran', $tahunAjaran);
        }

        return $query->get(['id', 'tingkat', 'nama', 'tahun_ajaran']);
    }

    /**
     * Get active payment categories untuk dropdown
     *
     * @return Collection<PaymentCategory>
     */
    public function getActiveCategories(?string $tahunAjaran = null): Collection
    {
        $query = PaymentCategory::query()
            ->active()
            ->orderBy('nama');

        if ($tahunAjaran) {
            $query->where(function ($q) use ($tahunAjaran) {
                $q->where('tahun_ajaran', $tahunAjaran)
                    ->orWhereNull('tahun_ajaran');
            });
        }

        return $query->get(['id', 'nama', 'kode', 'tipe', 'nominal_default', 'due_day']);
    }

    /**
     * Calculate tahun ajaran from bulan and tahun
     * Tahun ajaran dimulai Juli (7) dan berakhir Juni (6)
     */
    protected function calculateTahunAjaran(int $bulan, int $tahun): string
    {
        if ($bulan >= 7) {
            // July - December: tahun/tahun+1
            return $tahun.'/'.($tahun + 1);
        } else {
            // January - June: tahun-1/tahun
            return ($tahun - 1).'/'.$tahun;
        }
    }

    /**
     * Get month name in Indonesian
     */
    protected function getMonthName(int $bulan): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$bulan] ?? '';
    }
}
