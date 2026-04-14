<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Domain\Shipping\Actions\CreateShippingZoneAreaAction;
use App\Domain\Shipping\Actions\DeleteShippingZoneAreaAction;
use App\Domain\Shipping\Actions\UpdateShippingZoneAreaAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingZoneAreaRequest;
use App\Http\Requests\Admin\UpdateShippingZoneAreaRequest;
use App\Models\ShippingZone;
use App\Models\ShippingZoneArea;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ShippingZoneAreaController extends Controller
{
    public function __construct(
        private CreateShippingZoneAreaAction $createAction,
        private UpdateShippingZoneAreaAction $updateAction,
        private DeleteShippingZoneAreaAction $deleteAction,
    ) {
        $this->authorizeResource(ShippingZoneArea::class, 'area');
    }

    public function index(ShippingZone $zone): RedirectResponse
    {
        $this->authorize('view', $zone);

        return redirect()->route('admin.shipping.zones.show', $zone);
    }

    public function create(ShippingZone $zone): InertiaResponse
    {
        $this->authorize('update', $zone);

        return Inertia::render('admin/shipping/areas/create', [
            'zone' => $this->formatZone($zone),
        ]);
    }

    public function store(StoreShippingZoneAreaRequest $request, ShippingZone $zone): RedirectResponse
    {
        $this->authorize('update', $zone);

        $this->createAction->execute($zone, $request->validated());

        return redirect()->route('admin.shipping.zones.show', $zone)
            ->with('success', 'Shipping zone area created successfully.');
    }

    public function show(ShippingZoneArea $area): InertiaResponse
    {
        $area->load('shippingZone');

        return Inertia::render('admin/shipping/areas/show', [
            'area' => $this->formatArea($area),
        ]);
    }

    public function edit(ShippingZoneArea $area): InertiaResponse
    {
        $area->load('shippingZone');

        return Inertia::render('admin/shipping/areas/edit', [
            'area' => $this->formatArea($area),
        ]);
    }

    public function update(UpdateShippingZoneAreaRequest $request, ShippingZoneArea $area): RedirectResponse
    {
        $this->updateAction->execute($area, $request->validated());

        return redirect()->route('admin.shipping.areas.show', $area)
            ->with('success', 'Shipping zone area updated successfully.');
    }

    public function destroy(ShippingZoneArea $area): RedirectResponse
    {
        $zone = $area->shippingZone;
        $this->deleteAction->execute($area);

        return redirect()->route('admin.shipping.zones.show', $zone)
            ->with('success', 'Shipping zone area deleted.');
    }

    private function formatZone(ShippingZone $zone): array
    {
        return [
            'id' => $zone->id,
            'name' => $zone->name,
            'code' => $zone->code,
        ];
    }

    private function formatArea(ShippingZoneArea $area): array
    {
        return [
            'id' => $area->id,
            'area_type' => $area->area_type,
            'area_name' => $area->area_name,
            'created_at' => $area->created_at?->toISOString(),
            'updated_at' => $area->updated_at?->toISOString(),
            'zone' => $this->formatZone($area->shippingZone),
        ];
    }
}
