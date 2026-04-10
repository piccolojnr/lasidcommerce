<?php

namespace App\Domain\Payment\Queries;

use App\Models\PaymentWebhookLog;
use Illuminate\Database\Eloquent\Builder;

class ListPaymentWebhookLogsQuery
{
    public function execute(): Builder
    {
        return PaymentWebhookLog::query()->latest('id');
    }
}
