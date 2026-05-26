<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SellerApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedSellerUser(): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SELLER,
        ]);

        $seller = Seller::query()->create([
            'owner_user_id' => $user->id,
            'store_name' => 'Seller One',
            'slug' => 'seller-one',
            'status' => 'active',
        ]);

        return [$user, $seller];
    }

    public function test_seller_can_get_and_update_profile(): void
    {
        [$user] = $this->seedSellerUser();
        Sanctum::actingAs($user);

        $this->getJson('/api/seller/profile')->assertOk()->assertJsonPath('store_name', 'Seller One');

        $this->patchJson('/api/seller/profile', [
            'store_name' => 'Seller Updated',
            'owner_name' => 'Owner Updated',
        ])->assertOk()->assertJsonPath('store_name', 'Seller Updated');

        $this->assertDatabaseHas('sellers', ['store_name' => 'Seller Updated']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Owner Updated']);
    }

    public function test_seller_can_show_and_delete_own_product(): void
    {
        [$user, $seller] = $this->seedSellerUser();
        Sanctum::actingAs($user);

        $product = Product::query()->create([
            'seller_id' => $seller->id,
            'name' => 'Kaos Test',
            'slug' => 'kaos-test',
            'status' => 'active',
        ]);

        $this->getJson('/api/seller/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('id', $product->id);

        $this->deleteJson('/api/seller/products/'.$product->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_seller_can_upload_product_image_and_set_primary_flag(): void
    {
        Storage::fake('public');

        [$user, $seller] = $this->seedSellerUser();
        Sanctum::actingAs($user);

        $product = Product::query()->create([
            'seller_id' => $seller->id,
            'name' => 'Jaket Test',
            'slug' => 'jaket-test',
            'status' => 'active',
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => 'products/'.$seller->id.'/old-image.jpg',
            'source_type' => 'uploaded',
            'image_type' => 'product',
            'is_primary' => true,
        ]);

        $response = $this->postJson('/api/seller/products/'.$product->id.'/images', [
            'image' => UploadedFile::fake()->image('new-product.webp'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('product_id', $product->id);

        $storedPath = $response->json('path');

        Storage::disk('public')->assertExists($storedPath);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'path' => $storedPath,
            'source_type' => 'uploaded',
            'is_primary' => true,
        ]);

        $this->assertDatabaseMissing('product_images', [
            'product_id' => $product->id,
            'path' => 'products/'.$seller->id.'/old-image.jpg',
        ]);
        Storage::disk('public')->assertMissing('products/'.$seller->id.'/old-image.jpg');
    }

    public function test_seller_can_attach_external_product_image_url_without_local_storage(): void
    {
        Storage::fake('public');

        [$user, $seller] = $this->seedSellerUser();
        Sanctum::actingAs($user);

        $product = Product::query()->create([
            'seller_id' => $seller->id,
            'name' => 'Dress Test',
            'slug' => 'dress-test',
            'status' => 'active',
        ]);

        $externalUrl = 'https://cdn.example.com/products/dress-test-main.jpg';

        $response = $this->postJson('/api/seller/products/'.$product->id.'/images', [
            'image_url' => $externalUrl,
        ]);

        $response->assertCreated()
            ->assertJsonPath('path', $externalUrl)
            ->assertJsonPath('source_type', 'external')
            ->assertJsonPath('image_url', $externalUrl);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'path' => $externalUrl,
            'source_type' => 'external',
            'is_primary' => true,
        ]);

        Storage::disk('public')->assertDirectoryEmpty('products/'.$seller->id);
    }
}
