<?php

namespace App\Domain\Shipment\Actions;

use App\Domain\Notification\Services\CustomerNotificationService;
use App\Domain\Notification\Services\InternalNotificationService;
use App\Domain\Order\Services\OrderFulfillmentService;
use App\Domain\Shipment\Exceptions\ShipmentException;
use App\Domain\Shipment\Services\ShipmentStatusManager;
use App\Models\Shipment;
use App\Models\ShipmentStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateShipmentStatusAction
{
    public function __construct(
        private ShipmentStatusManager $statusManager,
        private OrderFulfillmentService $fulfillmentService,
        private CustomerNotificationService $notificationService,
        private InternalNotificationService $internalNotificationService,
    ) {}

    /**
     * @throws ShipmentException
     */
    public function execute(Shipment $shipment, string $toStatus, ?string $note = null, ?User $actor = null): Shipment
    {
        if (! $this->statusManager->canTransition($shipment, $toStatus)) {
            throw new ShipmentException(
                "Cannot transition shipment from '{$shipment->status}' to '{$toStatus}'."
            );
        }

        $fromStatus = $shipment->status;
        $updateData = ['status' => $toStatus];

        $tsField = $this->statusManager->timestampField($toStatus);
        if ($tsField !== null) {
            $updateData[$tsField] = now();
        }

        DB::transaction(function () use ($shipment, $updateData, $fromStatus, $toStatus, $note, $actor) {
            $shipment->update($updateData);

            ShipmentStatusHistory::create([
                'shipment_id' => $shipment->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note,
                'changed_by' => $actor?->id,
            ]);

            $this->fulfillmentService->sync($shipment->order->fresh([
                'orderItems.shipmentItems.shipment',
                'shipments',
            ]));
        });

        $updatedShipment = $shipment->fresh(['order']);

        $this->notificationService->sendShipmentStatusUpdated($updatedShipment, $fromStatus, $toStatus);
        $this->internalNotificationService->sendShipmentStatusUpdated($updatedShipment, $fromStatus, $toStatus);

        return $updatedShipment;
    }
}
