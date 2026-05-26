<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerAiSetting;
use App\Models\TryOnSession;
use App\Support\CurrentSellerResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SellerDashboardController extends Controller
{
    public function __construct(private readonly CurrentSellerResolver $currentSellerResolver)
    {
    }

    public function index()
    {
        $seller = $this->currentSellerResolver->resolveForUser(auth()->user());
        $seller->loadMissing('aiSetting');

        [$fashnCredits, $fashnCreditsSource] = $this->resolveFashnCredits($seller->aiSetting?->fashn_api_key);
        $activeModel = (string) ($seller->aiSetting?->fashn_model ?: 'tryon-max');
        $activeModelLabel = $activeModel === 'tryon-v1.6'
            ? 'FASHN Virtual Try-On v1.6'
            : 'Try-On Max';
        $activeModelConfig = $this->resolveActiveModelConfig($activeModel, $seller->aiSetting);
        $dummyEnabled = (bool) ($seller->aiSetting?->fashn_dummy_enabled ?? false);
        $dummyResultUrl = is_string($seller->aiSetting?->fashn_dummy_result_url)
            ? trim($seller->aiSetting->fashn_dummy_result_url)
            : '';
        $dummyModelImageUrl = is_string($seller->aiSetting?->fashn_dummy_model_image_url)
            ? trim($seller->aiSetting->fashn_dummy_model_image_url)
            : '';

        $stats = [
            'total_products' => $seller->products()->count(),
            'fashn_credits' => $fashnCredits,
            'fashn_credits_source' => $fashnCreditsSource,
            'fashn_model' => $activeModel,
            'fashn_model_label' => $activeModelLabel,
            'fashn_model_config' => $activeModelConfig,
            'dummy_enabled' => $dummyEnabled,
            'dummy_result_url' => $dummyResultUrl,
            'dummy_model_image_url' => $dummyModelImageUrl,
            'recent_tryon' => TryOnSession::query()
                ->with(['product:id,name', 'product.images:id,product_id,path,source_type,is_primary'])
                ->where('seller_id', $seller->id)
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return view('seller.dashboard', compact('seller', 'stats'));
    }

    public function updateModel(Request $request): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user());
        $payload = $request->validate([
            'fashn_model' => ['required', 'in:tryon-v1.6,tryon-max'],
        ]);

        $setting = SellerAiSetting::query()->firstOrNew(['seller_id' => $seller->id]);
        $setting->provider_name = 'fashn';
        $setting->fashn_model = $payload['fashn_model'];
        $setting->save();

        $setting->refresh();

        $activeModel = (string) ($setting->fashn_model ?: 'tryon-max');
        $activeModelLabel = $activeModel === 'tryon-v1.6'
            ? 'FASHN Virtual Try-On v1.6'
            : 'Try-On Max';

        return response()->json([
            'ok' => true,
            'model' => $activeModel,
            'model_label' => $activeModelLabel,
            'config' => $this->resolveActiveModelConfig($activeModel, $setting),
        ]);
    }

    private function resolveFashnCredits(?string $apiKey): array
    {
        $fallbackCredits = [
            'total' => 0,
            'subscription' => 0,
            'on_demand' => 0,
        ];

        $key = trim((string) $apiKey);
        if ($key === '') {
            return [$fallbackCredits, 'local'];
        }

        try {
            $response = Http::acceptJson()
                ->withToken($key)
                ->timeout(max((int) config('ai.providers.fashn.timeout_seconds', 60), 5))
                ->get('https://api.fashn.ai/v1/credits');

            if (! $response->successful()) {
                return [$fallbackCredits, 'local'];
            }

            $payload = (array) $response->json();
            $credits = $this->extractCreditsBreakdown($payload);
            if ($credits === null) {
                return [$fallbackCredits, 'local'];
            }

            return [$credits, 'fashn'];
        } catch (\Throwable) {
            return [$fallbackCredits, 'local'];
        }
    }

    private function extractCreditsBreakdown(array $payload): ?array
    {
        $credits = isset($payload['credits']) && is_array($payload['credits']) ? $payload['credits'] : [];
        $total = $this->toIntOrNull($credits['total'] ?? null);
        $subscription = $this->toIntOrNull($credits['subscription'] ?? null);
        $onDemand = $this->toIntOrNull($credits['on_demand'] ?? null);

        if ($total !== null || $subscription !== null || $onDemand !== null) {
            $normalizedSubscription = $subscription ?? 0;
            $normalizedOnDemand = $onDemand ?? 0;
            $normalizedTotal = $total ?? ($normalizedSubscription + $normalizedOnDemand);

            return [
                'total' => max(0, $normalizedTotal),
                'subscription' => max(0, $normalizedSubscription),
                'on_demand' => max(0, $normalizedOnDemand),
            ];
        }

        $flatTotal = $this->toIntOrNull($payload['available_credits'] ?? $payload['remaining_credits'] ?? $payload['credits_remaining'] ?? null);
        if ($flatTotal !== null) {
            return [
                'total' => max(0, $flatTotal),
                'subscription' => 0,
                'on_demand' => max(0, $flatTotal),
            ];
        }

        return null;
    }

    private function toIntOrNull(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (int) round((float) $value);
    }

    private function resolveActiveModelConfig(string $activeModel, mixed $aiSetting): array
    {
        if ($activeModel === 'tryon-v1.6') {
            return [
                'mode' => (string) ($aiSetting?->fashn_tryon_v16_mode ?: 'balanced'),
                'samples' => (int) ($aiSetting?->fashn_tryon_v16_num_samples ?: 1),
                'format' => (string) ($aiSetting?->fashn_tryon_v16_output_format ?: 'png'),
            ];
        }

        return [
            'generation_mode' => (string) ($aiSetting?->fashn_tryon_max_generation_mode ?: 'balanced'),
            'resolution' => (string) ($aiSetting?->fashn_tryon_max_resolution ?: '1k'),
            'format' => (string) ($aiSetting?->fashn_tryon_max_output_format ?: 'png'),
        ];
    }
}
