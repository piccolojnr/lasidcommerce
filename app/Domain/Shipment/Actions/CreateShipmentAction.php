<?php

namespace App\Domain\Shipment\Actions;

use App\Domain\Shipment\Exceptions\ShipmentException;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Support\Facades\DB;

class CreateShipmentAction
{
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

        return DB::transaction(function () use ($order, $data) {
            $shipment = Shipment::create([
                'order_id'        => $order->id,
                'status'          => 'pending',
                'carrier_name'    => $data['carrier_name'] ?? null,
                'tracking_number' => $data['tracking_number'] ?? null,
                'tracking_url'    => $data['tracking_url'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'rider_name'      => $data['rider_name'] ?? null,
                'rider_phone'     => $data['rider_phone'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                ShipmentItem::create([
                    'shipment_id'   => $shipment->id,
                    'order_item_id' => $item['order_item_id'],
                    'quantity'      => $item['quantity'],
                ]);
            }

            return $shipment->load('shipmentItems');
        });
    }
}
