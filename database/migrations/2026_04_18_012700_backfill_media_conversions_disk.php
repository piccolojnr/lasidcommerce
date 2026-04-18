<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('media')
            ->whereNull('conversions_disk')
            ->update([
                'conversions_disk' => DB::raw('disk'),
            ]);
    }

    public function down(): void
    {
        // Intentionally left blank: prior null values cannot be safely reconstructed.
    }
};
