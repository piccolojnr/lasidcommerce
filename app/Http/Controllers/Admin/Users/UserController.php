<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(): Response
    {
        return response('Admin user index placeholder');
    }

    public function show(User $user): Response
    {
        return response("Admin user show placeholder: {$user->getKey()}");
    }

    public function update(UpdateUserStatusRequest $request, User $user): Response
    {
        return response("Admin user update placeholder: {$user->getKey()}");
    }
}
