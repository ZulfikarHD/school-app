<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create instance dengan reset URL untuk kirim ke user via email
     * dengan queue untuk better performance
     */
    public function __construct(
        public string $resetUrl,
        public string $userName
    ) {
        //
    }

    /**
     * Email envelope dengan subject dalam Bahasa Indonesia
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password - Sistem Informasi Sekolah',
        );
    }

    /**
     * Email content dengan view template dan data yang diperlukan
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'userName' => $this->userName,
                'resetUrl' => $this->resetUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
