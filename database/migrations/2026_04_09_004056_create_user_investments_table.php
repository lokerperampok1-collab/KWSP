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
        Schema::create('user_investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('investment_plans')->onDelete('cascade');
            $table->string('plan_name', 120);
            $table->decimal('amount', 18, 2)->default(0.00);
            $table->decimal('roi_daily_percent', 8, 4)->default(0.0000);
            $table->integer('duration_days')->default(0);
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status', 20)->default('active'); // active, completed, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_investments');
    }
};
