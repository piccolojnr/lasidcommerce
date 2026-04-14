<?php

namespace App\Domain\User\Queries;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ListAdminUsersQuery
{
    private ?string $search = null;
    private ?string $status = null;
    private ?string $role = null;

    public function withFilters(array $filters): static
    {
        $clone = clone $this;
        $clone->search = $filters['search'] ?? null;
        $clone->status = $filters['status'] ?? null;
        $clone->role = $filters['role'] ?? null;

        return $clone;
    }

    public function paginate(): LengthAwarePaginator
    {
        return User::query()
            ->with('roles:name')
            ->withCount(['orders', 'payments'])
            ->when($this->search, function ($query, $search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($this->status, fn ($query, $status) => $query->where('status', $status))
            ->when($this->role, function ($query, $role) {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', $role));
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }
}
