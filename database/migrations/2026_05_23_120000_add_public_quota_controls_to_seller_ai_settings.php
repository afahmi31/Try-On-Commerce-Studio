<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            $table->unsignedInteger('public_generate_per_day')->nullable()->after('fashn_dummy_model_image_url');
            $table->unsignedInteger('public_generate_per_minute_per_ip')->nullable()->after('public_generate_per_day');
            $table->boolean('public_limit_per_ip_enabled')->default(true)->after('public_generate_per_minute_per_ip');
            $table->boolean('public_limit_per_device_enabled')->default(true)->after('public_limit_per_ip_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'public_generate_per_day',
                'public_generate_per_minute_per_ip',
                'public_limit_per_ip_enabled',
                'public_limit_per_device_enabled',
            ]);
        });
    }
};
