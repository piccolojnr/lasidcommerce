<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use App\Domain\Notification\Services\NotificationPreferenceService;
use App\Models\Payment;

class PaymentReceivedNotification extends CustomerMailNotification
{
    public function __construct(
        public readonly Payment $payment,
        public readonly string $ordersUrl,
    ) {}

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return app(NotificationPreferenceService::class)->allows($this->payment->order->user, 'payments_received');
    }

    protected function mailData(object $notifiable): CustomerMailData
    {
        $order = $this->payment->order;

        return new CustomerMailData(
            subject: 'Payment received for '.$order->order_number,
            preheader: 'Your payment was received successfully.',
            eyebrow: 'Payment received',
            title: 'Payment confirmed',
            intro: 'We have received your payment and updated your order.',
            lines: [
                'You can use the orders page to track the rest of the fulfillment process.',
            ],
            actionText: 'View orders',
            actionUrl: $this->ordersUrl,
            facts: [
                'Order number' => $order->order_number,
                'Amount paid' => $this->money($this->payment->amount, $this->payment->currency_code),
                'Reference' => $this->payment->reference,
                'Paid at' => $this->formatDateTime($this->payment->paid_at ?? now()),
            ],
        );
    }
}
