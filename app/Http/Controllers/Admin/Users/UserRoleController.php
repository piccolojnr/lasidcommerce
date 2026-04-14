<?php

namespace App\Http\Controllers\Admin\Users;

use App\Domain\User\Actions\AssignUserRolesAction;
use App\Domain\User\Services\UserSegmentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRolesRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserRoleController extends Controller
{
    public function __construct(
        private AssignUserRolesAction $assignUserRolesAction,
        private UserSegmentService $segmentService,
    ) {}

    public function update(UpdateUserRolesRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        abort_if(! $this->segmentService->isPlatformUser($user), 404);

        $this->assignUserRolesAction->execute($user, $request->roles);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User roles updated.');
    }
}
