<?php

namespace App\Domain\Order\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;

class OrderFulfillmentService
{
    private const ACTIVE_SHIPMENT_STATUSES = ['pending', 'packed', 'shipped', 'in_transit', 'delivered'];

    private const IN_PROGRESS_SHIPMENT_STATUSES = ['pending', 'packed', 'shipped', 'in_transit'];

    private const DELIVERED_SHIPMENT_STATUSES = ['delivered'];

    private const SHIPPED_ORDER_SIGNAL_STATUSES = ['shipped', 'in_transit', 'delivered'];

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

                if (in_array($status, self::ACTIVE_SHIPMENT_STATUSES, true)) {
                    $allocatedQuantity += $shipmentItem->quantity;
                }

                if (in_array($status, self::IN_PROGRESS_SHIPMENT_STATUSES, true)) {
                    $inProgressQuantity += $shipmentItem->quantity;
                }

                if (in_array($status, self::DELIVERED_SHIPMENT_STATUSES, true)) {
                    $deliveredQuantity += $shipmentItem->quantity;
                }
            }

            $remainingQuantity = max($item->quantity - $allocatedQuantity, 0);

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

        return [
            'items' => $items->all(),
            'total_ordered_quantity' => $totalOrderedQuantity,
            'total_allocated_quantity' => $totalAllocatedQuantity,
            'total_in_progress_quantity' => $totalInProgressQuantity,
            'total_delivered_quantity' => $totalDeliveredQuantity,
            'total_remaining_quantity' => max($totalOrderedQuantity - $totalAllocatedQuantity, 0),
            'fulfillment_status' => $fulfillmentStatus,
            'can_create_shipment' => $order->status === 'processing' && $items->contains(fn (array $item) => $item['remaining_quantity'] > 0),
            'synced_order_status' => $this->determineOrderStatus($order, $totalOrderedQuantity, $totalDeliveredQuantity),
        ];
    }

    public function sync(Order $order): Order
    {
        $summary = $this->summarize($order);
        $updateData = [];

        if ($order->fulfillment_status !== $summary['fulfillment_status']) {
            $updateData['fulfillment_status'] = $summary['fulfillment_status'];
        }

        if ($order->status !== $summary['synced_order_status']) {
            $fromStatus = $order->status;
            $updateData['status'] = $summary['synced_order_status'];
        }

        if ($updateData !== []) {
            $order->update($updateData);
        }

        if (isset($fromStatus)) {
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $fromStatus,
                'to_status' => $summary['synced_order_status'],
                'note' => $summary['synced_order_status'] === 'delivered'
                    ? 'Order marked as delivered from shipment activity.'
                    : 'Order marked as shipped from shipment activity.',
                'changed_by' => null,
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

    private function determineOrderStatus(Order $order, int $totalOrderedQuantity, int $totalDeliveredQuantity): string
    {
        if (in_array($order->status, ['cancelled', 'completed', 'delivered'], true)) {
            return $order->status;
        }

        if (
            $totalOrderedQuantity > 0
            && $totalDeliveredQuantity >= $totalOrderedQuantity
            && in_array($order->status, ['processing', 'shipped'], true)
        ) {
            return 'delivered';
        }

        if (
            $order->status === 'processing'
            && $order->shipments->contains(fn ($shipment) => in_array($shipment->status, self::SHIPPED_ORDER_SIGNAL_STATUSES, true))
        ) {
            return 'shipped';
        }

        return $order->status;
    }
}
