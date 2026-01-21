<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk Payment System
 *
 * Membuat data sample untuk testing fitur pembayaran:
 * - Kategori pembayaran (SPP, Uang Gedung, Seragam, Kegiatan)
 * - Tagihan untuk siswa aktif
 * - Beberapa pembayaran sample dengan berbagai status
 */
class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Payment Categories...');
        $categories = $this->seedPaymentCategories();

        $this->command->info('Seeding Bills...');
        $this->seedBills($categories);

        $this->command->info('Seeding Payments...');
        $this->seedPayments();

        $this->command->info('Payment seeding completed!');
    }

    /**
     * Seed payment categories
     */
    protected function seedPaymentCategories(): array
    {
        $admin = User::where('role', 'ADMIN')->first();
        $tahunAjaran = $this->getCurrentAcademicYear();

        $categories = [
            [
                'nama' => 'SPP Bulanan',
                'kode' => 'SPP',
                'deskripsi' => 'Sumbangan Pembinaan Pendidikan bulanan',
                'tipe' => 'bulanan',
                'nominal_default' => 300000,
                'is_active' => true,
                'is_mandatory' => true,
                'due_day' => 10,
                'tahun_ajaran' => $tahunAjaran,
            ],
            [
                'nama' => 'Uang Gedung',
                'kode' => 'UG',
                'deskripsi' => 'Uang pembangunan gedung tahunan',
                'tipe' => 'tahunan',
                'nominal_default' => 2500000,
                'is_active' => true,
                'is_mandatory' => true,
                'due_day' => 15,
                'tahun_ajaran' => $tahunAjaran,
            ],
            [
                'nama' => 'Seragam',
                'kode' => 'SRG',
                'deskripsi' => 'Biaya seragam sekolah',
                'tipe' => 'insidental',
                'nominal_default' => 750000,
                'is_active' => true,
                'is_mandatory' => false,
                'due_day' => null,
                'tahun_ajaran' => $tahunAjaran,
            ],
            [
                'nama' => 'Kegiatan Ekstrakurikuler',
                'kode' => 'EKSKUL',
                'deskripsi' => 'Biaya kegiatan ekstrakurikuler per semester',
                'tipe' => 'insidental',
                'nominal_default' => 150000,
                'is_active' => true,
                'is_mandatory' => false,
                'due_day' => null,
                'tahun_ajaran' => $tahunAjaran,
            ],
            [
                'nama' => 'Study Tour',
                'kode' => 'TOUR',
                'deskripsi' => 'Biaya study tour tahunan',
                'tipe' => 'insidental',
                'nominal_default' => 500000,
                'is_active' => true,
                'is_mandatory' => false,
                'due_day' => null,
                'tahun_ajaran' => $tahunAjaran,
            ],
        ];

        $createdCategories = [];

        foreach ($categories as $category) {
            $createdCategories[$category['kode']] = PaymentCategory::updateOrCreate(
                ['kode' => $category['kode']],
                array_merge($category, [
                    'created_by' => $admin?->id,
                ])
            );
        }

        $this->command->info('  Created ' . count($createdCategories) . ' payment categories');

        return $createdCategories;
    }

    /**
     * Seed bills for active students
     */
    protected function seedBills(array $categories): void
    {
        $students = Student::active()->with('kelas')->get();

        if ($students->isEmpty()) {
            $this->command->warn('  No active students found. Skipping bills seeding.');
            return;
        }

        $admin = User::where('role', 'ADMIN')->first();
        $sppCategory = $categories['SPP'] ?? null;

        if (!$sppCategory) {
            $this->command->warn('  SPP category not found. Skipping bills seeding.');
            return;
        }

        $currentMonth = now()->month;
        $currentYear = now()->year;
        $tahunAjaran = $this->getCurrentAcademicYear();

        $billCount = 0;

        // Generate SPP bills untuk 3 bulan terakhir + bulan ini
        foreach ($students as $student) {
            for ($i = 2; $i >= 0; $i--) {
                $month = $currentMonth - $i;
                $year = $currentYear;

                if ($month <= 0) {
                    $month += 12;
                    $year--;
                }

                // Skip jika sudah ada
                $exists = Bill::where('student_id', $student->id)
                    ->where('payment_category_id', $sppCategory->id)
                    ->where('bulan', $month)
                    ->where('tahun', $year)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $dueDay = min($sppCategory->due_day ?? 10, 28);
                $dueDate = Carbon::create($year, $month, $dueDay);

                Bill::create([
                    'nomor_tagihan' => Bill::generateNomorTagihan(),
                    'student_id' => $student->id,
                    'payment_category_id' => $sppCategory->id,
                    'tahun_ajaran' => $tahunAjaran,
                    'bulan' => $month,
                    'tahun' => $year,
                    'nominal' => $sppCategory->getPriceForClass($student->kelas_id),
                    'nominal_terbayar' => 0,
                    'status' => 'belum_bayar',
                    'tanggal_jatuh_tempo' => $dueDate,
                    'keterangan' => "SPP Bulanan - " . $this->getMonthName($month) . " {$year}",
                    'created_by' => $admin?->id,
                ]);

                $billCount++;
            }
        }

        $this->command->info("  Created {$billCount} bills for " . $students->count() . " students");
    }

    /**
     * Seed sample payments
     */
    protected function seedPayments(): void
    {
        $admin = User::where('role', 'ADMIN')->first();

        // Get unpaid bills
        $unpaidBills = Bill::with(['student', 'paymentCategory'])
            ->where('status', 'belum_bayar')
            ->inRandomOrder()
            ->limit(20)
            ->get();

        if ($unpaidBills->isEmpty()) {
            $this->command->warn('  No unpaid bills found. Skipping payments seeding.');
            return;
        }

        $paymentCount = 0;

        foreach ($unpaidBills as $index => $bill) {
            // Variasi: beberapa lunas, beberapa partial, beberapa pending
            $scenario = $index % 5;

            switch ($scenario) {
                case 0:
                case 1:
                    // Full payment - Tunai (auto verified)
                    $this->createPayment($bill, $bill->nominal, 'tunai', 'verified', $admin);
                    break;

                case 2:
                    // Full payment - Transfer (pending)
                    $this->createPayment($bill, $bill->nominal, 'transfer', 'pending', $admin);
                    break;

                case 3:
                    // Partial payment - Tunai
                    $partial = $bill->nominal * 0.5;
                    $this->createPayment($bill, $partial, 'tunai', 'verified', $admin);
                    break;

                case 4:
                    // Skip - leave as unpaid
                    continue 2;
            }

            $paymentCount++;
        }

        $this->command->info("  Created {$paymentCount} sample payments");
    }

    /**
     * Create a single payment
     */
    protected function createPayment(Bill $bill, float $nominal, string $method, string $status, ?User $admin): void
    {
        $payment = Payment::create([
            'nomor_kwitansi' => Payment::generateNomorKwitansi(),
            'bill_id' => $bill->id,
            'student_id' => $bill->student_id,
            'nominal' => $nominal,
            'metode_pembayaran' => $method,
            'tanggal_bayar' => now()->subDays(rand(0, 14)),
            'waktu_bayar' => now()->format('H:i:s'),
            'status' => $status,
            'keterangan' => 'Pembayaran via ' . ($method === 'tunai' ? 'tunai di TU' : 'transfer bank'),
            'created_by' => $admin?->id,
            'verified_by' => $status === 'verified' ? $admin?->id : null,
            'verified_at' => $status === 'verified' ? now() : null,
        ]);

        // Update bill status
        $bill->updatePaymentStatus();
    }

    /**
     * Get current academic year
     */
    protected function getCurrentAcademicYear(): string
    {
        $month = now()->month;
        $year = now()->year;

        if ($month >= 7) {
            return $year . '/' . ($year + 1);
        }

        return ($year - 1) . '/' . $year;
    }

    /**
     * Get month name in Indonesian
     */
    protected function getMonthName(int $month): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $months[$month] ?? '';
    }
}
