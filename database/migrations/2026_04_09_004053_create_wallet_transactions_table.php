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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('currency', 8)->default('MYR');
            $table->string('type', 32); // deposit, withdraw, profit, etc
            $table->string('status', 32)->default('pending'); // pending, approved, rejected
            $table->decimal('amount', 18, 2)->default(0.00);
            $table->string('note')->nullable();
            $table->string('idempotency_key', 64)->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
