<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tryon_sessions', function (Blueprint $table): void {
            $table->string('source_channel')->nullable()->after('quality_mode');
            $table->string('ip_address', 45)->nullable()->after('source_channel');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->index(['seller_id', 'source_channel']);
        });
    }

    public function down(): void
    {
        Schema::table('tryon_sessions', function (Blueprint $table): void {
            $table->dropIndex('tryon_sessions_seller_id_source_channel_index');
            $table->dropColumn(['source_channel', 'ip_address', 'user_agent']);
        });
    }
};