<?php

namespace App\Http\Controllers\Admin\Coupons;

use App\Domain\Coupon\Actions\CreateCouponAction;
use App\Domain\Coupon\Actions\DeleteCouponAction;
use App\Domain\Coupon\Actions\UpdateCouponAction;
use App\Domain\Coupon\Queries\ListAdminCouponsQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CouponController extends Controller
{
    public function __construct(
        private ListAdminCouponsQuery $listQuery,
        private CreateCouponAction $createAction,
        private UpdateCouponAction $updateAction,
        private DeleteCouponAction $deleteAction,
    ) {
        $this->authorizeResource(Coupon::class, 'coupon');
    }

    public function index(Request $request): InertiaResponse
    {
        $filters = [
            'search' => $request->query('search') ?: null,
            'is_active' => $request->query('is_active') !== null
                ? filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
        ];

        return Inertia::render('admin/coupons/index', [
            'coupons' => $this->listQuery->withFilters($filters)->paginate(),
            'filters' => $filters,
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('admin/coupons/create');
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    public function show(Coupon $coupon): InertiaResponse
    {
        return Inertia::render('admin/coupons/show', [
            'coupon' => $this->formatCoupon($coupon),
        ]);
    }

    public function edit(Coupon $coupon): InertiaResponse
    {
        return Inertia::render('admin/coupons/edit', [
            'coupon' => $this->formatCoupon($coupon),
        ]);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $this->updateAction->execute($coupon, $request->validated());

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $this->deleteAction->execute($coupon);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted.');
    }

    private function formatCoupon(Coupon $coupon): array
    {
        return [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'minimum_order_amount' => $coupon->minimum_order_amount,
            'maximum_discount_amount' => $coupon->maximum_discount_amount,
            'usage_limit' => $coupon->usage_limit,
            'used_count' => $coupon->used_count,
            'starts_at' => $coupon->starts_at?->toISOString(),
            'expires_at' => $coupon->expires_at?->toISOString(),
            'is_active' => $coupon->is_active,
            'is_currently_valid' => $coupon->isCurrentlyValid(),
            'created_at' => $coupon->created_at?->toISOString(),
            'updated_at' => $coupon->updated_at?->toISOString(),
        ];
    }
}
