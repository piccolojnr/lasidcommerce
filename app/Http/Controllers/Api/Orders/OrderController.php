<?php

namespace App\Http\Controllers\Api\Orders;

use App\Domain\Order\Queries\GetCustomerOrderDetailQuery;
use App\Domain\Order\Queries\GetCustomerOrderTimelineQuery;
use App\Domain\Order\Queries\ListCustomerOrdersQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Orders\OrderDetailResource;
use App\Http\Resources\Api\Orders\OrderSummaryResource;
use App\Models\Order;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private ListCustomerOrdersQuery $listQuery,
        private GetCustomerOrderDetailQuery $detailQuery,
        private GetCustomerOrderTimelineQuery $timelineQuery,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listQuery->execute($request->user())->paginate(15);

        return ApiResponse::paginated(
            $paginator,
            OrderSummaryResource::collection($paginator),
        );
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return ApiResponse::error('Order not found.', [], 404);
        }

        $order = $this->detailQuery->execute($order);

        return ApiResponse::success(new OrderDetailResource($order));
    }

    public function timeline(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return ApiResponse::error('Order not found.', [], 404);
        }

        $events = $this->timelineQuery->execute($order);

        return ApiResponse::success($events);
    }
}
