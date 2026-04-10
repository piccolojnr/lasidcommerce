<?php

namespace App\Domain\Payment\Queries;

use App\Models\Refund;
use Illuminate\Database\Eloquent\Builder;

class ListAdminRefundsQuery
{
    public function execute(): Builder
    {
        return Refund::query()->latest('id');
    }
}
