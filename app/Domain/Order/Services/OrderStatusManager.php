<?php

namespace App\Domain\Order\Services;

use App\Models\Order;

class OrderStatusManager
{
    private const TRANSITIONS = [
        'pending'    => ['confirmed', 'cancelled'],
        'confirmed'  => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped'    => ['delivered'],
        'delivered'  => ['completed'],
        'completed'  => [],
        'cancelled'  => [],
    ];

    public function canTransition(Order $order, string $toStatus): bool
    {
        $allowed = self::TRANSITIONS[$order->status] ?? [];

        return in_array($toStatus, $allowed, true);
    }

    public function allowedFrom(string $fromStatus): array
    {
        return self::TRANSITIONS[$fromStatus] ?? [];
    }

    public function allStatuses(): array
    {
        return array_keys(self::TRANSITIONS);
    }
}
