<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\RequestCustomerMagicLinkData;
use App\Domain\Auth\Services\StorefrontRedirectService;
use App\Domain\Notification\Services\NotificationPreferenceService;
use App\Domain\User\Services\UserSegmentService;
use App\Models\CustomerMagicLink;
use App\Models\User;
use App\Notifications\CustomerMagicLinkNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class RequestCustomerMagicLinkAction
{
    public function __construct(
        private UserSegmentService $segmentService,
        private StorefrontRedirectService $redirectService,
        private NotificationPreferenceService $preferenceService,
    ) {}

    public function execute(RequestCustomerMagicLinkData $data): void
    {
        $user = User::where('email', $data->email)->first();

        if ($user !== null && ! $this->segmentService->isCustomer($user)) {
            return;
        }

        if ($user !== null && ! $this->preferenceService->allows($user, 'auth_magic_link')) {
            return;
        }

        $plainToken = Str::random(64);

        $magicLink = CustomerMagicLink::create([
            'user_id' => $user?->id,
            'email' => $data->email,
            'token_hash' => hash('sha256', $plainToken),
            'cart_token' => $data->cartToken,
            'redirect_to' => $this->redirectService->sanitizePath($data->redirectTo),
            'expires_at' => now()->addMinutes(config('storefront.magic_link_expire_minutes')),
        ]);

        $verifyUrl = config('storefront.url')
            .'/auth/verify?token='.urlencode($plainToken)
            .($magicLink->redirect_to ? '&redirect='.urlencode($magicLink->redirect_to) : '');

        Notification::route('mail', $data->email)
            ->notify(new CustomerMagicLinkNotification($verifyUrl, $magicLink->expires_at));
    }
}
