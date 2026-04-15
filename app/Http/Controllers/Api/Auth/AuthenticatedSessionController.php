<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticatedSessionController extends Controller
{
    public function csrfCookie(Request $request): JsonResponse
    {
        $request->session()->regenerateToken();

        return ApiResponse::success([
            'csrf_token' => $request->session()->token(),
            'csrf_cookie' => config('storefront.csrf_cookie'),
            'csrf_header' => config('storefront.csrf_header'),
        ]);
    }

    public function show(): JsonResponse
    {
        $user = auth('customer')->user();

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
        auth('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return ApiResponse::success([
            'authenticated' => false,
        ], 'Logged out successfully.');
    }
}
