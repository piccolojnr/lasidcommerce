<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerMagicLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $verifyUrl,
        public readonly \DateTimeInterface $expiresAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your sign-in link')
            ->line('Use this one-time link to sign in to your customer account.')
            ->action('Sign in', $this->verifyUrl)
            ->line('This link expires at '.$this->expiresAt->format('Y-m-d H:i:s').' UTC.')
            ->line('If you did not request this link, you can ignore this email.');
    }
}
