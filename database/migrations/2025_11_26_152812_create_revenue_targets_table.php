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
        Schema::create('revenue_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->integer('year');  // Contoh: 2025
            $table->integer('month'); // Contoh: 11
            $table->decimal('amount', 15, 2); // Nominal Target
            $table->timestamps();

            // Mencegah duplikasi: Satu resto hanya boleh punya 1 target per bulan/tahun
            $table->unique(['restaurant_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_targets');
    }
};
