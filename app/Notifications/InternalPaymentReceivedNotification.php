<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use App\Models\Payment;

class InternalPaymentReceivedNotification extends InternalMailNotification
{
    public function __construct(
        public readonly Payment $payment,
    ) {}

    protected function mailData(object $notifiable): CustomerMailData
    {
        $order = $this->payment->order;

        return new CustomerMailData(
            subject: 'Internal alert: payment received '.$order->order_number,
            preheader: 'An order payment has been confirmed.',
            eyebrow: 'Internal alert',
            title: 'Payment received',
            intro: 'A payment has been confirmed for an order.',
            facts: [
                'Order number' => $order->order_number,
                'Customer email' => $order->email,
                'Amount' => $this->money($this->payment->amount, $this->payment->currency_code),
                'Reference' => $this->payment->reference,
            ],
        );
    }
}
