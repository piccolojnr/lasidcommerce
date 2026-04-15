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
        $user = auth('customer')->user();

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name !== '' ? $user->name : null,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'profile_completion_required' => blank($user->name),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user('customer');
        $validated = $request->safe()->except(['password', 'password_confirmation']);

        if (array_key_exists('name', $validated) && $validated['name'] === null) {
            $validated['name'] = '';
        }

        if (($validated['email'] ?? null) !== null && $validated['email'] !== $user->email) {
            $validated['email_verified_at'] = null;
        }

        $user->fill($validated);

        if ($request->filled('password')) {
            $user->password = $request->string('password')->toString();
        }

        $user->save();

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name !== '' ? $user->name : null,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'profile_completion_required' => blank($user->name),
        ], 'Profile updated successfully.');
    }
}
