<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Services\StorefrontRedirectService;
use App\Domain\Cart\Actions\MergeGuestCartAction;
use App\Domain\Notification\Services\CustomerNotificationService;
use App\Domain\User\Services\UserSegmentService;
use App\Models\CustomerMagicLink;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerifyCustomerMagicLinkAction
{
    public function __construct(
        private MergeGuestCartAction $mergeGuestCartAction,
        private UserSegmentService $segmentService,
        private StorefrontRedirectService $redirectService,
        private CustomerNotificationService $notificationService,
    ) {}

    public function execute(string $token): string
    {
        $magicLink = CustomerMagicLink::where('token_hash', hash('sha256', $token))
            ->latest()
            ->first();

        if ($magicLink === null || $magicLink->isConsumed() || $magicLink->isExpired()) {
            return $this->redirectService->toFailureUrl('invalid_or_expired_link');
        }

        $result = DB::transaction(function () use ($magicLink): array {
            $user = $magicLink->user ?? User::where('email', $magicLink->email)->first();
            $wasCreated = false;

            if ($user !== null && ! $this->segmentService->isCustomer($user)) {
                $magicLink->update(['consumed_at' => now()]);

                return [
                    'redirect' => $this->redirectService->toFailureUrl('account_not_available'),
                    'user' => null,
                    'was_created' => false,
                ];
            }

            if ($user === null) {
                $user = User::create([
                    'name' => '',
                    'email' => $magicLink->email,
                    'status' => 'active',
                    'password' => null,
                ]);
                $wasCreated = true;
            }

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $magicLink->update([
                'user_id' => $user->id,
                'consumed_at' => now(),
            ]);

            Auth::guard('customer')->login($user);
            request()->session()->regenerate();

            $this->mergeGuestCartAction->execute($user, $magicLink->cart_token);

            return [
                'redirect' => $this->redirectService->toSuccessUrl($magicLink->redirect_to),
                'user' => $user,
                'was_created' => $wasCreated,
            ];
        });

        if ($result['was_created'] && $result['user'] instanceof User) {
            $this->notificationService->sendWelcome($result['user']);
        }

        return $result['redirect'];
    }
}
