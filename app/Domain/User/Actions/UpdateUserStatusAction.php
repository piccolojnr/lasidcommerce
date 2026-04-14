<?php

namespace App\Domain\User\Actions;

use App\Models\User;

class UpdateUserStatusAction
{
    public function execute(User $user, string $status): User
    {
        $user->update(['status' => $status]);

        return $user->fresh();
    }
}
