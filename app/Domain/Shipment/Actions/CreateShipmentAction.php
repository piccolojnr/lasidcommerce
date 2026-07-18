<?php

namespace App\Domain\Shipment\Actions;

use App\Domain\Order\Services\OrderFulfillmentService;
use App\Domain\Shipment\Exceptions\ShipmentException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Support\Facades\DB;

class CreateShipmentAction
{
    public function __construct(
        private OrderFulfillmentService $fulfillmentService,
    ) {}

    /**
     * @throws ShipmentException
     */
    public function execute(Order $order, array $data): Shipment
    {
        if ($order->status !== 'processing') {
            throw new ShipmentException(
                "Shipments can only be created for orders in 'processing' status. Current status: '{$order->status}'."
            );
        }

        $order->loadMissing([
            'orderItems.shipmentItems.shipment',
            'shipments',
        ]);

        $summary = collect($this->fulfillmentService->summarize($order)['items'])->keyBy('id');
        $requestedItems = collect($data['items']);

        if ($requestedItems->duplicates('order_item_id')->isNotEmpty()) {
            throw new ShipmentException('Each order item can only appear once in a shipment request.');
        }

        foreach ($requestedItems as $item) {
            $orderItem = $summary->get($item['order_item_id']);

            if ($orderItem === null) {
                throw new ShipmentException('Shipment items must belong to the selected order.');
            }

            if ($item['quantity'] > $orderItem['remaining_quantity']) {
                $orderItemModel = $order->orderItems->firstWhere('id', $item['order_item_id']);

                throw new ShipmentException(
                    sprintf(
                        'Requested quantity for "%s" exceeds the remaining shippable quantity.',
                        $orderItemModel instanceof OrderItem ? $orderItemModel->product_name : 'this item',
                    )
                );
            }
        }

        return DB::transaction(function () use ($order, $data) {
            $shipment = Shipment::create([
                'order_id' => $order->id,
                'warehouse_location_id' => $data['warehouse_location_id'] ?? null,
                'shipping_method_id' => $order->shipping_method_id,
                'status' => 'pending',
                'carrier_name' => $data['carrier_name'] ?? null,
                'tracking_number' => $data['tracking_number'] ?? null,
                'tracking_url' => $data['tracking_url'] ?? null,
                'notes' => $data['notes'] ?? null,
                'rider_name' => $data['rider_name'] ?? null,
                'rider_phone' => $data['rider_phone'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'order_item_id' => $item['order_item_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            $this->fulfillmentService->sync($order->fresh([
                'orderItems.shipmentItems.shipment',
                'shipments',
            ]));

            return $shipment->load('shipmentItems.orderItem', 'warehouseLocation', 'shippingMethod');
        });
    }
}
