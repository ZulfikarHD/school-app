<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserAccountCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create instance dengan user data dan password untuk email notification
     * yang akan dikirim via queue untuk better performance
     */
    public function __construct(
        public User $user,
        public string $password
    ) {
        //
    }

    /**
     * Email envelope dengan subject dalam Bahasa Indonesia
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun Anda Telah Dibuat - Sistem Informasi Sekolah',
        );
    }

    /**
     * Email content dengan view template dan data yang diperlukan
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-account-created',
            with: [
                'userName' => $this->user->name,
                'username' => $this->user->username,
                'password' => $this->password,
                'loginUrl' => route('login'),
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
