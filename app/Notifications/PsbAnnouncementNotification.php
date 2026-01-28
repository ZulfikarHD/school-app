<?php

namespace App\Notifications;

use App\Models\PsbRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PsbAnnouncementNotification - Notifikasi pengumuman PSB
 *
 * Notifikasi ini bertujuan untuk mengirim email ke orang tua
 * bahwa pendaftaran anak mereka telah diumumkan dan diterima
 * dengan informasi langkah selanjutnya untuk daftar ulang
 */
class PsbAnnouncementNotification extends Notification implements ShouldQueue
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
            ->subject("Pengumuman Penerimaan PSB - {$this->registration->registration_number}")
            ->greeting("Assalamu'alaikum Wr. Wb.")
            ->line('Dengan rahmat Tuhan Yang Maha Esa, kami sampaikan bahwa:')
            ->line('')
            ->line("**Nomor Pendaftaran:** {$this->registration->registration_number}")
            ->line("**Nama Calon Siswa:** {$this->registration->student_name}")
            ->line('')
            ->line('## DINYATAKAN **DITERIMA** ##')
            ->line('')
            ->line("sebagai calon siswa baru di {$schoolName}.")
            ->line('---')
            ->line('**Langkah Selanjutnya:**')
            ->line('1. Silakan login ke portal orang tua untuk melakukan daftar ulang')
            ->line('2. Upload bukti pembayaran biaya pendaftaran')
            ->line('3. Tunggu verifikasi dari admin')
            ->line('4. Setelah terverifikasi, Anda akan menerima informasi lebih lanjut')
            ->line('')
            ->line('**Informasi Penting:**')
            ->line('- Daftar ulang wajib dilakukan sesuai jadwal yang ditentukan')
            ->line('- Siapkan berkas asli untuk verifikasi pada saat daftar ulang')
            ->line('- Pembayaran dapat dilakukan melalui transfer bank')
            ->action('Mulai Daftar Ulang', url('/login'))
            ->line('---')
            ->line("Selamat dan terima kasih telah mempercayakan pendidikan putra/putri Anda kepada {$schoolName}.")
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
            'status' => 'announced',
            'message' => "Pengumuman PSB: {$this->registration->student_name} dinyatakan DITERIMA.",
        ];
    }
}
