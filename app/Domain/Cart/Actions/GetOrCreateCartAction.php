<?php

namespace App\Domain\Cart\Actions;

use App\Models\Cart;
use App\Models\User;

class GetOrCreateCartAction
{
    public function execute(?User $user = null, ?string $sessionId = null): Cart
    {
        return new Cart([
            'user_id' => $user?->getKey(),
            'session_id' => $sessionId,
            'status' => 'active',
        ]);
    }
}
