<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zone_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')
                ->constrained('shipping_zones')
                ->cascadeOnDelete();
            $table->string('area_type');
            $table->string('area_name');
            $table->timestamps();

            $table->index('shipping_zone_id');
            $table->index(['area_type', 'area_name']);
            $table->unique(['shipping_zone_id', 'area_type', 'area_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zone_areas');
    }
};
