<?php

namespace App\Notifications;

use App\Models\PsbPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PsbPaymentVerified - Notifikasi verifikasi pembayaran PSB
 *
 * Notifikasi ini bertujuan untuk mengirim email ke orang tua
 * tentang status verifikasi pembayaran daftar ulang PSB
 */
class PsbPaymentVerified extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance dengan data pembayaran
     */
    public function __construct(
        public PsbPayment $payment
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
        $registration = $this->payment->registration;
        $isApproved = $this->payment->status === PsbPayment::STATUS_VERIFIED;

        if ($isApproved) {
            return $this->buildApprovedMail($schoolName, $registration);
        }

        return $this->buildRejectedMail($schoolName, $registration);
    }

    /**
     * Build mail untuk pembayaran yang disetujui
     */
    protected function buildApprovedMail(string $schoolName, $registration): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Pembayaran PSB Terverifikasi - {$registration->registration_number}")
            ->greeting("Assalamu'alaikum Wr. Wb.")
            ->line('Dengan senang hati kami informasikan bahwa pembayaran Anda telah **TERVERIFIKASI**.')
            ->line('')
            ->line("**Nomor Pendaftaran:** {$registration->registration_number}")
            ->line("**Nama Calon Siswa:** {$registration->student_name}")
            ->line("**Jumlah Pembayaran:** {$this->payment->getFormattedAmount()}")
            ->line("**Jenis Pembayaran:** {$this->payment->getPaymentTypeLabel()}")
            ->line('');

        // Check if registration is completed
        if ($registration->status === 'completed') {
            $mail->line('---')
                ->line('## Selamat! Pendaftaran Anda telah selesai. ##')
                ->line('')
                ->line('Putra/putri Anda telah resmi terdaftar sebagai siswa baru.')
                ->line('Informasi lebih lanjut mengenai orientasi dan jadwal akan disampaikan kemudian.')
                ->action('Lihat Status', url('/login'));
        } else {
            $mail->line('Silakan tunggu proses selanjutnya atau upload pembayaran tambahan jika diperlukan.')
                ->action('Cek Status Daftar Ulang', url('/login'));
        }

        return $mail
            ->line('---')
            ->line("Terima kasih telah mempercayakan pendidikan putra/putri Anda kepada {$schoolName}.")
            ->salutation("Wassalamu'alaikum Wr. Wb.\n\nHormat kami,\nTim PSB {$schoolName}");
    }

    /**
     * Build mail untuk pembayaran yang ditolak
     */
    protected function buildRejectedMail(string $schoolName, $registration): MailMessage
    {
        return (new MailMessage)
            ->subject("Pembayaran PSB Ditolak - {$registration->registration_number}")
            ->greeting("Assalamu'alaikum Wr. Wb.")
            ->line('Mohon maaf, pembayaran Anda **TIDAK DAPAT DIVERIFIKASI**.')
            ->line('')
            ->line("**Nomor Pendaftaran:** {$registration->registration_number}")
            ->line("**Nama Calon Siswa:** {$registration->student_name}")
            ->line("**Jumlah Pembayaran:** {$this->payment->getFormattedAmount()}")
            ->line('')
            ->line('**Alasan Penolakan:**')
            ->line($this->payment->notes ?? 'Bukti pembayaran tidak valid atau tidak dapat diverifikasi.')
            ->line('---')
            ->line('**Langkah Selanjutnya:**')
            ->line('1. Silakan periksa kembali bukti pembayaran Anda')
            ->line('2. Pastikan transfer sudah berhasil')
            ->line('3. Upload ulang bukti pembayaran yang valid')
            ->action('Upload Ulang Bukti Pembayaran', url('/login'))
            ->line('---')
            ->line('Jika Anda memiliki pertanyaan, silakan hubungi panitia PSB.')
            ->salutation("Wassalamu'alaikum Wr. Wb.\n\nHormat kami,\nTim PSB {$schoolName}");
    }

    /**
     * Get the array representation of the notification untuk database channel
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $registration = $this->payment->registration;
        $isApproved = $this->payment->status === PsbPayment::STATUS_VERIFIED;

        return [
            'payment_id' => $this->payment->id,
            'registration_id' => $registration->id,
            'registration_number' => $registration->registration_number,
            'student_name' => $registration->student_name,
            'status' => $isApproved ? 'verified' : 'rejected',
            'message' => $isApproved
                ? "Pembayaran untuk {$registration->student_name} telah terverifikasi."
                : "Pembayaran untuk {$registration->student_name} ditolak.",
        ];
    }
}
