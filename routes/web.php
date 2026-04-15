<?php

use App\Http\Controllers\Admin\Catalog\BrandController;
use App\Http\Controllers\Admin\Catalog\CategoryController;
use App\Http\Controllers\Admin\Catalog\ProductController;
use App\Http\Controllers\Admin\Catalog\ProductOptionTypeController;
use App\Http\Controllers\Admin\Catalog\ProductOptionValueController;
use App\Http\Controllers\Admin\Catalog\ProductVariantController;
use App\Http\Controllers\Admin\Catalog\ProductVariantOptionValueController;
use App\Http\Controllers\Admin\Coupons\CouponController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Inventory\StockAdjustmentController;
use App\Http\Controllers\Admin\Inventory\StockItemController;
use App\Http\Controllers\Admin\Inventory\StockMovementController;
use App\Http\Controllers\Admin\Orders\OrderController;
use App\Http\Controllers\Admin\Orders\OrderStatusController;
use App\Http\Controllers\Admin\Payments\PaymentController;
use App\Http\Controllers\Admin\Payments\PaymentWebhookLogController;
use App\Http\Controllers\Admin\Payments\RefundController;
use App\Http\Controllers\Admin\Settings\SettingController;
use App\Http\Controllers\Admin\Shipments\ShipmentController;
use App\Http\Controllers\Admin\Shipments\ShippingMethodController;
use App\Http\Controllers\Admin\Shipments\ShippingZoneAreaController;
use App\Http\Controllers\Admin\Shipments\ShippingZoneController;
use App\Http\Controllers\Admin\Shipments\ShipmentStatusController;
use App\Http\Controllers\Admin\Shipments\WarehouseLocationController;
use App\Http\Controllers\Admin\Users\UserController;
use App\Http\Controllers\Admin\Users\CustomerController;
use App\Http\Controllers\Admin\Users\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        Route::prefix('catalog')->name('catalog.')->group(function () {
            Route::resource('categories', CategoryController::class);
            Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
                ->name('categories.toggle-status');
            Route::resource('brands', BrandController::class);
            Route::patch('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])
                ->name('brands.toggle-status');
            Route::resource('products', ProductController::class);
            Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
                ->name('products.toggle-status');
            Route::resource('products.variants', ProductVariantController::class)->shallow();
            Route::resource('products.option-types', ProductOptionTypeController::class)->shallow();
            Route::resource('option-types.values', ProductOptionValueController::class)->shallow();
            Route::put('variants/{variant}/option-values', [ProductVariantOptionValueController::class, 'update'])
                ->name('variants.option-values.update');
        });

        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::patch('orders/{order}/status', [OrderStatusController::class, 'update'])->name('orders.status.update');
        Route::post('orders/{order}/shipments/quick', [ShipmentController::class, 'quickStore'])->name('orders.shipments.quick-store');

        Route::resource('shipments', ShipmentController::class)->only(['index', 'show', 'store', 'update']);
        Route::patch('shipments/{shipment}/status', [ShipmentStatusController::class, 'update'])->name('shipments.status.update');

        Route::prefix('shipping')->name('shipping.')->group(function () {
            Route::resource('warehouse-locations', WarehouseLocationController::class);
            Route::resource('zones', ShippingZoneController::class);
            Route::resource('zones.areas', ShippingZoneAreaController::class)->shallow();
            Route::post('zones/{zone}/methods/attach', [ShippingMethodController::class, 'attach'])
                ->name('zones.methods.attach');
            Route::delete('zones/{zone}/methods/{method}', [ShippingMethodController::class, 'detach'])
                ->name('zones.methods.detach');
            Route::resource('methods', ShippingMethodController::class);
        });

        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::resource('stock-items', StockItemController::class)->only(['index', 'show', 'update']);
            Route::post('stock-items/{stockItem}/adjustments', [StockAdjustmentController::class, 'store'])
                ->name('stock-items.adjustments.store');
            Route::resource('stock-movements', StockMovementController::class)->only(['index', 'show']);
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::resource('payments', PaymentController::class)->only(['index', 'show']);
            Route::resource('refunds', RefundController::class)->only(['index', 'show']);
            Route::resource('webhook-logs', PaymentWebhookLogController::class)->only(['index', 'show']);
        });

        Route::resource('coupons', CouponController::class);

        Route::resource('users', UserController::class)->only(['index', 'show', 'update']);
        Route::patch('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
        Route::resource('customers', CustomerController::class)->only(['index', 'show']);

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::match(['put', 'patch'], 'settings', [SettingController::class, 'update'])->name('settings.update');
    });
