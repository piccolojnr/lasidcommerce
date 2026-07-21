<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Cart\Actions\MergeGuestCartAction;
use App\Domain\Notification\Services\CustomerNotificationService;
use App\Domain\Notification\Services\InternalNotificationService;
use App\Domain\User\Services\UserSegmentService;
use App\Models\CustomerMagicLink;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class VerifyCustomerMagicLinkAction
{
    public function __construct(
        private MergeGuestCartAction $mergeGuestCartAction,
        private UserSegmentService $segmentService,
        private CustomerNotificationService $notificationService,
        private InternalNotificationService $internalNotificationService,
    ) {}

    /**
     * Verify a magic link token and return auth data.
     *
     * @return array{token: string, user: User, was_created: bool, redirect_to: string}|array{error: string}
     */
    public function execute(string $token): array
    {
        $magicLink = CustomerMagicLink::where('token_hash', hash('sha256', $token))
            ->latest()
            ->first();

        if ($magicLink === null || $magicLink->isConsumed() || $magicLink->isExpired()) {
            return ['error' => 'invalid_or_expired_link'];
        }

        $result = DB::transaction(function () use ($magicLink): array {
            $user = $magicLink->user ?? User::where('email', $magicLink->email)->first();
            $wasCreated = false;

            if ($user !== null && ! $this->segmentService->isCustomer($user)) {
                $magicLink->update(['consumed_at' => now()]);

                return ['error' => 'account_not_available'];
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

            $authToken = $user->createToken('storefront-token')->plainTextToken;

            $this->mergeGuestCartAction->execute($user, $magicLink->cart_token);

            return [
                'token' => $authToken,
                'user' => $user,
                'was_created' => $wasCreated,
                'redirect_to' => $magicLink->redirect_to ?? config('storefront.default_redirect_path', '/account'),
            ];
        });

        if (isset($result['error'])) {
            return $result;
        }

        if ($result['was_created'] && $result['user'] instanceof User) {
            $this->notificationService->sendWelcome($result['user']);
            $this->internalNotificationService->sendNewCustomer($result['user']);
        }

        return $result;
    }
}
