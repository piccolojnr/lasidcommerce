<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Domain\Shipment\Actions\CreateShipmentAction;
use App\Domain\Shipment\Exceptions\ShipmentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShipmentRequest;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ShipmentController extends Controller
{
    public function __construct(
        private CreateShipmentAction $createShipmentAction,
    ) {}

    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Shipment::class);

        return Inertia::render('admin/shipments/index');
    }

    public function show(Shipment $shipment): InertiaResponse
    {
        $this->authorize('view', $shipment);

        return Inertia::render('admin/shipments/show');
    }

    public function store(StoreShipmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Shipment::class);

        $order = Order::findOrFail($request->order_id);

        try {
            $shipment = $this->createShipmentAction->execute($order, $request->validated());
        } catch (ShipmentException $e) {
            return back()->withErrors(['order_id' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.shipments.show', $shipment)
            ->with('success', 'Shipment created successfully.');
    }

    public function update(Shipment $shipment): \Illuminate\Http\Response
    {
        $this->authorize('update', $shipment);

        return response("Admin shipment update placeholder: {$shipment->getKey()}");
    }
}
