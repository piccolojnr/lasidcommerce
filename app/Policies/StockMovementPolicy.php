<?php

namespace App\Policies;

use App\Models\StockMovement;
use App\Models\User;

class StockMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage products');
    }

    public function view(User $user, StockMovement $stockMovement): bool
    {
        return $user->can('manage products');
    }

    public function create(User $user): bool
    {
        return $user->can('manage products');
    }

    public function update(User $user, StockMovement $stockMovement): bool
    {
        return $user->can('manage products');
    }

    public function delete(User $user, StockMovement $stockMovement): bool
    {
        return $user->can('manage products');
    }
}
