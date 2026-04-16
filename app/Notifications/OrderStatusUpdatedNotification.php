<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use App\Domain\Notification\Services\NotificationPreferenceService;
use App\Models\Order;

class OrderStatusUpdatedNotification extends CustomerMailNotification
{
    public function __construct(
        public readonly Order $order,
        public readonly string $fromStatus,
        public readonly string $toStatus,
        public readonly string $ordersUrl,
        public readonly ?string $note = null,
    ) {}

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return app(NotificationPreferenceService::class)->allows($this->order->user, 'orders_status_updates');
    }

    protected function mailData(object $notifiable): CustomerMailData
    {
        $lines = [
            'You can use the orders page for the latest fulfillment and payment details.',
        ];

        if ($this->note !== null && $this->note !== '') {
            $lines[] = 'Update note: '.$this->note;
        }

        return new CustomerMailData(
            subject: 'Order update: '.$this->order->order_number,
            preheader: 'Your order status has changed to '.$this->statusLabel($this->toStatus).'.',
            eyebrow: 'Order update',
            title: 'Your order is now '.$this->statusLabel($this->toStatus),
            intro: 'We have updated the status of your order.',
            lines: $lines,
            actionText: 'View orders',
            actionUrl: $this->ordersUrl,
            facts: [
                'Order number' => $this->order->order_number,
                'Previous status' => $this->statusLabel($this->fromStatus),
                'Current status' => $this->statusLabel($this->toStatus),
            ],
        );
    }
}
