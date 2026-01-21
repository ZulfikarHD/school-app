<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentReminderJob;
use App\Models\Bill;
use App\Models\PaymentReminderLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Command untuk mengirim payment reminders secara otomatis
 *
 * Dijadwalkan berjalan setiap hari pada pukul 06:00 WIB
 * untuk mengirim reminder H-5, H-0 (jatuh tempo), dan H+7 (overdue)
 */
class SendPaymentRemindersCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'payments:send-reminders
                            {--type= : Tipe reminder spesifik (h_minus_5, due_date, h_plus_7)}
                            {--dry-run : Tampilkan tagihan yang akan di-remind tanpa mengirim}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Kirim payment reminders ke orang tua siswa (H-5, Jatuh Tempo, H+7)';

    /**
     * Execute the console command
     */
    public function handle(): int
    {
        $this->info('=== Payment Reminders ===');
        $this->info('Waktu: '.now()->format('d M Y H:i:s'));
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $specificType = $this->option('type');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - Tidak ada reminder yang akan dikirim');
            $this->newLine();
        }

        $totalDispatched = 0;

        // Process each reminder type
        $reminderTypes = $specificType
            ? [$specificType]
            : [
                PaymentReminderLog::TYPE_H_MINUS_5,
                PaymentReminderLog::TYPE_DUE_DATE,
                PaymentReminderLog::TYPE_H_PLUS_7,
            ];

        foreach ($reminderTypes as $type) {
            $count = $this->processReminderType($type, $isDryRun);
            $totalDispatched += $count;
        }

        $this->newLine();
        $this->info("Total: {$totalDispatched} reminder jobs dispatched");

        return Command::SUCCESS;
    }

    /**
     * Process reminder untuk tipe tertentu
     *
     * @param  string  $type  Tipe reminder
     * @param  bool  $isDryRun  Mode dry run
     * @return int Jumlah job yang di-dispatch
     */
    protected function processReminderType(string $type, bool $isDryRun): int
    {
        $typeLabel = $this->getReminderTypeLabel($type);
        $this->info("Processing: {$typeLabel}");

        $bills = $this->getBillsForReminderType($type);
        $count = $bills->count();

        if ($count === 0) {
            $this->line('  - Tidak ada tagihan untuk di-remind');

            return 0;
        }

        $this->line("  - Ditemukan {$count} tagihan");

        if ($isDryRun) {
            $this->displayBillsTable($bills);

            return 0;
        }

        $dispatched = 0;

        foreach ($bills as $bill) {
            // Skip if already sent
            if (PaymentReminderLog::hasBeenSent($bill->id, $type)) {
                continue;
            }

            SendPaymentReminderJob::dispatch($bill, $type);
            $dispatched++;
        }

        $this->line("  - {$dispatched} job dispatched");

        return $dispatched;
    }

    /**
     * Get bills yang perlu di-remind berdasarkan tipe
     *
     * @param  string  $type  Tipe reminder
     * @return \Illuminate\Database\Eloquent\Collection<Bill>
     */
    protected function getBillsForReminderType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        $today = Carbon::today();

        $query = Bill::query()
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->whereNotNull('tanggal_jatuh_tempo')
            ->with(['student.guardians', 'paymentCategory']);

        return match ($type) {
            PaymentReminderLog::TYPE_H_MINUS_5 => $query
                ->whereDate('tanggal_jatuh_tempo', $today->copy()->addDays(5))
                ->get(),

            PaymentReminderLog::TYPE_DUE_DATE => $query
                ->whereDate('tanggal_jatuh_tempo', $today)
                ->get(),

            PaymentReminderLog::TYPE_H_PLUS_7 => $query
                ->whereDate('tanggal_jatuh_tempo', $today->copy()->subDays(7))
                ->get(),

            default => collect(),
        };
    }

    /**
     * Display bills dalam format tabel (untuk dry run)
     */
    protected function displayBillsTable($bills): void
    {
        $tableData = $bills->map(fn ($bill) => [
            $bill->id,
            $bill->student->nama_lengkap ?? '-',
            $bill->paymentCategory->nama ?? '-',
            $bill->nama_bulan.' '.$bill->tahun,
            $bill->formatted_sisa,
            $bill->tanggal_jatuh_tempo?->format('d M Y'),
        ])->toArray();

        $this->table(
            ['ID', 'Siswa', 'Kategori', 'Periode', 'Sisa', 'Jatuh Tempo'],
            $tableData
        );
    }

    /**
     * Get label untuk tipe reminder
     */
    protected function getReminderTypeLabel(string $type): string
    {
        return match ($type) {
            PaymentReminderLog::TYPE_H_MINUS_5 => 'H-5 (5 hari sebelum jatuh tempo)',
            PaymentReminderLog::TYPE_DUE_DATE => 'Jatuh Tempo (H-0)',
            PaymentReminderLog::TYPE_H_PLUS_7 => 'H+7 (7 hari setelah jatuh tempo)',
            default => $type,
        };
    }
}
