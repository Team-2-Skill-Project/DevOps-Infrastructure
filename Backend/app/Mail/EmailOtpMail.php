<?php

namespace App\Mail;

use App\Models\EmailOtp;
use Carbon\CarbonInterface;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EmailOtpMail extends Mailable
{
    public function __construct(public string $code, public string $purpose, public CarbonInterface $expiresAt) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->purpose === EmailOtp::PASSWORD_RESET
            ? __('auth.mail.password_reset_subject') : __('auth.mail.email_verification_subject'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.email-otp');
    }
}
