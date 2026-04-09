<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('provider');
            $table->string('reference')->unique();
            $table->string('provider_transaction_id')->nullable();
            $table->string('status');
            $table->bigInteger('amount');
            $table->string('currency_code', 3)->default('GHS');
            $table->text('gateway_response')->nullable();
            $table->jsonb('raw_payload_json')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('user_id');
            $table->index(['provider', 'status']);
            $table->index('provider_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
