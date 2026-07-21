<?php

namespace App\Http\Middleware;

use App\Domain\User\Services\UserSegmentService;
use App\Support\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStorefrontCustomer
{
    public function __construct(
        private UserSegmentService $segmentService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('customer');

        if ($user === null) {
            return ApiResponse::error('Unauthenticated.', [], Response::HTTP_UNAUTHORIZED);
        }

        if (! $this->segmentService->isCustomer($user)) {
            auth('customer')->purge();

            return ApiResponse::error('This account is not available on the storefront.', [], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
