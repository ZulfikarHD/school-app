<?php

namespace App\Console\Commands;

use App\Jobs\SendAttendanceReminder;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAttendanceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder ke wali kelas yang belum input absensi hari ini';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();

        $this->info("Checking classes without attendance for: {$today->format('Y-m-d')}");

        // Get all classes
        $allClasses = SchoolClass::with('waliKelas')->get();

        // Get classes yang sudah input absensi hari ini
        $classesWithAttendance = StudentAttendance::where('tanggal', $today)
            ->distinct('class_id')
            ->pluck('class_id');

        // Classes yang belum input
        $classesWithoutAttendance = $allClasses->whereNotIn('id', $classesWithAttendance);

        if ($classesWithoutAttendance->isEmpty()) {
            $this->info('All classes have recorded attendance for today.');

            return self::SUCCESS;
        }

        $queued = 0;

        foreach ($classesWithoutAttendance as $class) {
            if ($class->waliKelas) {
                SendAttendanceReminder::dispatch($class);
                $queued++;
            }
        }

        $this->info("Queued {$queued} attendance reminders.");

        return self::SUCCESS;
    }
}
