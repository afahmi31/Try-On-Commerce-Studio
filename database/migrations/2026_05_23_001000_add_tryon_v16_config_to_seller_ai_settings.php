<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            $table->string('fashn_tryon_v16_mode', 20)
                ->nullable()
                ->after('fashn_tryon_max_output_format');
            $table->unsignedTinyInteger('fashn_tryon_v16_num_samples')
                ->nullable()
                ->after('fashn_tryon_v16_mode');
            $table->string('fashn_tryon_v16_output_format', 10)
                ->nullable()
                ->after('fashn_tryon_v16_num_samples');
        });
    }

    public function down(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'fashn_tryon_v16_mode',
                'fashn_tryon_v16_num_samples',
                'fashn_tryon_v16_output_format',
            ]);
        });
    }
};
