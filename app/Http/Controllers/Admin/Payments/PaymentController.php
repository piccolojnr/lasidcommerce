<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Payment::class);

        return response('Admin payment index placeholder');
    }

    public function show(Payment $payment): Response
    {
        $this->authorize('view', $payment);

        return response("Admin payment show placeholder: {$payment->getKey()}");
    }
}
