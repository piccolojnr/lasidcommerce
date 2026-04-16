<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use App\Domain\Notification\Services\NotificationPreferenceService;
use App\Models\Order;

class OrderPlacedNotification extends CustomerMailNotification
{
    public function __construct(
        public readonly Order $order,
        public readonly string $ordersUrl,
    ) {}

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return app(NotificationPreferenceService::class)->allows($this->order->user, 'orders_placed');
    }

    protected function mailData(object $notifiable): CustomerMailData
    {
        return new CustomerMailData(
            subject: 'Order received: '.$this->order->order_number,
            preheader: 'We have received your order and are preparing the next steps.',
            eyebrow: 'Order placed',
            title: 'We have received your order',
            intro: 'Your order has been placed successfully and is now awaiting payment or processing.',
            lines: [
                'We will email you again when the payment or fulfillment status changes.',
            ],
            actionText: 'View orders',
            actionUrl: $this->ordersUrl,
            facts: [
                'Order number' => $this->order->order_number,
                'Total' => $this->money($this->order->total_amount, $this->order->currency_code),
                'Shipping method' => $this->order->shipping_method_name ?? 'Not set',
                'Placed at' => $this->formatDateTime($this->order->placed_at ?? now()),
            ],
        );
    }
}
