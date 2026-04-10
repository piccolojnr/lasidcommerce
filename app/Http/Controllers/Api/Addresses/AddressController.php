<?php

namespace App\Http\Controllers\Api\Addresses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Models\Address;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success([], 'Address listing placeholder');
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        return ApiResponse::created([], 'Address store placeholder');
    }

    public function show(Address $address): JsonResponse
    {
        return ApiResponse::success([
            'id' => $address->getKey(),
        ], 'Address detail placeholder');
    }

    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        return ApiResponse::success([
            'id' => $address->getKey(),
        ], 'Address update placeholder');
    }

    public function destroy(Address $address): JsonResponse
    {
        return ApiResponse::success([
            'id' => $address->getKey(),
        ], 'Address delete placeholder');
    }
}
