<?php

namespace App\Http\Controllers\Webhooks\Paystack;

use App\Http\Controllers\Controller;
use App\Support\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        return ApiResponse::success([], 'Paystack webhook placeholder');
    }
}
