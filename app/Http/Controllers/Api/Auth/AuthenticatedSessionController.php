<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticatedSessionController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user('customer');

        return ApiResponse::success([
            'authenticated' => $user !== null,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name !== '' ? $user->name : null,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'profile_completion_required' => blank($user->name),
            ] : null,
            'email_verified' => $user?->hasVerifiedEmail() ?? false,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user('customer')?->currentAccessToken()->delete();

        return ApiResponse::success([
            'authenticated' => false,
        ], 'Logged out successfully.');
    }
}
