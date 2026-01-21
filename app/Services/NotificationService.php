<?php

namespace App\Services;

use App\Models\AttendanceNotification;
use App\Models\Bill;
use App\Models\PaymentReminderLog;
use App\Models\ReportCard;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengelola pengiriman notifikasi
 *
 * Menyediakan fitur pengiriman notifikasi melalui WhatsApp dan Email
 * untuk berbagai keperluan seperti kehadiran dan pembayaran
 */
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

    // ============================================================
    // REPORT CARD NOTIFICATION METHODS
    // ============================================================

    /**
     * Queue notification ke orang tua saat rapor dirilis
     *
     * @param  ReportCard  $reportCard  Rapor yang baru dirilis
     */
    public function queueReportCardReleasedNotification(ReportCard $reportCard): void
    {
        // Load necessary relationships
        $reportCard->loadMissing(['student.guardians', 'class']);

        $student = $reportCard->student;

        if (! $student) {
            Log::warning('Report card notification skipped: No student found', [
                'report_card_id' => $reportCard->id,
            ]);

            return;
        }

        // Get all guardians for the student
        $guardians = $student->guardians;

        if ($guardians->isEmpty()) {
            Log::warning('Report card notification skipped: No guardians found', [
                'report_card_id' => $reportCard->id,
                'student_id' => $student->id,
            ]);

            return;
        }

        // Build message
        $message = $this->buildReportCardReleasedMessage($reportCard);
        $schoolName = config('app.school_name', 'Sekolah');

        // Queue notification untuk setiap guardian yang memiliki user account
        foreach ($guardians as $guardian) {
            if (! $guardian->user) {
                continue;
            }

            $this->queueNotification(
                type: 'report_card_released',
                recipient: $guardian->user,
                message: $message,
                deliveryMethod: 'whatsapp',
                subject: "Rapor Semester {$reportCard->semester} Tersedia",
                referenceType: 'report_card',
                referenceId: $reportCard->id
            );

            Log::info('Report card release notification queued', [
                'report_card_id' => $reportCard->id,
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
            ]);
        }
    }

    /**
     * Build message untuk notifikasi rapor dirilis
     *
     * @param  ReportCard  $reportCard  Rapor
     * @return string Pesan notifikasi
     */
    protected function buildReportCardReleasedMessage(ReportCard $reportCard): string
    {
        $studentName = $reportCard->student->nama_lengkap;
        $className = $reportCard->class?->nama_lengkap ?? '-';
        $semester = $reportCard->semester === '1' ? 'Ganjil' : 'Genap';
        $tahunAjaran = $reportCard->tahun_ajaran;
        $average = number_format($reportCard->average_score ?? 0, 1);
        $rank = $reportCard->rank ?? '-';
        $schoolName = config('app.school_name', 'Sekolah');
        $appUrl = config('app.url');

        return <<<MSG
Yth. Bapak/Ibu Orang Tua/Wali,

*RAPOR SEMESTER {$semester} TELAH TERSEDIA*

Rapor anak Anda sudah dapat dilihat:
- Nama: {$studentName}
- Kelas: {$className}
- Semester: {$semester}
- Tahun Ajaran: {$tahunAjaran}

Ringkasan:
- Rata-rata Nilai: {$average}
- Peringkat: {$rank}

Silakan login ke portal orang tua untuk melihat detail rapor dan mengunduh PDF.

{$appUrl}

Terima kasih atas dukungan Bapak/Ibu selama semester ini.

Salam,
{$schoolName}
MSG;
    }

    // ============================================================
    // PAYMENT REMINDER METHODS
    // ============================================================

    /**
     * Send payment reminder ke guardian siswa
     *
     * @param  Bill  $bill  Tagihan yang akan di-remind
     * @param  string  $reminderType  Tipe reminder (h_minus_5, due_date, h_plus_7)
     * @return PaymentReminderLog|null Log reminder yang dibuat
     */
    public function sendPaymentReminder(Bill $bill, string $reminderType): ?PaymentReminderLog
    {
        // Load necessary relationships
        $bill->loadMissing(['student.guardians', 'student.kelas', 'paymentCategory']);

        // Get primary guardian
        $guardian = $bill->student->guardians()->first();

        if (! $guardian || ! $guardian->no_hp) {
            Log::warning('Payment reminder skipped: No guardian or phone number', [
                'bill_id' => $bill->id,
                'student_id' => $bill->student_id,
            ]);

            return null;
        }

        // Check if reminder already sent
        if (PaymentReminderLog::hasBeenSent($bill->id, $reminderType)) {
            Log::info('Payment reminder skipped: Already sent', [
                'bill_id' => $bill->id,
                'reminder_type' => $reminderType,
            ]);

            return null;
        }

        // Build message
        $message = $this->buildPaymentReminderMessage($bill, $reminderType);

        // Create reminder log
        $reminderLog = PaymentReminderLog::create([
            'bill_id' => $bill->id,
            'reminder_type' => $reminderType,
            'channel' => PaymentReminderLog::CHANNEL_WHATSAPP,
            'recipient' => $guardian->no_hp,
            'message' => $message,
            'status' => PaymentReminderLog::STATUS_PENDING,
        ]);

        // Send WhatsApp
        $success = $this->sendPaymentReminderWhatsApp($reminderLog);

        return $reminderLog;
    }

    /**
     * Send payment reminder via WhatsApp
     */
    protected function sendPaymentReminderWhatsApp(PaymentReminderLog $reminderLog): bool
    {
        try {
            $apiKey = config('whatsapp.fonnte_api_key');
            $phone = $this->formatPhoneNumber($reminderLog->recipient);

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
                'message' => $reminderLog->message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                $reminderLog->markAsSent();
                Log::info('Payment reminder sent via WhatsApp', [
                    'reminder_log_id' => $reminderLog->id,
                    'bill_id' => $reminderLog->bill_id,
                    'recipient' => $phone,
                ]);

                return true;
            }

            throw new \Exception('WhatsApp API returned error: '.$response->body());
        } catch (\Exception $e) {
            $reminderLog->markAsFailed($e->getMessage());
            Log::error('Payment reminder failed via WhatsApp', [
                'reminder_log_id' => $reminderLog->id,
                'bill_id' => $reminderLog->bill_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Build message untuk payment reminder berdasarkan tipe
     *
     * @param  Bill  $bill  Tagihan
     * @param  string  $reminderType  Tipe reminder
     * @return string Pesan reminder
     */
    protected function buildPaymentReminderMessage(Bill $bill, string $reminderType): string
    {
        $studentName = $bill->student->nama_lengkap;
        $className = $bill->student->kelas?->nama_lengkap ?? '-';
        $category = $bill->paymentCategory->nama;
        $periode = $bill->nama_bulan.' '.$bill->tahun;
        $nominal = $bill->formatted_sisa;
        $dueDate = $bill->tanggal_jatuh_tempo?->format('d M Y') ?? '-';
        $schoolName = config('app.school_name', 'Sekolah');

        return match ($reminderType) {
            PaymentReminderLog::TYPE_H_MINUS_5 => $this->getHMinus5Message($studentName, $className, $category, $periode, $nominal, $dueDate, $schoolName),
            PaymentReminderLog::TYPE_DUE_DATE => $this->getDueDateMessage($studentName, $className, $category, $periode, $nominal, $dueDate, $schoolName),
            PaymentReminderLog::TYPE_H_PLUS_7 => $this->getHPlus7Message($studentName, $className, $category, $periode, $nominal, $dueDate, $schoolName),
            default => $this->getDueDateMessage($studentName, $className, $category, $periode, $nominal, $dueDate, $schoolName),
        };
    }

    /**
     * Template pesan H-5 (5 hari sebelum jatuh tempo)
     */
    protected function getHMinus5Message(
        string $studentName,
        string $className,
        string $category,
        string $periode,
        string $nominal,
        string $dueDate,
        string $schoolName
    ): string {
        return <<<MSG
Yth. Bapak/Ibu Wali Murid,

Tagihan *{$category}* untuk anak Anda:
- Nama: {$studentName}
- Kelas: {$className}
- Periode: {$periode}
- Nominal: {$nominal}

akan *jatuh tempo pada {$dueDate}* (5 hari lagi).

Mohon segera melakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari denda keterlambatan.

Terima kasih.

Salam,
{$schoolName}
MSG;
    }

    /**
     * Template pesan jatuh tempo (H-0)
     */
    protected function getDueDateMessage(
        string $studentName,
        string $className,
        string $category,
        string $periode,
        string $nominal,
        string $dueDate,
        string $schoolName
    ): string {
        return <<<MSG
Yth. Bapak/Ibu Wali Murid,

*PENGINGAT - HARI INI JATUH TEMPO*

Tagihan *{$category}* untuk anak Anda:
- Nama: {$studentName}
- Kelas: {$className}
- Periode: {$periode}
- Nominal: {$nominal}

*JATUH TEMPO HARI INI ({$dueDate})*

Mohon segera melakukan pembayaran untuk menghindari denda keterlambatan.

Terima kasih atas perhatiannya.

Salam,
{$schoolName}
MSG;
    }

    /**
     * Template pesan H+7 (7 hari setelah jatuh tempo - overdue)
     */
    protected function getHPlus7Message(
        string $studentName,
        string $className,
        string $category,
        string $periode,
        string $nominal,
        string $dueDate,
        string $schoolName
    ): string {
        return <<<MSG
Yth. Bapak/Ibu Wali Murid,

*PEMBERITAHUAN TUNGGAKAN*

Tagihan *{$category}* untuk anak Anda:
- Nama: {$studentName}
- Kelas: {$className}
- Periode: {$periode}
- Nominal: {$nominal}

telah *MELEWATI JATUH TEMPO* sejak {$dueDate}.

Mohon segera melakukan pembayaran untuk menghindari sanksi administrasi.

Jika sudah melakukan pembayaran, mohon abaikan pesan ini.

Terima kasih atas perhatiannya.

Salam,
{$schoolName}
MSG;
    }
}
