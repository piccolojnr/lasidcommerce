<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')
                ->constrained('payments')
                ->cascadeOnDelete();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->text('reason')->nullable();
            $table->string('status');
            $table->string('provider_refund_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('payment_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('provider_refund_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
