<?php

namespace App\Policies;

use App\Models\StockItem;
use App\Models\User;

class StockItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage products');
    }

    public function view(User $user, StockItem $stockItem): bool
    {
        return $user->can('manage products');
    }

    public function create(User $user): bool
    {
        return $user->can('manage products');
    }

    public function update(User $user, StockItem $stockItem): bool
    {
        return $user->can('manage products');
    }

    public function delete(User $user, StockItem $stockItem): bool
    {
        return $user->can('manage products');
    }
}
