<?php

namespace App\Policies;

use App\Models\Refund;
use App\Models\User;

class RefundPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage orders');
    }

    public function view(User $user, Refund $refund): bool
    {
        return $user->can('manage orders');
    }

    public function create(User $user): bool
    {
        return $user->can('manage orders');
    }

    public function update(User $user, Refund $refund): bool
    {
        return $user->can('manage orders');
    }

    public function delete(User $user, Refund $refund): bool
    {
        return $user->can('manage orders');
    }
}
