<?php

namespace App\Policies;

use App\Models\ShippingZoneArea;
use App\Models\User;

class ShippingZoneAreaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage shipments');
    }

    public function view(User $user, ShippingZoneArea $shippingZoneArea): bool
    {
        return $user->can('manage shipments');
    }

    public function create(User $user): bool
    {
        return $user->can('manage shipments');
    }

    public function update(User $user, ShippingZoneArea $shippingZoneArea): bool
    {
        return $user->can('manage shipments');
    }

    public function delete(User $user, ShippingZoneArea $shippingZoneArea): bool
    {
        return $user->can('manage shipments');
    }
}
