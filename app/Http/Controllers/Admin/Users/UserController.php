<?php

namespace App\Http\Controllers\Admin\Users;

use App\Domain\User\Actions\UpdateUserStatusAction;
use App\Domain\User\Queries\ListAdminUsersQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private ListAdminUsersQuery $listQuery,
        private UpdateUserStatusAction $updateStatusAction,
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): InertiaResponse
    {
        $filters = [
            'search' => $request->query('search') ?: null,
            'status' => $request->query('status') ?: null,
            'role' => $request->query('role') ?: null,
        ];

        $users = $this->listQuery->withFilters($filters)->paginate();
        $users->getCollection()->transform(fn (User $user) => $this->formatUserSummary($user));

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => Role::query()->orderBy('name')->get(['name']),
        ]);
    }

    public function show(User $user): InertiaResponse
    {
        $user->load(['roles:name']);
        $user->loadCount(['orders', 'payments', 'addresses']);
        $user->load([
            'orders' => fn ($query) => $query->latest('id')->limit(5),
            'payments' => fn ($query) => $query->latest('id')->limit(5),
        ]);

        return Inertia::render('admin/users/show', [
            'user' => $this->formatUserDetail($user),
            'availableRoles' => Role::query()->orderBy('name')->get(['name']),
            'availableStatuses' => ['active', 'inactive'],
        ]);
    }

    public function update(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        $this->updateStatusAction->execute($user, $request->status);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', "User status updated to '{$request->status}'.");
    }

    private function formatUserSummary(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'roles' => $user->roles->pluck('name')->values()->all(),
            'orders_count' => $user->orders_count ?? 0,
            'payments_count' => $user->payments_count ?? 0,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }

    private function formatUserDetail(User $user): array
    {
        return [
            ...$this->formatUserSummary($user),
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
