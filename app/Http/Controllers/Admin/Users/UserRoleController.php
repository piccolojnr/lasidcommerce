<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRolesRequest;
use App\Models\User;
use Illuminate\Http\Response;

class UserRoleController extends Controller
{
    public function update(UpdateUserRolesRequest $request, User $user): Response
    {
        $this->authorize('update', $user);

        return response("Admin user role update placeholder: {$user->getKey()}");
    }
}
