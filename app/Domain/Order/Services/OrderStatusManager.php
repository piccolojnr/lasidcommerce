<?php

namespace App\Domain\Order\Services;

use App\Models\Order;

class OrderStatusManager
{
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function canTransition(Order $order, string $toStatus): bool
    {
        return in_array($toStatus, $this->allowedFrom($order), true);
    }

    public function allowedFrom(Order|string $from): array
    {
        $fromStatus = $from instanceof Order ? $from->status : $from;
        $allowed = self::TRANSITIONS[$fromStatus] ?? [];

        if ($from instanceof Order && in_array('completed', $allowed, true) && $from->fulfillment_status !== 'fulfilled') {
            $allowed = array_values(array_filter($allowed, fn (string $status) => $status !== 'completed'));
        }

        return $allowed;
    }

    public function allStatuses(): array
    {
        return array_keys(self::TRANSITIONS);
    }
}
