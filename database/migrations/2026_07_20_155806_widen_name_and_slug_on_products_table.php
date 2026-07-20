<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widen products.name and products.slug from varchar(255) to varchar(512).
 *
 * Demo catalog products scraped from third-party sources frequently have very
 * long titles (250–350 characters). varchar(255) is too narrow to hold them,
 * so we expand both columns to 512 characters.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name', 512)->change();
            $table->string('slug', 512)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('slug', 255)->change();
        });
    }
};
