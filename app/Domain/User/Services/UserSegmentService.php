<?php

namespace App\Domain\User\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserSegmentService
{
    public function applyPlatformUserScope(Builder $query): Builder
    {
        return $query->where(function (Builder $nestedQuery): void {
            $nestedQuery
                ->whereHas('roles')
                ->orWhereHas('permissions');
        });
    }

    public function applyCustomerScope(Builder $query): Builder
    {
        return $query
            ->whereDoesntHave('roles')
            ->whereDoesntHave('permissions');
    }

    public function isPlatformUser(User $user): bool
    {
        return $user->roles()->exists() || $user->permissions()->exists();
    }

    public function isCustomer(User $user): bool
    {
        return ! $this->isPlatformUser($user);
    }
}
