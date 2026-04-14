<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Domain\Shipping\Actions\AttachShippingMethodToZoneAction;
use App\Domain\Shipping\Actions\CreateShippingMethodAction;
use App\Domain\Shipping\Actions\DeleteShippingMethodAction;
use App\Domain\Shipping\Actions\DetachShippingMethodFromZoneAction;
use App\Domain\Shipping\Actions\UpdateShippingMethodAction;
use App\Domain\Shipping\Queries\ListAdminShippingMethodsQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingMethodRequest;
use App\Http\Requests\Admin\UpdateShippingMethodRequest;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ShippingMethodController extends Controller
{
    public function __construct(
        private ListAdminShippingMethodsQuery $listQuery,
        private CreateShippingMethodAction $createAction,
        private UpdateShippingMethodAction $updateAction,
        private DeleteShippingMethodAction $deleteAction,
        private AttachShippingMethodToZoneAction $attachAction,
        private DetachShippingMethodFromZoneAction $detachAction,
    ) {
        $this->authorizeResource(ShippingMethod::class, 'method');
    }

    public function index(Request $request): InertiaResponse
    {
        $filters = [
            'search' => $request->query('search') ?: null,
            'is_active' => $request->query('is_active') !== null
                ? filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
        ];

        return Inertia::render('admin/shipping/methods/index', [
            'methods' => $this->listQuery->withFilters($filters)->paginate(),
            'filters' => $filters,
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/shipping/methods/create');
    }

    public function store(StoreShippingMethodRequest $request): RedirectResponse
    {
        $method = $this->createAction->execute($request->validated());

        return redirect()->route('admin.shipping.methods.show', $method)
            ->with('success', 'Shipping method created successfully.');
    }

    public function show(ShippingMethod $method): InertiaResponse
    {
        $method->load('shippingZones');

        return Inertia::render('admin/shipping/methods/show', [
            'method' => $this->formatMethod($method),
        ]);
    }

    public function edit(ShippingMethod $method): InertiaResponse
    {
        $method->load('shippingZones');

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
        $this->deleteAction->execute($method);

        return redirect()->route('admin.shipping.methods.index')
            ->with('success', 'Shipping method deleted.');
    }

    public function attach(Request $request, ShippingZone $zone): RedirectResponse
    {
        $this->authorize('update', $zone);

        $validated = $request->validate([
            'shipping_method_id' => [
                'required',
                'integer',
                Rule::exists('shipping_methods', 'id')->where('is_active', true),
            ],
        ]);

        $method = ShippingMethod::query()->findOrFail($validated['shipping_method_id']);
        $this->attachAction->execute($zone, $method);

        return redirect()->route('admin.shipping.zones.show', $zone)
            ->with('success', 'Shipping method attached to zone.');
    }

    public function detach(ShippingZone $zone, ShippingMethod $method): RedirectResponse
    {
        $this->authorize('update', $zone);
        $this->detachAction->execute($zone, $method);

        return redirect()->route('admin.shipping.zones.show', $zone)
            ->with('success', 'Shipping method removed from zone.');
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
            'shipping_zones' => $method->shippingZones->map(fn (ShippingZone $zone): array => [
                'id' => $zone->id,
                'name' => $zone->name,
                'code' => $zone->code,
            ])->values()->all(),
        ];
    }
}
