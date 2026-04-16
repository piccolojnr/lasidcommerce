<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use App\Models\Order;

class InternalOrderPlacedNotification extends InternalMailNotification
{
    public function __construct(
        public readonly Order $order,
    ) {}

    protected function mailData(object $notifiable): CustomerMailData
    {
        return new CustomerMailData(
            subject: 'Internal alert: order placed '.$this->order->order_number,
            preheader: 'A new order has been placed.',
            eyebrow: 'Internal alert',
            title: 'New order placed',
            intro: 'A customer has completed order placement.',
            facts: [
                'Order number' => $this->order->order_number,
                'Customer email' => $this->order->email,
                'Status' => $this->statusLabel($this->order->status),
                'Total' => $this->money($this->order->total_amount, $this->order->currency_code),
            ],
        );
    }
}
