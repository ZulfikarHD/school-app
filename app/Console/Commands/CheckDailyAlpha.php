<?php

namespace App\Console\Commands;

use App\Jobs\SendAlphaNotification;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckDailyAlpha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:check-alpha {--date= : Date to check (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check untuk siswa dengan status Alpha dan queue notifikasi ke orang tua';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::today();

        $this->info("Checking alpha attendance for date: {$date->format('Y-m-d')}");

        $alphaAttendances = StudentAttendance::with(['student.guardian.user'])
            ->where('tanggal', $date)
            ->where('status', 'A')
            ->get();

        if ($alphaAttendances->isEmpty()) {
            $this->info('No alpha attendance found for today.');

            return self::SUCCESS;
        }

        $queued = 0;

        foreach ($alphaAttendances as $attendance) {
            // Dispatch job untuk send notification
            SendAlphaNotification::dispatch($attendance);
            $queued++;
        }

        $this->info("Queued {$queued} alpha notifications.");

        return self::SUCCESS;
    }
}
