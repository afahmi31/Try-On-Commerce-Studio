<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Seller;
use App\Support\CurrentSellerResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SellerProductController extends Controller
{
    public function __construct(private readonly CurrentSellerResolver $currentSellerResolver)
    {
    }

    private function seller(): Seller
    {
        return $this->currentSellerResolver->resolveForUser(auth()->user());
    }

    public function index(Request $request)
    {
        $seller = $this->seller();
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', 'all'));
        $aiCategory = trim((string) $request->query('ai_category', 'all'));
        $photoType = trim((string) $request->query('photo_type', 'all'));
        $perPage = (int) $request->query('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 20;

        $products = Product::query()
            ->with('images')
            ->where('seller_id', $seller->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');

                    if (ctype_digit($search)) {
                        $nested->orWhere('id', (int) $search);
                    }
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(in_array($aiCategory, ['auto', 'tops', 'bottoms', 'one-pieces'], true), function ($query) use ($aiCategory) {
                $query->where('ai_category', $aiCategory);
            })
            ->when(in_array($photoType, ['auto', 'flat-lay', 'model'], true), function ($query) use ($photoType) {
                $query->where('ai_garment_photo_type', $photoType);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('seller.products.index', compact('seller', 'products', 'search', 'status', 'aiCategory', 'photoType', 'perPage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $seller = $this->seller();

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'ai_prompt' => ['nullable', 'string', 'max:1000'],
            'ai_category' => ['required', 'in:auto,tops,bottoms,one-pieces'],
            'ai_garment_photo_type' => ['required', 'in:auto,flat-lay,model'],
            'ai_segmentation_free' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $hasFile = isset($payload['image']);
        $hasUrl = isset($payload['image_url']);

        if (! $hasFile && ! $hasUrl) {
            return back()->withErrors(['image' => 'Isi file image atau image_url saat create produk.'])->withInput();
        }

        if ($hasFile && $hasUrl) {
            return back()->withErrors(['image' => 'Gunakan salah satu saja: image atau image_url.'])->withInput();
        }

        $product = DB::transaction(function () use ($payload, $seller, $hasFile): Product {
            $product = Product::query()->create([
                'name' => $payload['name'],
                'sku' => $payload['sku'] ?? null,
                'category' => $payload['category'] ?? null,
                'ai_prompt' => $payload['ai_prompt'] ?? null,
                'ai_category' => $payload['ai_category'] ?? 'auto',
                'ai_garment_photo_type' => $payload['ai_garment_photo_type'] ?? 'auto',
                'ai_segmentation_free' => (bool) ($payload['ai_segmentation_free'] ?? true),
                'status' => $payload['status'],
                'seller_id' => $seller->id,
                'slug' => Str::slug($payload['name']),
            ]);

            $path = $hasFile
                ? $payload['image']->store('products/'.$seller->id, 'public')
                : $payload['image_url'];

            ProductImage::query()->create([
                'product_id' => $product->id,
                'path' => $path,
                'source_type' => $hasFile ? 'uploaded' : 'external',
                'image_type' => 'product',
                'is_primary' => true,
            ]);

            return $product;
        });

        return redirect()->route('seller.products.index')->with('success', 'Produk berhasil dibuat: '.$product->name);
    }

    public function update(Request $request, int $productId): RedirectResponse
    {
        $seller = $this->seller();
        $product = Product::query()->where('seller_id', $seller->id)->findOrFail($productId);

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'ai_prompt' => ['nullable', 'string', 'max:1000'],
            'ai_category' => ['required', 'in:auto,tops,bottoms,one-pieces'],
            'ai_garment_photo_type' => ['required', 'in:auto,flat-lay,model'],
            'ai_segmentation_free' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $product->update([
            'name' => $payload['name'],
            'sku' => $payload['sku'] ?? null,
            'category' => $payload['category'] ?? null,
            'ai_prompt' => $payload['ai_prompt'] ?? null,
            'ai_category' => $payload['ai_category'] ?? 'auto',
            'ai_garment_photo_type' => $payload['ai_garment_photo_type'] ?? 'auto',
            'ai_segmentation_free' => (bool) ($payload['ai_segmentation_free'] ?? true),
            'status' => $payload['status'],
            'slug' => Str::slug($payload['name']),
        ]);

        $hasFile = isset($payload['image']);
        $hasUrl = isset($payload['image_url']);

        if ($hasFile && $hasUrl) {
            return back()->withErrors(['image' => 'Gunakan salah satu saja: image atau image_url.'])->withInput();
        }

        if ($hasFile || $hasUrl) {
            $path = $hasFile ? $payload['image']->store('products/'.$seller->id, 'public') : $payload['image_url'];
            $sourceType = $hasFile ? 'uploaded' : 'external';

            $existingImages = ProductImage::query()->where('product_id', $product->id)->get();
            foreach ($existingImages as $existingImage) {
                if ($existingImage->source_type === 'uploaded') {
                    Storage::disk('public')->delete($existingImage->path);
                }
            }
            ProductImage::query()->where('product_id', $product->id)->delete();

            ProductImage::query()->create([
                'product_id' => $product->id,
                'path' => $path,
                'source_type' => $sourceType,
                'image_type' => 'product',
                'is_primary' => true,
            ]);
        }

        return redirect()->route('seller.products.index')->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy(int $productId): RedirectResponse
    {
        $seller = $this->seller();
        $product = Product::query()->with('images')->where('seller_id', $seller->id)->findOrFail($productId);

        foreach ($product->images as $image) {
            if ($image->source_type === 'uploaded') {
                Storage::disk('public')->delete($image->path);
            }
        }

        $product->delete();

        return redirect()->route('seller.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function addImage(Request $request, int $productId): RedirectResponse
    {
        $seller = $this->seller();
        $product = Product::query()->where('seller_id', $seller->id)->findOrFail($productId);

        $payload = $request->validate([
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $hasFile = isset($payload['image']);
        $hasUrl = isset($payload['image_url']);

        if (! $hasFile && ! $hasUrl) {
            return back()->withErrors(['image' => 'Isi file image atau image_url.']);
        }

        if ($hasFile && $hasUrl) {
            return back()->withErrors(['image' => 'Gunakan salah satu saja: image atau image_url.']);
        }

        $path = $hasFile ? $payload['image']->store('products/'.$seller->id, 'public') : $payload['image_url'];
        $sourceType = $hasFile ? 'uploaded' : 'external';

        $existingImages = ProductImage::query()->where('product_id', $product->id)->get();
        foreach ($existingImages as $existingImage) {
            if ($existingImage->source_type === 'uploaded') {
                Storage::disk('public')->delete($existingImage->path);
            }
        }
        ProductImage::query()->where('product_id', $product->id)->delete();

        ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => $path,
            'source_type' => $sourceType,
            'image_type' => 'product',
            'is_primary' => true,
        ]);

        return redirect()->route('seller.products.index')->with('success', 'Gambar produk berhasil ditambahkan.');
    }
}
