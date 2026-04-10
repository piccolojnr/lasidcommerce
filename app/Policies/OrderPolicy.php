<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage orders');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can('manage orders');
    }

    public function create(User $user): bool
    {
        return $user->can('manage orders');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('manage orders');
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can('manage orders');
    }
}
