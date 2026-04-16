<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use App\Domain\Notification\Services\NotificationPreferenceService;

class CustomerResetPasswordNotification extends CustomerMailNotification
{
    public function __construct(
        public readonly string $token,
    ) {}

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return app(NotificationPreferenceService::class)->allows($notifiable, 'auth_password_reset');
    }

    protected function mailData(object $notifiable): CustomerMailData
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return new CustomerMailData(
            subject: 'Reset your password',
            preheader: 'Use the secure link to choose a new password.',
            eyebrow: 'Password reset',
            title: 'Reset your password',
            intro: 'We received a request to reset your password.',
            lines: [
                'Use the button below to choose a new password for your account.',
                'If you did not request this change, you can ignore this email.',
            ],
            actionText: 'Reset password',
            actionUrl: $resetUrl,
            footerLines: [
                'This reset link expires automatically based on your account security settings.',
            ],
        );
    }
}
