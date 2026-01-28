<?php

namespace App\Notifications;

use App\Models\PsbRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PsbRegistrationApproved - Notifikasi pendaftaran disetujui
 *
 * Notifikasi ini bertujuan untuk mengirim email ke orang tua
 * bahwa pendaftaran anak mereka telah disetujui
 */
class PsbRegistrationApproved extends Notification implements ShouldQueue
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
            ->subject("Selamat! Pendaftaran PSB Disetujui - {$this->registration->registration_number}")
            ->greeting("Assalamu'alaikum Wr. Wb.")
            ->line('Dengan senang hati kami informasikan bahwa pendaftaran calon siswa baru dengan data berikut telah **DISETUJUI**:')
            ->line("**Nomor Pendaftaran:** {$this->registration->registration_number}")
            ->line("**Nama Calon Siswa:** {$this->registration->student_name}")
            ->line('---')
            ->line('**Langkah Selanjutnya:**')
            ->line('1. Silakan melakukan daftar ulang sesuai jadwal yang telah ditentukan')
            ->line('2. Siapkan berkas asli untuk verifikasi')
            ->line('3. Lakukan pembayaran biaya pendaftaran')
            ->action('Cek Status Pendaftaran', url('/psb/tracking'))
            ->line('---')
            ->line("Terima kasih telah mempercayakan pendidikan putra/putri Anda kepada {$schoolName}.")
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
            'status' => 'approved',
            'message' => "Pendaftaran {$this->registration->student_name} telah disetujui.",
        ];
    }
}
