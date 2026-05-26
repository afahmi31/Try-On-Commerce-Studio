<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tryon_sessions', function (Blueprint $table): void {
            $table->string('device_id', 64)
                ->nullable()
                ->after('user_agent');
            $table->index(['seller_id', 'device_id', 'created_at'], 'tryon_sessions_seller_device_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tryon_sessions', function (Blueprint $table): void {
            $table->dropIndex('tryon_sessions_seller_device_created_idx');
            $table->dropColumn('device_id');
        });
    }
};

