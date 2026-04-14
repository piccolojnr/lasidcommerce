<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Domain\Shipping\Actions\CreateWarehouseLocationAction;
use App\Domain\Shipping\Actions\DeleteWarehouseLocationAction;
use App\Domain\Shipping\Actions\UpdateWarehouseLocationAction;
use App\Domain\Shipping\Queries\ListAdminWarehouseLocationsQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseLocationRequest;
use App\Http\Requests\Admin\UpdateWarehouseLocationRequest;
use App\Models\WarehouseLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class WarehouseLocationController extends Controller
{
    public function __construct(
        private ListAdminWarehouseLocationsQuery $listQuery,
        private CreateWarehouseLocationAction $createAction,
        private UpdateWarehouseLocationAction $updateAction,
        private DeleteWarehouseLocationAction $deleteAction,
    ) {
        $this->authorizeResource(WarehouseLocation::class, 'warehouseLocation');
    }

    public function index(Request $request): InertiaResponse
    {
        $filters = [
            'search' => $request->query('search') ?: null,
            'is_active' => $request->query('is_active') !== null
                ? filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
        ];

        return Inertia::render('admin/shipping/warehouses/index', [
            'warehouses' => $this->listQuery->withFilters($filters)->paginate(),
            'filters' => $filters,
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/shipping/warehouses/create');
    }

    public function store(StoreWarehouseLocationRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return redirect()->route('admin.shipping.warehouse-locations.index')
            ->with('success', 'Warehouse location created successfully.');
    }

    public function show(WarehouseLocation $warehouseLocation): InertiaResponse
    {
        $warehouseLocation->loadCount('shipments');

        return Inertia::render('admin/shipping/warehouses/show', [
            'warehouse' => $this->formatWarehouse($warehouseLocation),
        ]);
    }

    public function edit(WarehouseLocation $warehouseLocation): InertiaResponse
    {
        $warehouseLocation->loadCount('shipments');

        return Inertia::render('admin/shipping/warehouses/edit', [
            'warehouse' => $this->formatWarehouse($warehouseLocation),
        ]);
    }

    public function update(UpdateWarehouseLocationRequest $request, WarehouseLocation $warehouseLocation): RedirectResponse
    {
        $this->updateAction->execute($warehouseLocation, $request->validated());

        return redirect()->route('admin.shipping.warehouse-locations.show', $warehouseLocation)
            ->with('success', 'Warehouse location updated successfully.');
    }

    public function destroy(WarehouseLocation $warehouseLocation): RedirectResponse
    {
        $this->deleteAction->execute($warehouseLocation);

        return redirect()->route('admin.shipping.warehouse-locations.index')
            ->with('success', 'Warehouse location deleted.');
    }

    private function formatWarehouse(WarehouseLocation $warehouse): array
    {
        return [
            'id' => $warehouse->id,
            'name' => $warehouse->name,
            'code' => $warehouse->code,
            'country' => $warehouse->country,
            'region' => $warehouse->region,
            'city' => $warehouse->city,
            'address_line_1' => $warehouse->address_line_1,
            'address_line_2' => $warehouse->address_line_2,
            'phone' => $warehouse->phone,
            'email' => $warehouse->email,
            'is_active' => $warehouse->is_active,
            'is_default' => $warehouse->is_default,
            'shipments_count' => $warehouse->shipments_count ?? 0,
            'created_at' => $warehouse->created_at?->toISOString(),
            'updated_at' => $warehouse->updated_at?->toISOString(),
        ];
    }
}
