<?php

namespace App\Http\Resources\Api\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentTrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'status'         => $this->status,
            'carrier_name'   => $this->carrier_name,
            'tracking_number' => $this->tracking_number,
            'tracking_url'   => $this->tracking_url,
            'rider_name'     => $this->rider_name,
            'rider_phone'    => $this->rider_phone,
            'packed_at'      => $this->packed_at?->toISOString(),
            'shipped_at'     => $this->shipped_at?->toISOString(),
            'delivered_at'   => $this->delivered_at?->toISOString(),
            'failed_at'      => $this->failed_at?->toISOString(),
            'returned_at'    => $this->returned_at?->toISOString(),
        ];
    }
}
