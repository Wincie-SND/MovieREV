<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiresMinutes = (int) config('auth.verification.expire', 60);
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes($expiresMinutes),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );

        return (new MailMessage)
            ->from(
                (string) config('mail.from.address'),
                (string) config('mail.from.name', 'MovieREV'),
            )
            ->subject('Verify your MovieREV email address')
            ->view('emails.verify-email', [
                'userName' => (string) ($notifiable->name ?? 'there'),
                'verificationUrl' => $verificationUrl,
                'expiresMinutes' => $expiresMinutes,
            ]);
    }
}
