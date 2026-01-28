<?php

namespace App\Notifications;

use App\Models\PsbRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PsbDocumentRevisionRequested - Notifikasi permintaan revisi dokumen
 *
 * Notifikasi ini bertujuan untuk mengirim email ke orang tua
 * bahwa ada dokumen yang perlu direvisi atau diunggah ulang
 */
class PsbDocumentRevisionRequested extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance dengan data pendaftaran
     */
    public function __construct(
        public PsbRegistration $registration
    ) {}

    /**
     * Get the notification's delivery channels
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification
     */
    public function toMail(object $notifiable): MailMessage
    {
        $schoolName = config('app.name', 'Sekolah');

        // Load dokumen yang perlu revisi
        $documentsNeedRevision = $this->registration->documents()
            ->where('status', 'rejected')
            ->whereNotNull('revision_note')
            ->get();

        $mail = (new MailMessage)
            ->subject("Permintaan Revisi Dokumen PSB - {$this->registration->registration_number}")
            ->greeting("Assalamu'alaikum Wr. Wb.")
            ->line('Kami informasikan bahwa terdapat dokumen yang perlu diperbaiki/diunggah ulang untuk pendaftaran:')
            ->line("**Nomor Pendaftaran:** {$this->registration->registration_number}")
            ->line("**Nama Calon Siswa:** {$this->registration->student_name}")
            ->line('---')
            ->line('**Dokumen yang perlu direvisi:**');

        // Tambahkan daftar dokumen yang perlu revisi
        foreach ($documentsNeedRevision as $doc) {
            $mail->line("- **{$doc->getDocumentTypeLabel()}**: {$doc->revision_note}");
        }

        $mail->line('---')
            ->line('**Langkah Selanjutnya:**')
            ->line('1. Siapkan dokumen yang diminta')
            ->line('2. Hubungi pihak sekolah untuk mengunggah ulang dokumen')
            ->action('Cek Status Pendaftaran', url('/psb/tracking'))
            ->line('Mohon segera melengkapi dokumen agar proses verifikasi dapat dilanjutkan.')
            ->salutation("Wassalamu'alaikum Wr. Wb.\n\nHormat kami,\nTim PSB {$schoolName}");

        return $mail;
    }

    /**
     * Get the array representation of the notification untuk database channel
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $documentsNeedRevision = $this->registration->documents()
            ->where('status', 'rejected')
            ->whereNotNull('revision_note')
            ->pluck('document_type')
            ->toArray();

        return [
            'registration_id' => $this->registration->id,
            'registration_number' => $this->registration->registration_number,
            'student_name' => $this->registration->student_name,
            'status' => 'document_review',
            'documents_need_revision' => $documentsNeedRevision,
            'message' => "Dokumen pendaftaran {$this->registration->student_name} perlu direvisi.",
        ];
    }
}
