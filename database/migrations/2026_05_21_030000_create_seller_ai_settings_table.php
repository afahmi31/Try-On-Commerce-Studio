<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_ai_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_id')->unique()->constrained('sellers')->cascadeOnDelete();
            $table->string('provider_name')->default('fashn');
            $table->text('fashn_api_key')->nullable();
            $table->string('fashn_model')->nullable();
            $table->boolean('fashn_dummy_enabled')->default(false);
            $table->text('fashn_dummy_result_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_ai_settings');
    }
};

