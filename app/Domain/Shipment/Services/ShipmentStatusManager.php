<?php

namespace App\Domain\Shipment\Services;

use App\Models\Shipment;

class ShipmentStatusManager
{
    private const TRANSITIONS = [
        'pending' => ['packed', 'cancelled'],
        'packed' => ['shipped', 'cancelled'],
        'shipped' => ['in_transit', 'delivered', 'failed'],
        'in_transit' => ['delivered', 'failed'],
        'delivered' => ['returned'],
        'failed' => [],
        'returned' => [],
        'cancelled' => [],
    ];

    private const TIMESTAMPS = [
        'packed' => 'packed_at',
        'shipped' => 'shipped_at',
        'delivered' => 'delivered_at',
        'failed' => 'failed_at',
        'returned' => 'returned_at',
    ];

    public function canTransition(Shipment $shipment, string $toStatus): bool
    {
        $allowed = self::TRANSITIONS[$shipment->status] ?? [];

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

    public function timestampField(string $status): ?string
    {
        return self::TIMESTAMPS[$status] ?? null;
    }
}
