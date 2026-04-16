<?php

namespace App\Notifications;

use App\Domain\Notification\DTOs\CustomerMailData;
use App\Domain\Notification\Services\NotificationPreferenceService;
use App\Models\Shipment;

class ShipmentStatusUpdatedNotification extends CustomerMailNotification
{
    public function __construct(
        public readonly Shipment $shipment,
        public readonly string $fromStatus,
        public readonly string $toStatus,
        public readonly string $ordersUrl,
    ) {}

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return app(NotificationPreferenceService::class)->allows($this->shipment->order->user, 'shipments_status_updates');
    }

    protected function mailData(object $notifiable): CustomerMailData
    {
        $actionText = 'View orders';
        $actionUrl = $this->ordersUrl;

        if ($this->shipment->tracking_url !== null && $this->shipment->tracking_url !== '') {
            $actionText = 'Track shipment';
            $actionUrl = $this->shipment->tracking_url;
        }

        $facts = [
            'Order number' => $this->shipment->order->order_number,
            'Previous status' => $this->statusLabel($this->fromStatus),
            'Current status' => $this->statusLabel($this->toStatus),
        ];

        if ($this->shipment->carrier_name !== null && $this->shipment->carrier_name !== '') {
            $facts['Carrier'] = $this->shipment->carrier_name;
        }

        if ($this->shipment->tracking_number !== null && $this->shipment->tracking_number !== '') {
            $facts['Tracking number'] = $this->shipment->tracking_number;
        }

        return new CustomerMailData(
            subject: 'Shipment update for '.$this->shipment->order->order_number,
            preheader: 'Your shipment status has changed to '.$this->statusLabel($this->toStatus).'.',
            eyebrow: 'Shipment update',
            title: 'Your shipment is now '.$this->statusLabel($this->toStatus),
            intro: 'We have updated the status of your shipment.',
            lines: [
                'Use the action below to check tracking or review the order details.',
            ],
            actionText: $actionText,
            actionUrl: $actionUrl,
            facts: $facts,
        );
    }
}
