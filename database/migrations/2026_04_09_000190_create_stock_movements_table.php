<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_item_id')
                ->constrained('stock_items')
                ->cascadeOnDelete();
            $table->string('type');
            $table->integer('quantity');
            $table->string('reference_type')->nullable();
            $table->bigInteger('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['stock_item_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
