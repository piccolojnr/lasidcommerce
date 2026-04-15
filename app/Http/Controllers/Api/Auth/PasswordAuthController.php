<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Auth\Actions\LoginCustomerWithPasswordAction;
use App\Domain\Auth\DTOs\CustomerPasswordLoginData;
use App\Domain\User\Services\UserSegmentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\CustomerPasswordLoginRequest;
use App\Http\Requests\Api\Auth\ForgotCustomerPasswordRequest;
use App\Http\Requests\Api\Auth\ResetCustomerPasswordRequest;
use App\Models\User;
use App\Support\Responses\ApiResponse;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordAuthController extends Controller
{
    public function __construct(
        private LoginCustomerWithPasswordAction $loginAction,
        private UserSegmentService $segmentService,
    ) {}

    public function login(CustomerPasswordLoginRequest $request): JsonResponse
    {
        $user = $this->loginAction->execute(CustomerPasswordLoginData::fromArray($request->validated()));

        return ApiResponse::success([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name !== '' ? $user->name : null,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'profile_completion_required' => blank($user->name),
            ],
        ], 'Logged in successfully.');
    }

    public function forgot(ForgotCustomerPasswordRequest $request): JsonResponse
    {
        $email = mb_strtolower($request->string('email')->toString());
        $user = User::where('email', $email)->first();

        if ($user !== null && $this->segmentService->isCustomer($user)) {
            Password::broker('users')->sendResetLink([
                'email' => $user->email,
            ]);
        }

        return ApiResponse::success(null, 'If the account is eligible, a password reset link has been sent.');
    }

    public function reset(ResetCustomerPasswordRequest $request): JsonResponse
    {
        $email = mb_strtolower($request->string('email')->toString());
        $user = User::where('email', $email)->first();

        if ($user === null || ! $this->segmentService->isCustomer($user)) {
            throw ValidationException::withMessages([
                'email' => ['We could not reset the password for that account.'],
            ]);
        }

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return ApiResponse::success(null, 'Password reset successfully.');
    }
}
