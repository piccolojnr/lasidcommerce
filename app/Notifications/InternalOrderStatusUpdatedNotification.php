<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use App\Models\Order;

class InternalOrderStatusUpdatedNotification extends InternalMailNotification
{
    public function __construct(
        public readonly Order $order,
        public readonly string $fromStatus,
        public readonly string $toStatus,
        public readonly ?string $note = null,
    ) {}

    protected function mailData(object $notifiable): CustomerMailData
    {
        $lines = [];

        if ($this->note !== null && $this->note !== '') {
            $lines[] = 'Note: '.$this->note;
        }

        return new CustomerMailData(
            subject: 'Internal alert: order status updated '.$this->order->order_number,
            preheader: 'An order status has changed.',
            eyebrow: 'Internal alert',
            title: 'Order status updated',
            intro: 'An order status transition was recorded.',
            lines: $lines,
            facts: [
                'Order number' => $this->order->order_number,
                'Customer email' => $this->order->email,
                'Previous status' => $this->statusLabel($this->fromStatus),
                'Current status' => $this->statusLabel($this->toStatus),
            ],
        );
    }
}
