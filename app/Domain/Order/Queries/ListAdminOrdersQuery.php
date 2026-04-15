<?php

namespace App\Domain\Order\Queries;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class ListAdminOrdersQuery
{
    private ?string $search = null;
    private ?string $status = null;
    private ?string $paymentStatus = null;
    private ?string $fulfillmentStatus = null;

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search = $filters['search'] ?? null;
        $clone->status = $filters['status'] ?? null;
        $clone->paymentStatus = $filters['payment_status'] ?? null;
        $clone->fulfillmentStatus = $filters['fulfillment_status'] ?? null;

        return $clone;
    }

    public function paginate(): LengthAwarePaginator
    {
        return Order::query()
            ->with([
                'shipments',
                'orderItems.shipmentItems.shipment',
            ])
            ->when($this->search, function ($query, $search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($this->status, fn ($query, $status) => $query->where('status', $status))
            ->when(
                $this->paymentStatus,
                fn ($query, $paymentStatus) => $query->where('payment_status', $paymentStatus),
            )
            ->when(
                $this->fulfillmentStatus,
                fn ($query, $fulfillmentStatus) => $query->where('fulfillment_status', $fulfillmentStatus),
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }
}
