<?php

namespace App\Policies;

use App\Models\PaymentWebhookLog;
use App\Models\User;

class PaymentWebhookLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage orders');
    }

    public function view(User $user, PaymentWebhookLog $paymentWebhookLog): bool
    {
        return $user->can('manage orders');
    }

    public function create(User $user): bool
    {
        return $user->can('manage orders');
    }

    public function update(User $user, PaymentWebhookLog $paymentWebhookLog): bool
    {
        return $user->can('manage orders');
    }

    public function delete(User $user, PaymentWebhookLog $paymentWebhookLog): bool
    {
        return $user->can('manage orders');
    }
}
