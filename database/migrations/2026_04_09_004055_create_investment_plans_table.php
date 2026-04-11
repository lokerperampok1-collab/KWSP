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
        Schema::create('investment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('description')->nullable();
            $table->decimal('min_amount', 18, 2)->default(0.00);
            $table->decimal('max_amount', 18, 2)->nullable();
            $table->decimal('roi_daily_percent', 8, 4)->default(0.0000);
            $table->integer('duration_days')->default(0);
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_plans');
    }
};
