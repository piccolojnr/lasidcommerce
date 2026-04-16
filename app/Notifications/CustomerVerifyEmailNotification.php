<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomerVerifyEmailNotification extends CustomerMailNotification
{
    protected function mailData(object $notifiable): CustomerMailData
    {
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );

        return new CustomerMailData(
            subject: 'Confirm your email address',
            preheader: 'Verify your email address to secure your customer account.',
            eyebrow: 'Account security',
            title: 'Confirm your email address',
            intro: 'Please verify your email address so we can keep your customer account secure.',
            lines: [
                'Use the button below to complete verification.',
                'If you did not create this account, you can ignore this message.',
            ],
            actionText: 'Verify email',
            actionUrl: $verifyUrl,
        );
    }
}
