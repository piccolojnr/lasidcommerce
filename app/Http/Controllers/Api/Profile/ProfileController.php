<?php

namespace App\Http\Controllers\Api\Profile;

use App\Domain\Notification\Services\NotificationPreferenceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(
        private NotificationPreferenceService $preferenceService,
    ) {}

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
            'notification_preferences' => $this->preferenceService->resolveForUser($user),
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

        if (array_key_exists('notification_preferences', $validated)) {
            $validated['notification_preferences'] = $this->preferenceService->merge(
                $user,
                $validated['notification_preferences'],
            );
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
            'notification_preferences' => $this->preferenceService->resolveForUser($user),
        ], 'Profile updated successfully.');
    }
}
