<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->text('ai_prompt')->nullable()->after('category');
            $table->string('ai_category', 20)->default('auto')->after('ai_prompt');
            $table->string('ai_garment_photo_type', 20)->default('auto')->after('ai_category');
            $table->boolean('ai_segmentation_free')->default(true)->after('ai_garment_photo_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'ai_prompt',
                'ai_category',
                'ai_garment_photo_type',
                'ai_segmentation_free',
            ]);
        });
    }
};
