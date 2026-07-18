<?php

namespace App\Domain\Order\Queries;

use App\Models\Order;
use Carbon\CarbonInterface;

class GetCustomerOrderTimelineQuery
{
    public function execute(Order $order): array
    {
        $order->loadMissing(['payments', 'orderStatusHistories', 'shipments']);

        $events = [];

        // Order placed
        if ($order->placed_at) {
            $events[] = $this->event(
                'order_placed',
                'Order Placed',
                'Your order was placed successfully.',
                $order->placed_at,
            );
        }

        // Payment confirmed
        $payment = $order->payments->first(fn ($p) => $p->paid_at !== null);
        if ($payment) {
            $events[] = $this->event(
                'payment_confirmed',
                'Payment Confirmed',
                'Your payment was received and confirmed.',
                $payment->paid_at,
            );
        }

        // Order status histories
        $statusMap = [
            'confirmed' => ['order_confirmed',  'Order Confirmed', 'Your order has been confirmed.'],
            'processing' => ['order_processing', 'Being Prepared',  'Your order is being prepared for shipment.'],
            'shipped' => ['order_shipped',    'Order Shipped',   'Your order has been shipped.'],
            'delivered' => ['order_delivered',  'Order Delivered', 'Your order has been delivered.'],
            'completed' => ['order_completed',  'Order Completed', 'Your order has been completed.'],
            'cancelled' => ['order_cancelled',  'Order Cancelled', 'Your order was cancelled.'],
        ];

        foreach ($order->orderStatusHistories as $history) {
            if (isset($statusMap[$history->to_status])) {
                [$type, $label, $desc] = $statusMap[$history->to_status];
                $events[] = $this->event($type, $label, $desc, $history->created_at);
            }
        }

        // Shipment milestones
        $shipmentEvents = [
            'packed_at' => ['shipment_packed',    'Order Packed',        'Your order has been packed and is ready for dispatch.'],
            'shipped_at' => ['shipment_shipped',   'Shipment Dispatched', 'Your order is on its way.'],
            'delivered_at' => ['shipment_delivered', 'Order Delivered',     'Your order has been delivered.'],
            'failed_at' => ['shipment_failed',    'Delivery Failed',     'Delivery was unsuccessful. We will be in touch.'],
            'returned_at' => ['shipment_returned',  'Shipment Returned',   'Your shipment has been returned.'],
        ];

        foreach ($order->shipments as $shipment) {
            foreach ($shipmentEvents as $field => [$type, $label, $desc]) {
                if ($shipment->$field !== null) {
                    $events[] = $this->event($type, $label, $desc, $shipment->$field);
                }
            }
        }

        // Sort chronologically ascending
        usort($events, fn ($a, $b) => $a['occurred_at'] <=> $b['occurred_at']);

        return $events;
    }

    private function event(string $type, string $label, string $description, CarbonInterface $occurredAt): array
    {
        return [
            'type' => $type,
            'label' => $label,
            'description' => $description,
            'occurred_at' => $occurredAt->toISOString(),
        ];
    }
}
