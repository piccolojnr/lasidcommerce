<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'              => ['required', 'integer', 'exists:orders,id'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer', 'distinct', 'exists:order_items,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'warehouse_location_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouse_locations', 'id')->where('is_active', true),
            ],
            'carrier_name'          => ['nullable', 'string', 'max:100'],
            'tracking_number'       => ['nullable', 'string', 'max:100'],
            'tracking_url'          => ['nullable', 'url', 'max:500'],
            'notes'                 => ['nullable', 'string'],
            'rider_name'            => ['nullable', 'string', 'max:100'],
            'rider_phone'           => ['nullable', 'string', 'max:20'],
        ];
    }
}
