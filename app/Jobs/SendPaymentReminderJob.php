<?php

namespace App\Jobs;

use App\Models\Bill;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk mengirim payment reminder ke orang tua siswa
 *
 * Job ini di-dispatch oleh SendPaymentRemindersCommand dan akan
 * memproses pengiriman reminder melalui WhatsApp menggunakan NotificationService
 */
class SendPaymentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan jika job gagal
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Delay antar percobaan (dalam detik)
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance
     *
     * @param  Bill  $bill  Tagihan yang akan di-remind
     * @param  string  $reminderType  Tipe reminder (h_minus_5, due_date, h_plus_7)
     */
    public function __construct(
        public Bill $bill,
        public string $reminderType
    ) {}

    /**
     * Execute the job
     */
    public function handle(NotificationService $notificationService): void
    {
        Log::info('Processing payment reminder job', [
            'bill_id' => $this->bill->id,
            'reminder_type' => $this->reminderType,
        ]);

        try {
            $reminderLog = $notificationService->sendPaymentReminder(
                $this->bill,
                $this->reminderType
            );

            if ($reminderLog) {
                Log::info('Payment reminder job completed', [
                    'bill_id' => $this->bill->id,
                    'reminder_log_id' => $reminderLog->id,
                    'status' => $reminderLog->status,
                ]);
            } else {
                Log::info('Payment reminder job skipped', [
                    'bill_id' => $this->bill->id,
                    'reminder_type' => $this->reminderType,
                    'reason' => 'No guardian or already sent',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Payment reminder job failed', [
                'bill_id' => $this->bill->id,
                'reminder_type' => $this->reminderType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Payment reminder job failed permanently', [
            'bill_id' => $this->bill->id,
            'reminder_type' => $this->reminderType,
            'error' => $exception->getMessage(),
        ]);
    }
}
