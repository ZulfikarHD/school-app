<?php

namespace App\Notifications;

use App\Models\PsbRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PsbRegistrationRejected - Notifikasi pendaftaran ditolak
 *
 * Notifikasi ini bertujuan untuk mengirim email ke orang tua
 * bahwa pendaftaran anak mereka ditolak beserta alasannya
 */
class PsbRegistrationRejected extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject("Pemberitahuan Hasil PSB - {$this->registration->registration_number}")
            ->greeting("Assalamu'alaikum Wr. Wb.")
            ->line('Dengan berat hati kami informasikan bahwa pendaftaran calon siswa baru dengan data berikut **tidak dapat kami terima**:')
            ->line("**Nomor Pendaftaran:** {$this->registration->registration_number}")
            ->line("**Nama Calon Siswa:** {$this->registration->student_name}")
            ->line('---')
            ->line('**Alasan:**')
            ->line($this->registration->rejection_reason)
            ->line('---')
            ->line('Jika Anda memiliki pertanyaan atau membutuhkan informasi lebih lanjut, silakan hubungi kami.')
            ->action('Hubungi Kami', url('/contact'))
            ->line("Terima kasih atas kepercayaan Anda kepada {$schoolName}.")
            ->salutation("Wassalamu'alaikum Wr. Wb.\n\nHormat kami,\nTim PSB {$schoolName}");
    }

    /**
     * Get the array representation of the notification untuk database channel
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'registration_id' => $this->registration->id,
            'registration_number' => $this->registration->registration_number,
            'student_name' => $this->registration->student_name,
            'status' => 'rejected',
            'rejection_reason' => $this->registration->rejection_reason,
            'message' => "Pendaftaran {$this->registration->student_name} tidak dapat diterima.",
        ];
    }
}
