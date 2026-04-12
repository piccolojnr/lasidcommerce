<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Domain\Order\Actions\UpdateOrderStatusAction;
use App\Domain\Order\Exceptions\InvalidOrderTransitionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;

class OrderStatusController extends Controller
{
    public function __construct(
        private UpdateOrderStatusAction $updateStatusAction,
    ) {}

    public function update(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        try {
            $this->updateStatusAction->execute(
                order:    $order,
                toStatus: $request->status,
                note:     $request->note,
                actor:    $request->user(),
            );
        } catch (InvalidOrderTransitionException $e) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', "Order status updated to '{$request->status}'.");
    }
}
