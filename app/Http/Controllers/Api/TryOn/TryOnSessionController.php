<?php

namespace App\Http\Controllers\Api\TryOn;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessTryOnSessionJob;
use App\Models\TryOnSession;
use App\Support\CurrentSellerResolver;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TryOnSessionController extends Controller
{
    public function __construct(private readonly CurrentSellerResolver $currentSellerResolver)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user())
            ->load('aiSetting');

        $payload = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'customer_photo_path' => ['nullable', 'string'],
            'customer_photo_url' => ['nullable', 'string'],
            'quality_mode' => ['required', 'in:standard,hd,ultra,standard_quality,high_quality,ultra_quality'],
            'source_channel' => ['nullable', 'in:subpage,seller_subpage,widget,wordpress,shopify,api'],
        ]);

        $product = $seller->products()->where('id', $payload['product_id'])->first();

        if (! $product) {
            return response()->json(['message' => 'Product not found for this seller.'], 422);
        }

        $apiKey = trim((string) ($seller->aiSetting?->fashn_api_key ?? ''));
        $dummyEnabled = (bool) ($seller->aiSetting?->fashn_dummy_enabled ?? false);
        if ($apiKey === '' && ! $dummyEnabled) {
            return response()->json([
                'message' => 'FASHN API key belum dikonfigurasi di Settings.',
            ], 422);
        }

        $customerPhotoPath = $payload['customer_photo_path'] ?? $payload['customer_photo_url'] ?? null;

        if (! $customerPhotoPath) {
            return response()->json([
                'message' => 'customer_photo_path atau customer_photo_url wajib diisi.',
            ], 422);
        }

        $qualityMode = match ($payload['quality_mode']) {
            'standard_quality' => 'standard',
            'high_quality' => 'hd',
            'ultra_quality' => 'ultra',
            default => $payload['quality_mode'],
        };
        $providerModel = trim((string) ($seller->aiSetting?->fashn_model ?? ''));
        if ($providerModel === '') {
            $providerModel = (string) config('ai.providers.fashn.model', 'tryon-max');
        }

        $session = TryOnSession::query()->create([
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'customer_photo_path' => $customerPhotoPath,
            'quality_mode' => $qualityMode,
            'source_channel' => $payload['source_channel'] ?? 'subpage',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'provider_name' => 'fashn',
            'provider_model' => $providerModel,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes((int) config('tryon.retention_minutes')),
        ]);

        ProcessTryOnSessionJob::dispatch($session->id);

        return response()->json($session, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user());

        $session = TryOnSession::query()
            ->where('seller_id', $seller->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($session);
    }
}
