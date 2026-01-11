<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class ProcessNotificationQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending notifications dari queue dan kirim via WhatsApp/Email';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $this->info('Processing notification queue...');

        $sent = $notificationService->processPendingNotifications();

        $this->info("Successfully sent {$sent} notifications.");

        return self::SUCCESS;
    }
}
