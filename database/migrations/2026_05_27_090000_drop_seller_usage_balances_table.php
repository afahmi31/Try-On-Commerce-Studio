<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('seller_usage_balances');
    }

    public function down(): void
    {
        Schema::create('seller_usage_balances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->unsignedInteger('token_balance')->default(0);
            $table->unsignedInteger('token_used')->default(0);
            $table->unsignedInteger('token_available')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamps();
        });
    }
};
