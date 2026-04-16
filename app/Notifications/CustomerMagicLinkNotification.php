<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;

class CustomerMagicLinkNotification extends CustomerMailNotification
{
    public function __construct(
        public readonly string $verifyUrl,
        public readonly \DateTimeInterface $expiresAt,
    ) {}

    protected function mailData(object $notifiable): CustomerMailData
    {
        return new CustomerMailData(
            subject: 'Your sign-in link',
            preheader: 'Use this one-time sign-in link to access your customer account.',
            eyebrow: 'Customer access',
            title: 'Sign in to your account',
            intro: 'Use the secure link below to sign in to your customer account.',
            lines: [
                'This one-time link expires at '.$this->formatDateTime($this->expiresAt).'.',
            ],
            actionText: 'Sign in',
            actionUrl: $this->verifyUrl,
            footerLines: [
                'If you did not request this link, you can ignore this email.',
            ],
        );
    }
}
