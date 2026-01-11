<?php

namespace App\Services;

use App\Models\AttendanceNotification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Queue notification untuk dikirim
     */
    public function queueNotification(
        string $type,
        User $recipient,
        string $message,
        string $deliveryMethod = 'whatsapp',
        ?string $subject = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): AttendanceNotification {
        return AttendanceNotification::create([
            'type' => $type,
            'recipient_user_id' => $recipient->id,
            'recipient_phone' => $recipient->phone ?? $recipient->guardian?->phone,
            'recipient_email' => $recipient->email,
            'subject' => $subject,
            'message' => $message,
            'status' => 'pending',
            'delivery_method' => $deliveryMethod,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Send notification via WhatsApp menggunakan Fonnte API
     */
    public function sendWhatsApp(AttendanceNotification $notification): bool
    {
        try {
            $apiKey = config('whatsapp.fonnte_api_key');
            $phone = $this->formatPhoneNumber($notification->recipient_phone);

            if (! $apiKey) {
                throw new \Exception('WhatsApp API key not configured');
            }

            if (! $phone) {
                throw new \Exception('Recipient phone number not available');
            }

            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $notification->message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                $notification->markAsSent();
                Log::info('WhatsApp notification sent', [
                    'notification_id' => $notification->id,
                    'recipient' => $phone,
                ]);

                return true;
            }

            throw new \Exception('WhatsApp API returned error: '.$response->body());
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            Log::error('WhatsApp notification failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send notification via Email
     */
    public function sendEmail(AttendanceNotification $notification): bool
    {
        try {
            // TODO: Implement email sending using Laravel Mail
            // For now, mark as sent (placeholder)
            $notification->markAsSent();
            Log::info('Email notification sent (placeholder)', [
                'notification_id' => $notification->id,
            ]);

            return true;
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            Log::error('Email notification failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Process pending notifications dari queue
     */
    public function processPendingNotifications(): int
    {
        $notifications = AttendanceNotification::where('status', 'pending')
            ->orWhere(function ($query) {
                $query->where('status', 'failed')
                    ->where('retry_count', '<', 3);
            })
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        $sent = 0;

        foreach ($notifications as $notification) {
            $success = match ($notification->delivery_method) {
                'whatsapp' => $this->sendWhatsApp($notification),
                'email' => $this->sendEmail($notification),
                default => false,
            };

            if ($success) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Format phone number untuk WhatsApp API (format: 628xxx)
     */
    private function formatPhoneNumber(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert 08xxx to 628xxx
        if (str_starts_with($phone, '08')) {
            $phone = '62'.substr($phone, 1);
        }

        // Add 62 if not present
        if (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }

        return $phone;
    }

    /**
     * Queue alpha alert untuk orang tua
     */
    public function queueAlphaAlert(User $parent, string $studentName, string $date): void
    {
        $message = "Anak Anda {$studentName} tidak hadir di sekolah pada tanggal {$date} tanpa keterangan (Alpha). Jika ada kesalahan, silakan ajukan izin melalui portal.";

        $this->queueNotification(
            type: 'alpha_alert',
            recipient: $parent,
            message: $message,
            deliveryMethod: 'whatsapp',
            subject: 'Notifikasi Ketidakhadiran',
            referenceType: 'student_attendance',
            referenceId: null
        );
    }

    /**
     * Queue reminder untuk guru yang belum input absensi
     */
    public function queueAttendanceReminder(User $teacher, string $className): void
    {
        $message = "Jangan lupa input absensi harian untuk kelas {$className} hari ini.";

        $this->queueNotification(
            type: 'reminder',
            recipient: $teacher,
            message: $message,
            deliveryMethod: 'whatsapp',
            subject: 'Reminder Input Absensi'
        );
    }
}
