<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\CustomerPasswordLoginData;
use App\Domain\Cart\Actions\MergeGuestCartAction;
use App\Domain\User\Services\UserSegmentService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginCustomerWithPasswordAction
{
    public function __construct(
        private MergeGuestCartAction $mergeGuestCartAction,
        private UserSegmentService $segmentService,
    ) {}

    public function execute(CustomerPasswordLoginData $data): User
    {
        $user = User::where('email', $data->email)->first();

        if ($user === null || ! $this->segmentService->isCustomer($user) || ! is_string($user->password) || ! Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are invalid.'],
            ]);
        }

        Auth::guard('customer')->login($user);
        request()->session()->regenerate();

        $this->mergeGuestCartAction->execute($user, $data->cartToken);

        return $user;
    }
}
