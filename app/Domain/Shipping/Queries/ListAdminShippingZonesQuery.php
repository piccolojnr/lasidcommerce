<?php

namespace App\Domain\Shipping\Queries;

use App\Models\ShippingZone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAdminShippingZonesQuery
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
        return ShippingZone::query()
            ->withCount(['areas', 'shippingMethods', 'orders'])
            ->when($this->filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('country_code', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_active', $this->filters) && $this->filters['is_active'] !== null, function ($query): void {
                $query->where('is_active', $this->filters['is_active']);
            })
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (ShippingZone $zone): array => [
                'id' => $zone->id,
                'name' => $zone->name,
                'code' => $zone->code,
                'description' => $zone->description,
                'country_code' => $zone->country_code,
                'is_active' => $zone->is_active,
                'areas_count' => $zone->areas_count,
                'shipping_methods_count' => $zone->shipping_methods_count,
                'orders_count' => $zone->orders_count,
                'created_at' => $zone->created_at?->toISOString(),
            ]);
    }
}
