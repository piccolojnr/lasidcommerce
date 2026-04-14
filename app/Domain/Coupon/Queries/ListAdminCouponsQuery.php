<?php

namespace App\Domain\Coupon\Queries;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAdminCouponsQuery
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
        return Coupon::query()
            ->when($this->filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_active', $this->filters) && $this->filters['is_active'] !== null, function ($query) {
                $query->where('is_active', $this->filters['is_active']);
            })
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Coupon $coupon): array => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'minimum_order_amount' => $coupon->minimum_order_amount,
                'maximum_discount_amount' => $coupon->maximum_discount_amount,
                'usage_limit' => $coupon->usage_limit,
                'used_count' => $coupon->used_count,
                'starts_at' => $coupon->starts_at?->toISOString(),
                'expires_at' => $coupon->expires_at?->toISOString(),
                'is_active' => $coupon->is_active,
                'is_currently_valid' => $coupon->isCurrentlyValid(),
                'created_at' => $coupon->created_at?->toISOString(),
            ]);
    }
}
