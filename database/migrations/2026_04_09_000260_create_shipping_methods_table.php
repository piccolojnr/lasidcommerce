<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')
                ->constrained('shipping_zones')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('method_type');
            $table->string('price_type');
            $table->bigInteger('flat_rate_amount')->nullable();
            $table->smallInteger('min_delivery_days')->nullable();
            $table->smallInteger('max_delivery_days')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['shipping_zone_id', 'is_active']);
            $table->index('method_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
