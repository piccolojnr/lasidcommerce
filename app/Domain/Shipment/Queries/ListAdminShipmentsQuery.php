<?php

namespace App\Domain\Shipment\Queries;

use App\Models\Shipment;
use Illuminate\Pagination\LengthAwarePaginator;

class ListAdminShipmentsQuery
{
    private ?string $search = null;

    private ?string $status = null;

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search = $filters['search'] ?? null;
        $clone->status = $filters['status'] ?? null;

        return $clone;
    }

    public function paginate(): LengthAwarePaginator
    {
        return Shipment::query()
            ->with('order:id,order_number,email')
            ->when($this->search, function ($query, $search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('tracking_number', 'like', "%{$search}%")
                        ->orWhere('carrier_name', 'like', "%{$search}%")
                        ->orWhereHas('order', function ($orderQuery) use ($search) {
                            $orderQuery
                                ->where('order_number', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($this->status, fn ($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }
}
