<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(): InertiaResponse
    {
        return Inertia::render('admin/users/index');
    }

    public function show(User $user): InertiaResponse
    {
        return Inertia::render('admin/users/show');
    }

    public function update(UpdateUserStatusRequest $request, User $user): Response
    {
        return response("Admin user update placeholder: {$user->getKey()}");
    }
}
