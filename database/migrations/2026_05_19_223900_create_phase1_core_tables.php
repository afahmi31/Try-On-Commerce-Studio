<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('store_name');
            $table->string('slug')->unique();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('sku')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['seller_id', 'slug']);
            $table->index(['seller_id', 'status']);
            $table->index(['seller_id', 'created_at']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('path');
            $table->string('image_type')->default('product');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('tryon_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('customer_photo_path');
            $table->string('status')->default('pending');
            $table->string('quality_mode')->default('standard');
            $table->string('provider_name')->default('fashn');
            $table->string('provider_job_id')->nullable();
            $table->string('result_path')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('token_cost')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'status']);
            $table->index(['seller_id', 'created_at']);
            $table->index('expires_at');
        });

        Schema::create('seller_usage_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->unique()->constrained('sellers')->cascadeOnDelete();
            $table->unsignedInteger('token_balance')->default(0);
            $table->unsignedInteger('token_used')->default(0);
            $table->unsignedInteger('token_available')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('payload_json')->nullable();
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('seller_usage_balances');
        Schema::dropIfExists('tryon_sessions');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('sellers');
    }
};