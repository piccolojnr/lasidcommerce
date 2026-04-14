<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Domain\Shipping\Actions\CreateShippingMethodAction;
use App\Domain\Shipping\Actions\DeleteShippingMethodAction;
use App\Domain\Shipping\Actions\UpdateShippingMethodAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingMethodRequest;
use App\Http\Requests\Admin\UpdateShippingMethodRequest;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ShippingMethodController extends Controller
{
    public function __construct(
        private CreateShippingMethodAction $createAction,
        private UpdateShippingMethodAction $updateAction,
        private DeleteShippingMethodAction $deleteAction,
    ) {
        $this->authorizeResource(ShippingMethod::class, 'method');
    }

    public function index(ShippingZone $zone): RedirectResponse
    {
        $this->authorize('view', $zone);

        return redirect()->route('admin.shipping.zones.show', $zone);
    }

    public function create(ShippingZone $zone): InertiaResponse
    {
        $this->authorize('update', $zone);

        return Inertia::render('admin/shipping/methods/create', [
            'zone' => $this->formatZone($zone),
        ]);
    }

    public function store(StoreShippingMethodRequest $request, ShippingZone $zone): RedirectResponse
    {
        $this->authorize('update', $zone);

        $this->createAction->execute($zone, $request->validated());

        return redirect()->route('admin.shipping.zones.show', $zone)
            ->with('success', 'Shipping method created successfully.');
    }

    public function show(ShippingMethod $method): InertiaResponse
    {
        $method->load('shippingZone');

        return Inertia::render('admin/shipping/methods/show', [
            'method' => $this->formatMethod($method),
        ]);
    }

    public function edit(ShippingMethod $method): InertiaResponse
    {
        $method->load('shippingZone');

        return Inertia::render('admin/shipping/methods/edit', [
            'method' => $this->formatMethod($method),
        ]);
    }

    public function update(UpdateShippingMethodRequest $request, ShippingMethod $method): RedirectResponse
    {
        $this->updateAction->execute($method, $request->validated());

        return redirect()->route('admin.shipping.methods.show', $method)
            ->with('success', 'Shipping method updated successfully.');
    }

    public function destroy(ShippingMethod $method): RedirectResponse
    {
        $zone = $method->shippingZone;
        $this->deleteAction->execute($method);

        return redirect()->route('admin.shipping.zones.show', $zone)
            ->with('success', 'Shipping method deleted.');
    }

    private function formatZone(ShippingZone $zone): array
    {
        return [
            'id' => $zone->id,
            'name' => $zone->name,
            'code' => $zone->code,
        ];
    }

    private function formatMethod(ShippingMethod $method): array
    {
        return [
            'id' => $method->id,
            'name' => $method->name,
            'code' => $method->code,
            'method_type' => $method->method_type,
            'price_type' => $method->price_type,
            'flat_rate_amount' => $method->flat_rate_amount,
            'min_delivery_days' => $method->min_delivery_days,
            'max_delivery_days' => $method->max_delivery_days,
            'description' => $method->description,
            'is_active' => $method->is_active,
            'created_at' => $method->created_at?->toISOString(),
            'updated_at' => $method->updated_at?->toISOString(),
            'zone' => $this->formatZone($method->shippingZone),
        ];
    }
}
