<?php

namespace App\Http\Resources\Api\Addresses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type,
            'name'           => $this->name,
            'phone'          => $this->phone,
            'country'        => $this->country,
            'region'         => $this->region,
            'city'           => $this->city,
            'district'       => $this->district,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'landmark'       => $this->landmark,
            'postal_code'    => $this->postal_code,
            'is_default'     => $this->is_default,
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }
}
