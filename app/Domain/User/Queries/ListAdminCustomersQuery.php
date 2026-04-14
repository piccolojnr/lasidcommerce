<?php

namespace App\Domain\User\Queries;

use App\Domain\User\Services\UserSegmentService;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ListAdminCustomersQuery
{
    private ?string $search = null;
    private ?string $status = null;

    public function __construct(
        private UserSegmentService $segmentService,
    ) {}

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search = $filters['search'] ?? null;
        $clone->status = $filters['status'] ?? null;

        return $clone;
    }

    public function paginate(): LengthAwarePaginator
    {
        $query = User::query()
            ->withCount(['orders', 'payments'])
            ->when($this->search, function ($query, $search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($this->status, fn ($query, $status) => $query->where('status', $status))
            ->latest('id');

        $this->segmentService->applyCustomerScope($query);

        return $query
            ->paginate(20)
            ->withQueryString();
    }
}
