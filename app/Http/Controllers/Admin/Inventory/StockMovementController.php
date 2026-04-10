<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Response;

class StockMovementController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', StockMovement::class);

        return response('Admin stock movement index placeholder');
    }

    public function show(StockMovement $stockMovement): Response
    {
        $this->authorize('view', $stockMovement);

        return response("Admin stock movement show placeholder: {$stockMovement->getKey()}");
    }
}
