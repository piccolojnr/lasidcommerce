<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use App\Models\Shipment;

class InternalShipmentStatusUpdatedNotification extends InternalMailNotification
{
    public function __construct(
        public readonly Shipment $shipment,
        public readonly string $fromStatus,
        public readonly string $toStatus,
    ) {}

    protected function mailData(object $notifiable): CustomerMailData
    {
        return new CustomerMailData(
            subject: 'Internal alert: shipment status updated '.$this->shipment->order->order_number,
            preheader: 'A shipment status has changed.',
            eyebrow: 'Internal alert',
            title: 'Shipment status updated',
            intro: 'A shipment transition was recorded.',
            facts: array_filter([
                'Order number' => $this->shipment->order->order_number,
                'Customer email' => $this->shipment->order->email,
                'Previous status' => $this->statusLabel($this->fromStatus),
                'Current status' => $this->statusLabel($this->toStatus),
                'Carrier' => $this->shipment->carrier_name,
                'Tracking number' => $this->shipment->tracking_number,
            ], static fn ($value) => $value !== null && $value !== ''),
        );
    }
}
