<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            $table->boolean('fashn_api_key_last_test_ok')->nullable()->after('fashn_dummy_result_url');
            $table->text('fashn_api_key_last_test_message')->nullable()->after('fashn_api_key_last_test_ok');
            $table->timestamp('fashn_api_key_last_tested_at')->nullable()->after('fashn_api_key_last_test_message');
        });
    }

    public function down(): void
    {
        Schema::table('seller_ai_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'fashn_api_key_last_test_ok',
                'fashn_api_key_last_test_message',
                'fashn_api_key_last_tested_at',
            ]);
        });
    }
};

