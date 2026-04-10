<?php

namespace App\Http\Controllers\Admin\Shipments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseLocationRequest;
use App\Http\Requests\Admin\UpdateWarehouseLocationRequest;
use App\Models\WarehouseLocation;
use Illuminate\Http\Response;

class WarehouseLocationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(WarehouseLocation::class, 'warehouseLocation');
    }

    public function index(): Response
    {
        return response('Admin warehouse location index placeholder');
    }

    public function create(): Response
    {
        return response('Admin warehouse location create placeholder');
    }

    public function store(StoreWarehouseLocationRequest $request): Response
    {
        return response('Admin warehouse location store placeholder', Response::HTTP_CREATED);
    }

    public function show(WarehouseLocation $warehouseLocation): Response
    {
        return response("Admin warehouse location show placeholder: {$warehouseLocation->getKey()}");
    }

    public function edit(WarehouseLocation $warehouseLocation): Response
    {
        return response("Admin warehouse location edit placeholder: {$warehouseLocation->getKey()}");
    }

    public function update(UpdateWarehouseLocationRequest $request, WarehouseLocation $warehouseLocation): Response
    {
        return response("Admin warehouse location update placeholder: {$warehouseLocation->getKey()}");
    }

    public function destroy(WarehouseLocation $warehouseLocation): Response
    {
        return response()->noContent();
    }
}
