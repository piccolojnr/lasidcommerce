<?php

namespace App\Domain\Shipping\Queries;

use App\Models\WarehouseLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAdminWarehouseLocationsQuery
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
        return WarehouseLocation::query()
            ->withCount('shipments')
            ->when($this->filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_active', $this->filters) && $this->filters['is_active'] !== null, function ($query): void {
                $query->where('is_active', $this->filters['is_active']);
            })
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (WarehouseLocation $warehouse): array => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'code' => $warehouse->code,
                'country' => $warehouse->country,
                'region' => $warehouse->region,
                'city' => $warehouse->city,
                'address_line_1' => $warehouse->address_line_1,
                'address_line_2' => $warehouse->address_line_2,
                'phone' => $warehouse->phone,
                'email' => $warehouse->email,
                'is_active' => $warehouse->is_active,
                'is_default' => $warehouse->is_default,
                'shipments_count' => $warehouse->shipments_count,
                'created_at' => $warehouse->created_at?->toISOString(),
            ]);
    }
}
