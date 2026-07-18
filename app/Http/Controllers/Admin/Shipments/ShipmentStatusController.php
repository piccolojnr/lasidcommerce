<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Domain\Shipment\Actions\UpdateShipmentStatusAction;
use App\Domain\Shipment\Exceptions\ShipmentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateShipmentStatusRequest;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;

class ShipmentStatusController extends Controller
{
    public function __construct(
        private UpdateShipmentStatusAction $updateShipmentStatusAction,
    ) {}

    public function update(UpdateShipmentStatusRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        try {
            $this->updateShipmentStatusAction->execute($shipment, $request->status, $request->note, $request->user());
        } catch (ShipmentException $e) {
            return redirect()
                ->route('admin.shipments.show', $shipment)
                ->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.shipments.show', $shipment)
            ->with('success', "Shipment status updated to '{$request->status}'.");
    }
}
