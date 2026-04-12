<?php

namespace App\Domain\Order\Actions;

use App\Domain\Order\Exceptions\InvalidOrderTransitionException;
use App\Domain\Order\Services\OrderStatusManager;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;

class UpdateOrderStatusAction
{
    public function __construct(
        private OrderStatusManager $statusManager,
    ) {}

    /**
     * @throws InvalidOrderTransitionException
     */
    public function execute(Order $order, string $toStatus, ?string $note = null, ?User $actor = null): Order
    {
        if (! $this->statusManager->canTransition($order, $toStatus)) {
            throw new InvalidOrderTransitionException(
                "Cannot transition order from '{$order->status}' to '{$toStatus}'."
            );
        }

        $fromStatus = $order->status;

        $order->update(['status' => $toStatus]);

        OrderStatusHistory::create([
            'order_id'    => $order->id,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'note'        => $note,
            'changed_by'  => $actor?->id,
        ]);

        return $order->fresh();
    }
}
