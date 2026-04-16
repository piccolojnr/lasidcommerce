<?php

namespace App\Domain\Auth\Actions;

use App\Domain\User\Services\UserSegmentService;
use App\Models\User;

class ResolveStorefrontCustomerFromEmailAction
{
    public function __construct(
        private UserSegmentService $segmentService,
    ) {}

    /**
     * @return array{user: User, was_created: bool}
     */
    public function execute(string $email, ?string $name = null, ?string $phone = null): array
    {
        $user = User::where('email', $email)->first();
        $wasCreated = false;

        if ($user !== null && ! $this->segmentService->isCustomer($user)) {
            throw new \RuntimeException('This account is not available on the storefront.');
        }

        if ($user === null) {
            $user = User::create([
                'name' => $name ?? '',
                'email' => $email,
                'phone' => $phone,
                'status' => 'active',
                'password' => null,
            ]);
            $user->markEmailAsVerified();
            $wasCreated = true;
        } else {
            $dirty = false;

            if (($user->name === '' || $user->name === null) && $name !== null && $name !== '') {
                $user->name = $name;
                $dirty = true;
            }

            if (($user->phone === null || $user->phone === '') && $phone !== null && $phone !== '') {
                $user->phone = $phone;
                $dirty = true;
            }

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            } elseif ($dirty) {
                $user->save();
            }
        }

        return [
            'user' => $user->fresh(),
            'was_created' => $wasCreated,
        ];
    }
}
