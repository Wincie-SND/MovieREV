<?php

namespace App\Mail;

use App\Services\AccountVerificationCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly string $purpose,
        public readonly string $userName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                (string) config('mail.from.address'),
                (string) config('mail.from.name', 'MovieREV'),
            ),
            subject: $this->purpose === 'email-change'
                ? 'Your MovieREV email verification code'
                : 'Your MovieREV password verification code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-verification-code',
            with: [
                'code' => $this->code,
                'userName' => $this->userName,
                'purpose' => $this->purpose,
                'expiresMinutes' => AccountVerificationCodeService::CODE_TTL_MINUTES,
            ],
        );
    }
}
