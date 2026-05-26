<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            $table->text('fashn_dummy_model_image_url')
                ->nullable()
                ->after('fashn_dummy_result_url');
        });
    }

    public function down(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            $table->dropColumn('fashn_dummy_model_image_url');
        });
    }
};
