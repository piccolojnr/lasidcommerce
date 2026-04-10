<?php

namespace App\Domain\Payment\Queries;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class ListAdminPaymentsQuery
{
    public function execute(): Builder
    {
        return Payment::query()->latest('id');
    }
}
