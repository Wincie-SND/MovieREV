<?php

namespace App\Mail;

use App\Services\AccountVerificationCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly string $purpose,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->purpose === 'email-change'
                ? 'Verify your new MovieREV email address'
                : 'Your MovieREV password change code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-verification-code',
            with: [
                'code' => $this->code,
                'purposeLabel' => $this->purpose === 'email-change' ? 'email address change' : 'password change',
                'expiresMinutes' => AccountVerificationCodeService::CODE_TTL_MINUTES,
            ],
        );
    }
}
