<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;

class CustomerWelcomeNotification extends CustomerMailNotification
{
    public function __construct(
        public readonly string $accountUrl,
    ) {}

    protected function mailData(object $notifiable): CustomerMailData
    {
        return new CustomerMailData(
            subject: 'Your account is ready',
            preheader: 'Your customer account has been created and is ready to use.',
            eyebrow: 'Welcome',
            title: 'Your account is ready',
            intro: 'Your email is confirmed and your customer account is now active.',
            lines: [
                'You can use your account to track orders, manage addresses, and continue checkout faster.',
            ],
            actionText: 'Open account',
            actionUrl: $this->accountUrl,
        );
    }
}
