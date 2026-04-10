<?php

namespace App\Http\Controllers\Admin\Coupons;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Coupon::class, 'coupon');
    }

    public function index(): InertiaResponse
    {
        return Inertia::render('admin/coupons/index');
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/coupons/create');
    }

    public function store(StoreCouponRequest $request): Response
    {
        return response('Admin coupon store placeholder', Response::HTTP_CREATED);
    }

    public function show(Coupon $coupon): InertiaResponse
    {
        return Inertia::render('admin/coupons/index');
    }

    public function edit(Coupon $coupon): InertiaResponse
    {
        return Inertia::render('admin/coupons/edit');
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
