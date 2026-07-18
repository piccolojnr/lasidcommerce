<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_method_shipping_zone', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipping_method_id')
                ->constrained('shipping_methods')
                ->cascadeOnDelete();
            $table->foreignId('shipping_zone_id')
                ->constrained('shipping_zones')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shipping_method_id', 'shipping_zone_id']);
        });

        DB::table('shipping_methods')
            ->select(['id', 'shipping_zone_id'])
            ->whereNotNull('shipping_zone_id')
            ->orderBy('id')
            ->chunkById(100, function ($methods): void {
                $now = now();
                $rows = [];

                foreach ($methods as $method) {
                    $rows[] = [
                        'shipping_method_id' => $method->id,
                        'shipping_zone_id' => $method->shipping_zone_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('shipping_method_shipping_zone')->insertOrIgnore($rows);
                }
            });

        Schema::table('shipping_methods', function (Blueprint $table): void {
            $table->dropIndex('shipping_methods_shipping_zone_id_is_active_index');
            $table->dropConstrainedForeignId('shipping_zone_id');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table): void {
            $table->foreignId('shipping_zone_id')
                ->nullable()
                ->after('id')
                ->constrained('shipping_zones')
                ->nullOnDelete();
            $table->index(['shipping_zone_id', 'is_active']);
        });

        $assignments = DB::table('shipping_method_shipping_zone')
            ->selectRaw('MIN(shipping_zone_id) as shipping_zone_id, shipping_method_id')
            ->groupBy('shipping_method_id')
            ->get();

        foreach ($assignments as $assignment) {
            DB::table('shipping_methods')
                ->where('id', $assignment->shipping_method_id)
                ->update(['shipping_zone_id' => $assignment->shipping_zone_id]);
        }

        Schema::dropIfExists('shipping_method_shipping_zone');
    }
};
