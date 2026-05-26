<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\CurrentSellerResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function __construct(private readonly CurrentSellerResolver $currentSellerResolver)
    {
    }

    public function profile(Request $request): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user())
            ->load('owner:id,name,email');

        return response()->json($seller);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user())
            ->load('owner');

        $payload = $request->validate([
            'store_name' => ['sometimes', 'string', 'max:255'],
            'owner_name' => ['sometimes', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($seller, $payload): void {
            if (isset($payload['store_name'])) {
                $seller->store_name = $payload['store_name'];
                $seller->save();
            }

            if (isset($payload['owner_name'])) {
                $seller->owner->name = $payload['owner_name'];
                $seller->owner->save();
            }
        });

        return response()->json(
            $seller->fresh()->load('owner:id,name,email')
        );
    }

    public function products(Request $request): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user());

        return response()->json(
            Product::query()
                ->with('images')
                ->where('seller_id', $seller->id)
                ->latest()
                ->get()
        );
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user());

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $payload['seller_id'] = $seller->id;
        $payload['slug'] = Str::slug($payload['name']);

        $product = Product::query()->create($payload);

        return response()->json($product, 201);
    }

    public function updateProduct(Request $request, int $id): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user());

        $product = Product::query()
            ->where('seller_id', $seller->id)
            ->where('id', $id)
            ->firstOrFail();

        $payload = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        if (isset($payload['name'])) {
            $payload['slug'] = Str::slug($payload['name']);
        }

        $product->update($payload);

        return response()->json($product->fresh()->load('images'));
    }

    public function showProduct(Request $request, int $id): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user());

        $product = Product::query()
            ->with('images')
            ->where('seller_id', $seller->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($product);
    }

    public function destroyProduct(Request $request, int $id): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user());

        $product = Product::query()
            ->with('images')
            ->where('seller_id', $seller->id)
            ->where('id', $id)
            ->firstOrFail();

        foreach ($product->images as $image) {
            if ($image->source_type === 'uploaded') {
                Storage::disk('public')->delete($image->path);
            }
        }

        $product->delete();

        return response()->json([], 204);
    }

    public function uploadProductImage(Request $request, int $id): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user());

        $product = Product::query()
            ->where('seller_id', $seller->id)
            ->where('id', $id)
            ->firstOrFail();

        $payload = $request->validate([
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $hasUploadedImage = isset($payload['image']);
        $hasExternalUrl = isset($payload['image_url']);

        if (! $hasUploadedImage && ! $hasExternalUrl) {
            return response()->json([
                'message' => 'image atau image_url wajib diisi.',
            ], 422);
        }

        if ($hasUploadedImage && $hasExternalUrl) {
            return response()->json([
                'message' => 'Gunakan salah satu: image atau image_url.',
            ], 422);
        }

        $storedPath = $hasUploadedImage
            ? $payload['image']->store('products/'.$seller->id, 'public')
            : $payload['image_url'];
        $sourceType = $hasUploadedImage ? 'uploaded' : 'external';

        $existingImages = ProductImage::query()->where('product_id', $product->id)->get();
        foreach ($existingImages as $existingImage) {
            if ($existingImage->source_type === 'uploaded') {
                Storage::disk('public')->delete($existingImage->path);
            }
        }
        ProductImage::query()->where('product_id', $product->id)->delete();

        $image = ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => $storedPath,
            'source_type' => $sourceType,
            'image_type' => 'product',
            'is_primary' => true,
        ]);

        return response()->json($image, 201);
    }
}
