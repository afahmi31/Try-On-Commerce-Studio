<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('seller_ai_settings', 'public_generate_per_minute_per_ip')) {
                $table->dropColumn('public_generate_per_minute_per_ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('seller_ai_settings', 'public_generate_per_minute_per_ip')) {
                $table->unsignedInteger('public_generate_per_minute_per_ip')->nullable()->after('public_generate_per_day');
            }
        });
    }
};
