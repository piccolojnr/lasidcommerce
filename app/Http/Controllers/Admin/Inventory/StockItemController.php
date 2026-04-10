<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStockItemRequest;
use App\Models\StockItem;
use Illuminate\Http\Response;

class StockItemController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(StockItem::class, 'stockItem');
    }

    public function index(): Response
    {
        return response('Admin stock item index placeholder');
    }

    public function show(StockItem $stockItem): Response
    {
        return response("Admin stock item show placeholder: {$stockItem->getKey()}");
    }

    public function update(UpdateStockItemRequest $request, StockItem $stockItem): Response
    {
        return response("Admin stock item update placeholder: {$stockItem->getKey()}");
    }
}
