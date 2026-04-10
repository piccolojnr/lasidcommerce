<?php

namespace App\Domain\Order\Queries;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ListCustomerOrdersQuery
{
    public function execute(User $user): Builder
    {
        return Order::query()->where('user_id', $user->getKey())->latest('id');
    }
}
