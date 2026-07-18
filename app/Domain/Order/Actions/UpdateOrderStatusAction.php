<?php

namespace App\Domain\Order\Actions;

use App\Domain\Inventory\Services\StockReservationService;
use App\Domain\Notification\Services\CustomerNotificationService;
use App\Domain\Notification\Services\InternalNotificationService;
use App\Domain\Order\Exceptions\InvalidOrderTransitionException;
use App\Domain\Order\Services\OrderStatusManager;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatusAction
{
    public function __construct(
        private OrderStatusManager $statusManager,
        private CustomerNotificationService $notificationService,
        private InternalNotificationService $internalNotificationService,
        private StockReservationService $stockReservationService,
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

        DB::transaction(function () use ($order, $toStatus, $fromStatus, $note, $actor): void {
            if ($toStatus === 'cancelled') {
                $this->stockReservationService->releaseOrder($order);
            }

            if ($toStatus === 'completed') {
                $this->stockReservationService->commitOrder($order);
            }

            $order->update(['status' => $toStatus]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note,
                'changed_by' => $actor?->id,
            ]);
        });

        $updatedOrder = $order->fresh();

        $this->notificationService->sendOrderStatusUpdated($updatedOrder, $fromStatus, $toStatus, $note);
        $this->internalNotificationService->sendOrderStatusUpdated($updatedOrder, $fromStatus, $toStatus, $note);

        return $updatedOrder;
    }
}
