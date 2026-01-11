<?php

namespace App\Jobs;

use App\Models\StudentAttendance;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAlphaNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public StudentAttendance $attendance
    ) {}

    /**
     * Execute the job untuk send alpha notification ke orang tua
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            // Skip jika bukan status Alpha
            if ($this->attendance->status !== 'A') {
                return;
            }

            $student = $this->attendance->student;
            $parent = $student->guardian?->user;

            if (! $parent) {
                Log::warning('No parent found for student', [
                    'student_id' => $student->id,
                    'attendance_id' => $this->attendance->id,
                ]);

                return;
            }

            $date = $this->attendance->tanggal->format('d/m/Y');

            $notificationService->queueAlphaAlert(
                parent: $parent,
                studentName: $student->nama_lengkap,
                date: $date
            );

            Log::info('Alpha notification queued', [
                'student_id' => $student->id,
                'parent_id' => $parent->id,
                'date' => $date,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue alpha notification', [
                'attendance_id' => $this->attendance->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
