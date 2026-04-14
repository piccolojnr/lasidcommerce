<?php

namespace App\Domain\Shipping\Queries;

use App\Models\ShippingMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAdminShippingMethodsQuery
{
    private array $filters = [];

    public function withFilters(array $filters): self
    {
        $instance = clone $this;
        $instance->filters = $filters;

        return $instance;
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return ShippingMethod::query()
            ->withCount(['shippingZones', 'orders', 'shipments'])
            ->when($this->filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('method_type', 'like', "%{$search}%")
                        ->orWhere('price_type', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_active', $this->filters) && $this->filters['is_active'] !== null, function ($query): void {
                $query->where('is_active', $this->filters['is_active']);
            })
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (ShippingMethod $method): array => [
                'id' => $method->id,
                'name' => $method->name,
                'code' => $method->code,
                'method_type' => $method->method_type,
                'price_type' => $method->price_type,
                'flat_rate_amount' => $method->flat_rate_amount,
                'min_delivery_days' => $method->min_delivery_days,
                'max_delivery_days' => $method->max_delivery_days,
                'description' => $method->description,
                'is_active' => $method->is_active,
                'shipping_zones_count' => $method->shipping_zones_count,
                'orders_count' => $method->orders_count,
                'shipments_count' => $method->shipments_count,
                'created_at' => $method->created_at?->toISOString(),
            ]);
    }
}
