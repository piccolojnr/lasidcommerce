<?php

namespace App\Http\Controllers\Admin\Coupons;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\Response;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Coupon::class, 'coupon');
    }

    public function index(): Response
    {
        return response('Admin coupon index placeholder');
    }

    public function create(): Response
    {
        return response('Admin coupon create placeholder');
    }

    public function store(StoreCouponRequest $request): Response
    {
        return response('Admin coupon store placeholder', Response::HTTP_CREATED);
    }

    public function show(Coupon $coupon): Response
    {
        return response("Admin coupon show placeholder: {$coupon->getKey()}");
    }

    public function edit(Coupon $coupon): Response
    {
        return response("Admin coupon edit placeholder: {$coupon->getKey()}");
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): Response
    {
        return response("Admin coupon update placeholder: {$coupon->getKey()}");
    }

    public function destroy(Coupon $coupon): Response
    {
        return response()->noContent();
    }
}
