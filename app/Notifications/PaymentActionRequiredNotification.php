<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use App\Domain\Notification\Services\NotificationPreferenceService;
use App\Models\Order;
use App\Models\Payment;

class PaymentActionRequiredNotification extends CustomerMailNotification
{
    public function __construct(
        public readonly Order $order,
        public readonly Payment $payment,
        public readonly string $authorizationUrl,
    ) {}

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return app(NotificationPreferenceService::class)->allows($this->order->user, 'payments_action_required');
    }

    protected function mailData(object $notifiable): CustomerMailData
    {
        return new CustomerMailData(
            subject: 'Complete payment for '.$this->order->order_number,
            preheader: 'Your order is waiting for payment confirmation.',
            eyebrow: 'Payment pending',
            title: 'Complete your payment',
            intro: 'Use the secure payment link below to complete your checkout.',
            lines: [
                'Your order will stay pending until payment is confirmed.',
            ],
            actionText: 'Continue payment',
            actionUrl: $this->authorizationUrl,
            facts: [
                'Order number' => $this->order->order_number,
                'Amount due' => $this->money($this->payment->amount, $this->payment->currency_code),
                'Reference' => $this->payment->reference,
            ],
        );
    }
}
