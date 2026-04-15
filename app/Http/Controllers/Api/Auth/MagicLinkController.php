<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Auth\Actions\RequestCustomerMagicLinkAction;
use App\Domain\Auth\Actions\VerifyCustomerMagicLinkAction;
use App\Domain\Auth\DTOs\RequestCustomerMagicLinkData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RequestCustomerMagicLinkRequest;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    public function verify(Request $request): RedirectResponse
    {
        return redirect()->away(
            $this->verifyAction->execute((string) $request->query('token')),
        );
    }
}
