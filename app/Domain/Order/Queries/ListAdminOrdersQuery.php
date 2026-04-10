<?php

namespace App\Domain\Order\Queries;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class ListAdminOrdersQuery
{
    public function execute(): Builder
    {
        return Order::query()->latest('id');
    }
}
