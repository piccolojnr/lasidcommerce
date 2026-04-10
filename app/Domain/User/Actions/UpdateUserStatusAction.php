<?php

namespace App\Domain\User\Actions;

use App\Models\User;

class UpdateUserStatusAction
{
    public function execute(User $user, string $status): User
    {
        $user->status = $status;

        return $user;
    }
}
