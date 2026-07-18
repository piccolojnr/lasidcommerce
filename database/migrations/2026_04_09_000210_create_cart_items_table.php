<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')
                ->constrained('carts')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();
            $table->foreignId('product_variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->restrictOnDelete();
            $table->string('product_name_snapshot');
            $table->string('variant_name_snapshot')->nullable();
            $table->string('sku_snapshot');
            $table->bigInteger('unit_price');
            $table->integer('quantity');
            $table->bigInteger('line_total');
            $table->timestamps();

            $table->index('cart_id');
            $table->index('product_id');
            $table->index('product_variant_id');
        });

        DB::statement(
            'CREATE UNIQUE INDEX cart_items_cart_product_variant_unique '.
            'ON cart_items (cart_id, product_id, product_variant_id) '.
            'WHERE product_variant_id IS NOT NULL'
        );

        DB::statement(
            'CREATE UNIQUE INDEX cart_items_cart_product_null_variant_unique '.
            'ON cart_items (cart_id, product_id) '.
            'WHERE product_variant_id IS NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS cart_items_cart_product_null_variant_unique');
        DB::statement('DROP INDEX IF EXISTS cart_items_cart_product_variant_unique');

        Schema::dropIfExists('cart_items');
    }
};
