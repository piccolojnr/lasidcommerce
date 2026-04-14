<?php

namespace App\Domain\User\Actions;

use App\Models\User;

class AssignUserRolesAction
{
    public function execute(User $user, array $roles): User
    {
        $user->syncRoles($roles);

        return $user->fresh('roles');
    }
}
