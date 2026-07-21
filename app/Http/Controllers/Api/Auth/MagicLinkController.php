<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Auth\Actions\RequestCustomerMagicLinkAction;
use App\Domain\Auth\Actions\VerifyCustomerMagicLinkAction;
use App\Domain\Auth\DTOs\RequestCustomerMagicLinkData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RequestCustomerMagicLinkRequest;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MagicLinkController extends Controller
{
    public function __construct(
        private RequestCustomerMagicLinkAction $requestAction,
        private VerifyCustomerMagicLinkAction $verifyAction,
    ) {}

    public function store(RequestCustomerMagicLinkRequest $request): JsonResponse
    {
        $this->requestAction->execute(RequestCustomerMagicLinkData::fromArray($request->validated()));

        return ApiResponse::success(null, 'If the account is eligible, a sign-in link has been sent.');
    }

    public function verify(Request $request): JsonResponse
    {
        $result = $this->verifyAction->execute((string) $request->query('token'));

        if (isset($result['error'])) {
            return ApiResponse::error(
                'This sign-in link has expired or is invalid.',
                ['error_code' => $result['error']],
                422,
            );
        }

        return ApiResponse::success([
            'token' => $result['token'],
            'user' => [
                'id' => $result['user']->id,
                'name' => $result['user']->name !== '' ? $result['user']->name : null,
                'email' => $result['user']->email,
                'phone' => $result['user']->phone,
                'status' => $result['user']->status,
                'email_verified_at' => $result['user']->email_verified_at?->toISOString(),
                'profile_completion_required' => blank($result['user']->name),
            ],
            'email_verified' => $result['user']->hasVerifiedEmail(),
            'redirect_to' => $result['redirect_to'],
            'was_created' => $result['was_created'],
        ]);
    }
}
