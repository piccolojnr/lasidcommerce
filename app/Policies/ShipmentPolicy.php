<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage shipments');
    }

    public function view(User $user, Shipment $shipment): bool
    {
        return $user->can('manage shipments');
    }

    public function create(User $user): bool
    {
        return $user->can('manage shipments');
    }

    public function update(User $user, Shipment $shipment): bool
    {
        return $user->can('manage shipments');
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->can('manage shipments');
    }
}
