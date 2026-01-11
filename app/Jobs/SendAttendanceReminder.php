<?php

namespace App\Jobs;

use App\Models\SchoolClass;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAttendanceReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SchoolClass $class
    ) {}

    /**
     * Execute the job untuk send reminder ke wali kelas
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            $teacher = $this->class->waliKelas;

            if (! $teacher) {
                Log::warning('No homeroom teacher found for class', [
                    'class_id' => $this->class->id,
                ]);

                return;
            }

            $className = $this->class->tingkat.' '.$this->class->nama;

            $notificationService->queueAttendanceReminder(
                teacher: $teacher,
                className: $className
            );

            Log::info('Attendance reminder queued', [
                'class_id' => $this->class->id,
                'teacher_id' => $teacher->id,
                'class_name' => $className,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue attendance reminder', [
                'class_id' => $this->class->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
