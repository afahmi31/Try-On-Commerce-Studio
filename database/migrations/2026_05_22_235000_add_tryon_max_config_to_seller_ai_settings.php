<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            $table->string('fashn_tryon_max_generation_mode', 20)
                ->nullable()
                ->after('fashn_model');
            $table->string('fashn_tryon_max_resolution', 10)
                ->nullable()
                ->after('fashn_tryon_max_generation_mode');
            $table->string('fashn_tryon_max_output_format', 10)
                ->nullable()
                ->after('fashn_tryon_max_resolution');
        });
    }

    public function down(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'fashn_tryon_max_generation_mode',
                'fashn_tryon_max_resolution',
                'fashn_tryon_max_output_format',
            ]);
        });
    }
};
