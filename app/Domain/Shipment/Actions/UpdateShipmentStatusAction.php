<?php

namespace App\Domain\Shipment\Actions;

use App\Domain\Shipment\Exceptions\ShipmentException;
use App\Domain\Shipment\Services\ShipmentStatusManager;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class UpdateShipmentStatusAction
{
    public function __construct(
        private ShipmentStatusManager $statusManager,
    ) {}

    /**
     * @throws ShipmentException
     */
    public function execute(Shipment $shipment, string $toStatus): Shipment
    {
        if (! $this->statusManager->canTransition($shipment, $toStatus)) {
            throw new ShipmentException(
                "Cannot transition shipment from '{$shipment->status}' to '{$toStatus}'."
            );
        }

        $updateData = ['status' => $toStatus];

        $tsField = $this->statusManager->timestampField($toStatus);
        if ($tsField !== null) {
            $updateData[$tsField] = now();
        }

        DB::transaction(function () use ($shipment, $updateData, $toStatus) {
            $shipment->update($updateData);

            if ($toStatus === 'delivered') {
                $shipment->order->update(['fulfillment_status' => 'fulfilled']);
            }
        });

        return $shipment->fresh();
    }
}
