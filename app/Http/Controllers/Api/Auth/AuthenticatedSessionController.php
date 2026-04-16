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
        // Do NOT regenerate the CSRF token here. The session already carries a
        // stable token (seeded by StartSession on first session creation and
        // rotated on login/logout via session()->regenerate()). Calling
        // regenerateToken() unconditionally marks the session as dirty and
        // causes StartSession to write the session cookie back in every
        // bootstrap response. When a stale no-domain session cookie is also
        // present in the browser, that write-back can overwrite the correct
        // authenticated cookie with the stale one.
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
