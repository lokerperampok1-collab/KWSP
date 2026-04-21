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
        Schema::table('investment_plans', function (Blueprint $table) {
            $table->string('tier', 20)->after('id')->default('BASIC'); // BASIC, GOLD, DIAMOND, VVIP
            $table->decimal('price', 18, 2)->after('description')->default(0.00);
            $table->decimal('target_return', 18, 2)->after('price')->default(0.00);
            
            // Handle existing columns safely if they exist
            $table->dropColumn(['min_amount', 'max_amount', 'roi_daily_percent']);
        });

        Schema::table('user_investments', function (Blueprint $table) {
            $table->decimal('target_return', 18, 2)->after('amount')->default(0.00);
            $table->dropColumn('roi_daily_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investment_plans', function (Blueprint $table) {
            $table->decimal('min_amount', 18, 2)->default(0.00);
            $table->decimal('max_amount', 18, 2)->nullable();
            $table->decimal('roi_daily_percent', 8, 4)->default(0.0000);
            $table->dropColumn(['tier', 'price', 'target_return']);
        });

        Schema::table('user_investments', function (Blueprint $table) {
            $table->decimal('roi_daily_percent', 8, 4)->default(0.0000);
            $table->dropColumn('target_return');
        });
    }
};
