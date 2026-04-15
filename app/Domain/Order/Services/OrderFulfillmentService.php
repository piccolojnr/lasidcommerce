<?php

namespace App\Domain\Order\Services;

use App\Models\Order;

class OrderFulfillmentService
{
    private const ALLOCATED_SHIPMENT_STATUSES = ['pending', 'packed', 'shipped', 'in_transit'];

    private const IN_PROGRESS_SHIPMENT_STATUSES = ['shipped', 'in_transit'];

    private const DELIVERED_SHIPMENT_STATUSES = ['delivered'];

    private const PENDING_SHIPMENT_STATUSES = ['pending', 'packed'];

    private const ATTENTION_SHIPMENT_STATUSES = ['failed', 'returned'];

    public function summarize(Order $order): array
    {
        $order->loadMissing([
            'orderItems.shipmentItems.shipment',
            'shipments',
        ]);

        $totalOrderedQuantity = 0;
        $totalAllocatedQuantity = 0;
        $totalInProgressQuantity = 0;
        $totalDeliveredQuantity = 0;

        $items = $order->orderItems->map(function ($item) use (
            &$totalOrderedQuantity,
            &$totalAllocatedQuantity,
            &$totalInProgressQuantity,
            &$totalDeliveredQuantity
        ): array {
            $allocatedQuantity = 0;
            $inProgressQuantity = 0;
            $deliveredQuantity = 0;

            foreach ($item->shipmentItems as $shipmentItem) {
                $status = $shipmentItem->shipment?->status;

                if ($status === null) {
                    continue;
                }

                if (in_array($status, self::ALLOCATED_SHIPMENT_STATUSES, true)) {
                    $allocatedQuantity += $shipmentItem->quantity;
                }

                if (in_array($status, self::IN_PROGRESS_SHIPMENT_STATUSES, true)) {
                    $inProgressQuantity += $shipmentItem->quantity;
                }

                if (in_array($status, self::DELIVERED_SHIPMENT_STATUSES, true)) {
                    $deliveredQuantity += $shipmentItem->quantity;
                }
            }

            $remainingQuantity = max($item->quantity - $allocatedQuantity - $deliveredQuantity, 0);

            $totalOrderedQuantity += $item->quantity;
            $totalAllocatedQuantity += $allocatedQuantity;
            $totalInProgressQuantity += $inProgressQuantity;
            $totalDeliveredQuantity += $deliveredQuantity;

            return [
                'id' => $item->id,
                'allocated_quantity' => $allocatedQuantity,
                'in_progress_quantity' => $inProgressQuantity,
                'delivered_quantity' => $deliveredQuantity,
                'remaining_quantity' => $remainingQuantity,
            ];
        })->values();

        $fulfillmentStatus = $this->determineFulfillmentStatus(
            $totalOrderedQuantity,
            $totalAllocatedQuantity,
            $totalDeliveredQuantity,
        );
        $totalRemainingQuantity = max($totalOrderedQuantity - $totalAllocatedQuantity - $totalDeliveredQuantity, 0);

        return [
            'items' => $items->all(),
            'total_ordered_quantity' => $totalOrderedQuantity,
            'total_allocated_quantity' => $totalAllocatedQuantity,
            'total_in_progress_quantity' => $totalInProgressQuantity,
            'total_delivered_quantity' => $totalDeliveredQuantity,
            'total_remaining_quantity' => $totalRemainingQuantity,
            'fulfillment_status' => $fulfillmentStatus,
            'can_create_shipment' => $order->status === 'processing' && $items->contains(fn (array $item) => $item['remaining_quantity'] > 0),
            'shipping_summary' => $this->determineShippingSummary($order, $totalOrderedQuantity, $totalAllocatedQuantity, $totalDeliveredQuantity, $totalRemainingQuantity),
            'needs_reshipment' => $this->needsReshipment($order, $totalRemainingQuantity),
        ];
    }

    public function sync(Order $order): Order
    {
        $summary = $this->summarize($order);
        if ($order->fulfillment_status !== $summary['fulfillment_status']) {
            $order->update([
                'fulfillment_status' => $summary['fulfillment_status'],
            ]);
        }

        return $order->fresh();
    }

    private function determineFulfillmentStatus(int $totalOrderedQuantity, int $totalAllocatedQuantity, int $totalDeliveredQuantity): string
    {
        if ($totalOrderedQuantity === 0) {
            return 'unfulfilled';
        }

        if ($totalDeliveredQuantity >= $totalOrderedQuantity) {
            return 'fulfilled';
        }

        if ($totalAllocatedQuantity > 0 || $totalDeliveredQuantity > 0) {
            return 'partially_fulfilled';
        }

        return 'unfulfilled';
    }

    private function determineShippingSummary(
        Order $order,
        int $totalOrderedQuantity,
        int $totalAllocatedQuantity,
        int $totalDeliveredQuantity,
        int $totalRemainingQuantity,
    ): string {
        if ($totalOrderedQuantity === 0) {
            return 'no_shipment';
        }

        if ($totalDeliveredQuantity >= $totalOrderedQuantity) {
            return 'delivered';
        }

        if ($this->needsReshipment($order, $totalRemainingQuantity)) {
            return 'attention_required';
        }

        if ($totalDeliveredQuantity > 0) {
            return 'partially_delivered';
        }

        if ($order->shipments->contains(fn ($shipment) => in_array($shipment->status, self::IN_PROGRESS_SHIPMENT_STATUSES, true))) {
            return 'partially_shipped';
        }

        if ($order->shipments->contains(fn ($shipment) => in_array($shipment->status, self::PENDING_SHIPMENT_STATUSES, true))) {
            return 'shipment_pending';
        }

        if ($totalAllocatedQuantity > 0) {
            return 'shipment_pending';
        }

        return 'no_shipment';
    }

    private function needsReshipment(Order $order, int $totalRemainingQuantity): bool
    {
        return $totalRemainingQuantity > 0
            && $order->shipments->contains(fn ($shipment) => in_array($shipment->status, self::ATTENTION_SHIPMENT_STATUSES, true));
    }
}
