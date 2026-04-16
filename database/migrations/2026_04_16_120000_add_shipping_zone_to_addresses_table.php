<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->foreignId('shipping_zone_id')
                ->nullable()
                ->after('is_default')
                ->constrained('shipping_zones')
                ->nullOnDelete();

            $table->foreignId('shipping_zone_area_id')
                ->nullable()
                ->after('shipping_zone_id')
                ->constrained('shipping_zone_areas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_zone_area_id');
            $table->dropConstrainedForeignId('shipping_zone_id');
        });
    }
};
