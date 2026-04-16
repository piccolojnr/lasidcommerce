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
use Symfony\Component\HttpFoundation\Cookie;

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
        $redirect = $this->verifyAction->execute((string) $request->query('token'));

        // Expire any stale session cookie that has no Domain attribute (created
        // before STOREFRONT_SESSION_DOMAIN was configured). Without this,
        // browsers with old cookies can send the stale ID first in the Cookie
        // header, causing PHP to read it and overwrite the authenticated
        // Domain-scoped cookie during the next session write-back.
        $expireStale = Cookie::create(config('storefront.session_cookie'))
            ->withValue('')
            ->withExpires(1)   // Unix timestamp 1 = expired
            ->withPath('/')
            ->withDomain(null) // targets only the no-Domain variant
            ->withSecure(false)
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_LAX);

        return redirect()->away($redirect)->withCookie($expireStale);
    }
}
