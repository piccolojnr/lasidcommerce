<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentWebhookLog;
use Illuminate\Http\Response;

class PaymentWebhookLogController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', PaymentWebhookLog::class);

        return response('Admin payment webhook log index placeholder');
    }

    public function show(PaymentWebhookLog $webhookLog): Response
    {
        $this->authorize('view', $webhookLog);

        return response("Admin payment webhook log show placeholder: {$webhookLog->getKey()}");
    }
}
