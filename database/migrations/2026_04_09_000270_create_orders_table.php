<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('fulfillment_status')->default('unfulfilled');
            $table->string('currency_code', 3)->default('GHS');
            $table->bigInteger('subtotal_amount')->default(0);
            $table->bigInteger('discount_amount')->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('shipping_amount')->default(0);
            $table->bigInteger('total_amount')->default(0);
            $table->foreignId('shipping_zone_id')
                ->nullable()
                ->constrained('shipping_zones')
                ->nullOnDelete();
            $table->foreignId('shipping_method_id')
                ->nullable()
                ->constrained('shipping_methods')
                ->nullOnDelete();
            $table->string('shipping_zone_name')->nullable();
            $table->string('shipping_method_name')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('coupon_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['status', 'payment_status', 'fulfillment_status']);
            $table->index('placed_at');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
