<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;

class SellerPublicController extends Controller
{
    public function index(Request $request, string $seller_slug, ?string $product_ref = null)
    {
        $seller = Seller::query()
            ->where('slug', $seller_slug)
            ->where('status', 'active')
            ->with('aiSetting')
            ->firstOrFail();

        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));
        $sort = trim((string) $request->query('sort', 'latest'));

        $productsQuery = $seller->products()
            ->with('images')
            ->where('status', 'active');

        if ($search !== '') {
            $productsQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%');
            });
        }

        if ($category !== '') {
            $productsQuery->where('category', $category);
        }

        if ($sort === 'name_asc') {
            $productsQuery->orderBy('name');
        } elseif ($sort === 'name_desc') {
            $productsQuery->orderByDesc('name');
        } else {
            $sort = 'latest';
            $productsQuery->latest();
        }

        $products = $productsQuery
            ->paginate(12)
            ->withQueryString();

        $categories = Product::query()
            ->where('seller_id', $seller->id)
            ->where('status', 'active')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $selectedProduct = null;

        if ($product_ref !== null) {
            $selectedProduct = $seller->products()
                ->with('images')
                ->where('status', 'active')
                ->where(function ($query) use ($product_ref): void {
                    $query->where('slug', $product_ref)
                        ->orWhereRaw('LOWER(sku) = ?', [strtolower($product_ref)]);
                })
                ->first();

            if (! $selectedProduct) {
                abort(404);
            }
        }

        $activeFilters = [
            'q' => $search,
            'category' => $category,
            'sort' => $sort,
        ];

        $rawDummyEnabled = $seller->aiSetting?->fashn_dummy_enabled;
        $parsedDummyEnabled = filter_var($rawDummyEnabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $dummyEnabled = $parsedDummyEnabled ?? ((string) $rawDummyEnabled === '1' || (int) $rawDummyEnabled === 1);
        $dummyModelImageUrl = is_string($seller->aiSetting?->fashn_dummy_model_image_url)
            ? trim($seller->aiSetting->fashn_dummy_model_image_url)
            : '';
        $dummyResultUrl = is_string($seller->aiSetting?->fashn_dummy_result_url)
            ? trim($seller->aiSetting->fashn_dummy_result_url)
            : '';

        $tryOnDummy = [
            'enabled' => $dummyEnabled,
            'model_image_url' => $dummyModelImageUrl,
            'result_url' => $dummyResultUrl,
        ];

        return view('public.seller', compact(
            'seller',
            'products',
            'selectedProduct',
            'tryOnDummy',
            'categories',
            'activeFilters',
        ));
    }
}
