<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookLog;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\ShippingZoneArea;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\WarehouseLocation;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CouponPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PaymentWebhookLogPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RefundPolicy;
use App\Policies\SettingPolicy;
use App\Policies\ShipmentPolicy;
use App\Policies\ShippingMethodPolicy;
use App\Policies\ShippingZoneAreaPolicy;
use App\Policies\ShippingZonePolicy;
use App\Policies\StockItemPolicy;
use App\Policies\StockMovementPolicy;
use App\Policies\UserPolicy;
use App\Policies\WarehouseLocationPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        if (Str::startsWith((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Shipment::class, ShipmentPolicy::class);
        Gate::policy(Coupon::class, CouponPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(WarehouseLocation::class, WarehouseLocationPolicy::class);
        Gate::policy(ShippingZone::class, ShippingZonePolicy::class);
        Gate::policy(ShippingZoneArea::class, ShippingZoneAreaPolicy::class);
        Gate::policy(ShippingMethod::class, ShippingMethodPolicy::class);
        Gate::policy(StockItem::class, StockItemPolicy::class);
        Gate::policy(StockMovement::class, StockMovementPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Refund::class, RefundPolicy::class);
        Gate::policy(PaymentWebhookLog::class, PaymentWebhookLogPolicy::class);
        Gate::define('viewAdminDashboard', fn (User $user): bool => $user->can('view admin dashboard'));

        RateLimiter::for('storefront-magic-links', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));
            $key = Str::transliterate($email.'|'.$request->ip());

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('storefront-password-logins', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));
            $key = Str::transliterate($email.'|'.$request->ip());

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('storefront-password-resets', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));
            $key = Str::transliterate($email.'|'.$request->ip());

            return Limit::perMinute(5)->by($key);
        });
    }
}
