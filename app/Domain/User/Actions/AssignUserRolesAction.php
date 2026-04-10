<?php

namespace App\Domain\User\Actions;

use App\Models\User;

class AssignUserRolesAction
{
    public function execute(User $user, array $roles): User
    {
        return $user;
    }
}
