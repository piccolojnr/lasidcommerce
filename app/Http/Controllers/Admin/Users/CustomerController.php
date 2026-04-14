<?php

namespace App\Http\Controllers\Admin\Users;

use App\Domain\User\Queries\ListAdminCustomersQuery;
use App\Domain\User\Services\UserSegmentService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CustomerController extends Controller
{
    public function __construct(
        private ListAdminCustomersQuery $listQuery,
        private UserSegmentService $segmentService,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', User::class);

        $filters = [
            'search' => $request->query('search') ?: null,
            'status' => $request->query('status') ?: null,
        ];

        $customers = $this->listQuery->withFilters($filters)->paginate();
        $customers->getCollection()->transform(fn (User $user) => $this->formatCustomerSummary($user));

        return Inertia::render('admin/customers/index', [
            'customers' => $customers,
            'filters' => $filters,
        ]);
    }

    public function show(User $customer): InertiaResponse
    {
        $this->authorize('view', $customer);
        abort_if(! $this->segmentService->isCustomer($customer), 404);

        $customer->loadCount(['orders', 'payments', 'addresses']);
        $customer->load([
            'orders' => fn ($query) => $query->latest('id')->limit(5),
            'payments' => fn ($query) => $query->latest('id')->limit(5),
        ]);

        return Inertia::render('admin/customers/show', [
            'customer' => $this->formatCustomerDetail($customer),
        ]);
    }

    private function formatCustomerSummary(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'orders_count' => $user->orders_count ?? 0,
            'payments_count' => $user->payments_count ?? 0,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }

    private function formatCustomerDetail(User $user): array
    {
        return [
            ...$this->formatCustomerSummary($user),
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'two_factor_confirmed_at' => $user->two_factor_confirmed_at?->toISOString(),
            'addresses_count' => $user->addresses_count ?? 0,
            'recent_orders' => $user->orders
                ->map(fn ($order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'currency_code' => $order->currency_code,
                    'placed_at' => $order->placed_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'recent_payments' => $user->payments
                ->map(fn ($payment) => [
                    'id' => $payment->id,
                    'reference' => $payment->reference,
                    'provider' => $payment->provider,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'currency_code' => $payment->currency_code,
                    'paid_at' => $payment->paid_at?->toISOString(),
                ])
                ->values()
                ->all(),
        ];
    }
}
