<?php

namespace App\Policies;

use App\Models\ShippingZone;
use App\Models\User;

class ShippingZonePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage shipments');
    }

    public function view(User $user, ShippingZone $shippingZone): bool
    {
        return $user->can('manage shipments');
    }

    public function create(User $user): bool
    {
        return $user->can('manage shipments');
    }

    public function update(User $user, ShippingZone $shippingZone): bool
    {
        return $user->can('manage shipments');
    }

    public function delete(User $user, ShippingZone $shippingZone): bool
    {
        return $user->can('manage shipments');
    }
}
