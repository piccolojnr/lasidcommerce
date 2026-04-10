<?php

namespace App\Domain\User\Actions;

use App\Models\User;

class UpdateProfileAction
{
    public function execute(User $user, array $attributes): User
    {
        $user->fill($attributes);

        return $user;
    }
}
