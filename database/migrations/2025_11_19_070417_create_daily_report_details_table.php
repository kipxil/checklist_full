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
        Schema::create('daily_report_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->onDelete('cascade');

            $table->enum('session_type', ['breakfast', 'lunch', 'dinner']);

            // 1. Revenue (Decimal, Nullable untuk draft)
            $table->decimal('revenue_food', 15, 2)->nullable();
            $table->decimal('revenue_beverage', 15, 2)->nullable();
            $table->decimal('revenue_others', 15, 2)->nullable();
            $table->decimal('revenue_event', 15, 2)->nullable();

            // 2. Data Dinamis (JSON)
            $table->json('cover_data')->nullable();       // Struktur beda tiap resto
            $table->json('upselling_data')->nullable();   // Food & Bev upselling
            $table->json('competitor_data')->nullable();  // Comparation
            $table->json('additional_data')->nullable();  // Cadangan (misal: Package Chamas)

            // 3. Text Data
            $table->string('thematic')->nullable();
            $table->text('staff_on_duty')->nullable();
            $table->text('remarks')->nullable();
            $table->text('vip_remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_report_details');
    }
};
