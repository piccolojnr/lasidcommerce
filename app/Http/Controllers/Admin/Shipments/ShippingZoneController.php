<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Domain\Shipping\Actions\CreateShippingZoneAction;
use App\Domain\Shipping\Actions\DeleteShippingZoneAction;
use App\Domain\Shipping\Actions\UpdateShippingZoneAction;
use App\Domain\Shipping\Queries\ListAdminShippingZonesQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingZoneRequest;
use App\Http\Requests\Admin\UpdateShippingZoneRequest;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ShippingZoneController extends Controller
{
    public function __construct(
        private ListAdminShippingZonesQuery $listQuery,
        private CreateShippingZoneAction $createAction,
        private UpdateShippingZoneAction $updateAction,
        private DeleteShippingZoneAction $deleteAction,
    ) {
        $this->authorizeResource(ShippingZone::class, 'zone');
    }

    public function index(Request $request): InertiaResponse
    {
        $filters = [
            'search' => $request->query('search') ?: null,
            'is_active' => $request->query('is_active') !== null
                ? filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
        ];

        return Inertia::render('admin/shipping/zones/index', [
            'zones' => $this->listQuery->withFilters($filters)->paginate(),
            'filters' => $filters,
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/shipping/zones/create');
    }

    public function store(StoreShippingZoneRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return redirect()->route('admin.shipping.zones.index')
            ->with('success', 'Shipping zone created successfully.');
    }

    public function show(ShippingZone $zone): InertiaResponse
    {
        $zone->loadCount(['areas', 'shippingMethods', 'orders']);
        $zone->load([
            'areas' => fn ($query) => $query->latest('created_at'),
            'shippingMethods' => fn ($query) => $query->latest('created_at'),
        ]);

        return Inertia::render('admin/shipping/zones/show', [
            'zone' => $this->formatZone($zone, true),
        ]);
    }

    public function edit(ShippingZone $zone): InertiaResponse
    {
        $zone->loadCount(['areas', 'shippingMethods', 'orders']);

        return Inertia::render('admin/shipping/zones/edit', [
            'zone' => $this->formatZone($zone),
        ]);
    }

    public function update(UpdateShippingZoneRequest $request, ShippingZone $zone): RedirectResponse
    {
        $this->updateAction->execute($zone, $request->validated());

        return redirect()->route('admin.shipping.zones.show', $zone)
            ->with('success', 'Shipping zone updated successfully.');
    }

    public function destroy(ShippingZone $zone): RedirectResponse
    {
        $this->deleteAction->execute($zone);

        return redirect()->route('admin.shipping.zones.index')
            ->with('success', 'Shipping zone deleted.');
    }

    private function formatZone(ShippingZone $zone, bool $includeRelations = false): array
    {
        $payload = [
            'id' => $zone->id,
            'name' => $zone->name,
            'code' => $zone->code,
            'description' => $zone->description,
            'country_code' => $zone->country_code,
            'is_active' => $zone->is_active,
            'areas_count' => $zone->areas_count ?? 0,
            'shipping_methods_count' => $zone->shipping_methods_count ?? 0,
            'orders_count' => $zone->orders_count ?? 0,
            'created_at' => $zone->created_at?->toISOString(),
            'updated_at' => $zone->updated_at?->toISOString(),
        ];

        if (! $includeRelations) {
            return $payload;
        }

        $payload['areas'] = $zone->areas->map(fn ($area): array => [
            'id' => $area->id,
            'area_type' => $area->area_type,
            'area_name' => $area->area_name,
            'created_at' => $area->created_at?->toISOString(),
        ])->values()->all();

        $payload['shipping_methods'] = $zone->shippingMethods->map(fn ($method): array => [
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
        ])->values()->all();

        return $payload;
    }
}
