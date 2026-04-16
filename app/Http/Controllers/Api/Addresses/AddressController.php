<?php

namespace App\Http\Controllers\Api\Addresses;

use App\Domain\Addresses\Actions\CreateAddressAction;
use App\Domain\Addresses\Actions\DeleteAddressAction;
use App\Domain\Addresses\Actions\SetDefaultAddressAction;
use App\Domain\Addresses\Actions\UpdateAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Http\Resources\Api\Addresses\AddressResource;
use App\Models\Address;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddressController extends Controller
{
    public function __construct(
        private CreateAddressAction $createAction,
        private UpdateAddressAction $updateAction,
        private DeleteAddressAction $deleteAction,
        private SetDefaultAddressAction $setDefaultAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $addresses = Address::with(['shippingZone', 'shippingZoneArea'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderBy('created_at')
            ->get();

        return ApiResponse::success(AddressResource::collection($addresses));
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->createAction->execute($request->user(), $request->validated());

        return ApiResponse::created(new AddressResource($address));
    }

    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return ApiResponse::error('Address not found', [], Response::HTTP_NOT_FOUND);
        }

        $address = $this->updateAction->execute($address, $request->validated());

        return ApiResponse::success(new AddressResource($address));
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return ApiResponse::error('Address not found', [], Response::HTTP_NOT_FOUND);
        }

        $this->deleteAction->execute($address);

        return ApiResponse::success(null);
    }

    public function setDefault(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return ApiResponse::error('Address not found', [], Response::HTTP_NOT_FOUND);
        }

        $address = $this->setDefaultAction->execute($address);

        return ApiResponse::success(new AddressResource($address));
    }
}
