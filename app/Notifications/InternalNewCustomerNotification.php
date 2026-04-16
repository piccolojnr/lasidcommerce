<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use App\Models\User;

class InternalNewCustomerNotification extends InternalMailNotification
{
    public function __construct(
        public readonly User $user,
        public readonly bool $createdViaMagicLink = true,
    ) {}

    protected function mailData(object $notifiable): CustomerMailData
    {
        return new CustomerMailData(
            subject: 'Internal alert: new customer account',
            preheader: 'A new customer account was created.',
            eyebrow: 'Internal alert',
            title: 'New customer account created',
            intro: 'A new customer account has been created and verified.',
            facts: [
                'Email' => $this->user->email,
                'Customer ID' => (string) $this->user->id,
                'Created via' => $this->createdViaMagicLink ? 'Magic link' : 'Application flow',
            ],
        );
    }
}
