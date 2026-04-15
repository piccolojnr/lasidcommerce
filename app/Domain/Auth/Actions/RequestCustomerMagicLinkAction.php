<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\RequestCustomerMagicLinkData;
use App\Domain\Auth\Services\StorefrontRedirectService;
use App\Domain\User\Services\UserSegmentService;
use App\Models\CustomerMagicLink;
use App\Models\User;
use App\Notifications\CustomerMagicLinkNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class RequestCustomerMagicLinkAction
{
    public function __construct(
        private UserSegmentService $segmentService,
        private StorefrontRedirectService $redirectService,
    ) {}

    public function execute(RequestCustomerMagicLinkData $data): void
    {
        $user = User::where('email', $data->email)->first();

        if ($user !== null && ! $this->segmentService->isCustomer($user)) {
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

        $verifyUrl = URL::temporarySignedRoute(
            'api.v1.auth.magic-link.verify',
            $magicLink->expires_at,
            ['token' => $plainToken],
        );

        Notification::route('mail', $data->email)
            ->notify(new CustomerMagicLinkNotification($verifyUrl, $magicLink->expires_at));
    }
}
