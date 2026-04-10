<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Response;

class RefundController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Refund::class);

        return response('Admin refund index placeholder');
    }

    public function show(Refund $refund): Response
    {
        $this->authorize('view', $refund);

        return response("Admin refund show placeholder: {$refund->getKey()}");
    }
}
