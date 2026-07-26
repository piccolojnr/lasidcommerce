<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Domain\Storefront\Services\StorefrontAnnouncementService;
use App\Http\Controllers\Controller;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    public function __construct(private StorefrontAnnouncementService $service) {}

    public function show(): JsonResponse
    {
        return ApiResponse::success($this->service->get());
    }
}
