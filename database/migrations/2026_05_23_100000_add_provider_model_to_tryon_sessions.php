<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tryon_sessions', function (Blueprint $table): void {
            $table->string('provider_model')
                ->nullable()
                ->after('provider_name');
        });
    }

    public function down(): void
    {
        Schema::table('tryon_sessions', function (Blueprint $table): void {
            $table->dropColumn('provider_model');
        });
    }
};
