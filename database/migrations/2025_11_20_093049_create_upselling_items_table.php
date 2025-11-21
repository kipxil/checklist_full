<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('upselling_items', function (Blueprint $table) {
            $table->id();
            // Terhubung ke restoran spesifik
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');

            // Pembeda antara Food dan Beverage
            $table->enum('type', ['food', 'beverage']);

            // Nama Menu
            $table->string('name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upselling_items');
    }
};
