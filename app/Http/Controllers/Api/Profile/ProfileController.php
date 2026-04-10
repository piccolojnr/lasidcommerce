<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        return ApiResponse::success([], 'Profile placeholder');
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        return ApiResponse::success([], 'Profile update placeholder');
    }
}
