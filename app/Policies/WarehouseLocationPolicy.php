<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WarehouseLocation;

class WarehouseLocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage shipments');
    }

    public function view(User $user, WarehouseLocation $warehouseLocation): bool
    {
        return $user->can('manage shipments');
    }

    public function create(User $user): bool
    {
        return $user->can('manage shipments');
    }

    public function update(User $user, WarehouseLocation $warehouseLocation): bool
    {
        return $user->can('manage shipments');
    }

    public function delete(User $user, WarehouseLocation $warehouseLocation): bool
    {
        return $user->can('manage shipments');
    }
}
