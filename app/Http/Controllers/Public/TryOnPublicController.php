<?php

namespace App\Http\Controllers\Public;

use App\Domain\AI\ProviderRouter;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessTryOnSessionJob;
use App\Models\AuditLog;
use App\Models\Seller;
use App\Models\TryOnSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TryOnPublicController extends Controller
{
    public function quota(Request $request, string $seller_slug): JsonResponse
    {
        $seller = Seller::query()
            ->where('slug', $seller_slug)
            ->where('status', 'active')
            ->with('aiSetting')
            ->firstOrFail();

        $providerConfig = $this->resolveProviderConfig($seller);
        if (! $this->canUseProvider($providerConfig)) {
            return response()->json([
                'message' => 'FASHN API key belum dikonfigurasi oleh store owner.',
            ], 422);
        }

        return response()->json($this->buildQuotaPayload($request, $seller->slug));
    }

    public function store(Request $request, string $seller_slug): JsonResponse
    {
        $seller = Seller::query()
            ->where('slug', $seller_slug)
            ->where('status', 'active')
            ->with('aiSetting')
            ->firstOrFail();

        $quota = $this->buildQuotaPayload($request, $seller->slug);
        if (($quota['can_generate'] ?? false) !== true) {
            return response()->json([
                'message' => 'Batas generate harian habis untuk device/IP ini.',
                'quota' => $quota,
            ], 429);
        }

        $payload = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'use_dummy_model' => ['nullable', 'boolean'],
            'customer_photo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $dummyModelImageUrl = is_string($seller->aiSetting?->fashn_dummy_model_image_url)
            ? trim($seller->aiSetting->fashn_dummy_model_image_url)
            : '';
        $hasDummyModelImageUrl = $dummyModelImageUrl !== '';
        $useDummyModel = $hasDummyModelImageUrl && $request->boolean('use_dummy_model');

        if (! $request->hasFile('customer_photo') && ! $useDummyModel) {
            return response()->json([
                'message' => 'Upload foto model terlebih dahulu atau isi Dummy Model Image URL di settings.',
            ], 422);
        }

        // Locked to the lowest-cost provider mode (balanced + 1k via "standard").
        $qualityMode = 'standard';

        $product = $seller->products()
            ->where('id', $payload['product_id'])
            ->where('status', 'active')
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Produk tidak valid untuk seller ini.'], 422);
        }

        $providerConfig = $this->resolveProviderConfig($seller);
        $providerModel = (string) ($providerConfig['model'] ?? config('ai.providers.fashn.model', 'tryon-max'));

        $photoPath = $useDummyModel
            ? $dummyModelImageUrl
            : $payload['customer_photo']->store('tryon/customers/'.$seller->id, 'public');

        $sessionPayload = [
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'customer_photo_path' => $photoPath,
            'quality_mode' => $qualityMode,
            'source_channel' => 'seller_subpage',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'provider_name' => 'fashn',
            'provider_model' => $providerModel,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes((int) config('tryon.retention_minutes')),
        ];

        if ($this->hasTryOnDeviceIdColumn()) {
            $sessionPayload['device_id'] = $this->resolveTryOnDeviceId($request);
        }

        $session = TryOnSession::query()->create($sessionPayload);

        AuditLog::query()->create([
            'actor_user_id' => null,
            'action' => 'tryon_session_created_public',
            'entity_type' => TryOnSession::class,
            'entity_id' => $session->id,
            'payload_json' => [
                'seller_id' => $seller->id,
                'product_id' => $product->id,
                'source_channel' => 'seller_subpage',
                'quality_mode' => $qualityMode,
                'ip_address' => $request->ip(),
            ],
        ]);

        Log::info('Public try-on session created', [
            'session_id' => $session->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'quality_mode' => $qualityMode,
            'source_channel' => 'seller_subpage',
        ]);

        ProcessTryOnSessionJob::dispatch($session->id);

        // Count only valid created sessions toward daily public generate limit.
        $this->consumeGenerateQuota($request, $seller->slug);

        return response()->json([
            ...$this->sessionResponse($session->fresh()),
            'quota' => $this->buildQuotaPayload($request, $seller->slug),
        ], 201);
    }

    public function show(string $seller_slug, int $sessionId): JsonResponse
    {
        $seller = Seller::query()
            ->where('slug', $seller_slug)
            ->where('status', 'active')
            ->firstOrFail();

        $session = TryOnSession::query()
            ->where('seller_id', $seller->id)
            ->where('id', $sessionId)
            ->firstOrFail();

        $this->refreshProcessingSession($session);

        return response()->json($this->sessionResponse($session));
    }

    public function history(Request $request, string $seller_slug): JsonResponse
    {
        $seller = Seller::query()
            ->where('slug', $seller_slug)
            ->where('status', 'active')
            ->firstOrFail();

        $deviceId = $this->resolveTryOnDeviceId($request);

        $query = TryOnSession::query()
            ->where('seller_id', $seller->id)
            ->where('source_channel', 'seller_subpage')
            ->where('status', 'completed')
            ->whereNotNull('result_path')
            ->latest();

        if ($this->hasTryOnDeviceIdColumn()) {
            $query->where('device_id', $deviceId);
        } else {
            // Backward-compatible fallback until migration for device_id is applied.
            $query->where('ip_address', $request->ip())
                ->where('user_agent', (string) $request->userAgent());
        }

        $sessions = $query->limit(12)->get();

        return response()->json([
            'items' => $sessions
                ->map(fn (TryOnSession $session): array => $this->sessionResponse($session))
                ->values(),
        ]);
    }

    private function refreshProcessingSession(TryOnSession $session): void
    {
        if ($session->status !== 'processing' || ! $session->provider_job_id) {
            return;
        }
        $session->loadMissing('seller.aiSetting');

        try {
            /** @var ProviderRouter $providerRouter */
            $providerRouter = app(ProviderRouter::class);
            $provider = $providerRouter->resolve($session->provider_name);
            $status = $provider->getJobStatus((string) $session->provider_job_id, [
                'provider_config' => $this->resolveProviderConfig($session->seller),
            ]);

            if (($status['status'] ?? null) === 'completed') {
                $cost = $provider->estimateCost([
                    'quality_mode' => $session->quality_mode,
                    'num_images' => 1,
                    'provider_config' => $this->resolveProviderConfig($session->seller),
                ]);

                $session->update([
                    'status' => 'completed',
                    'result_path' => $status['result_path'] ?? $status['result_url'] ?? $session->result_path,
                    'token_cost' => $cost,
                    'expires_at' => Carbon::now()->addMinutes((int) config('tryon.retention_minutes')),
                ]);
            }

            if (($status['status'] ?? null) === 'failed') {
                $session->update([
                    'status' => 'failed',
                    'error_message' => $status['error_message'] ?? 'Provider failure.',
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to refresh processing try-on session', [
                'session_id' => $session->id,
                'provider_job_id' => $session->provider_job_id,
                'message' => $exception->getMessage(),
            ]);
        }

        $session->refresh();
    }

    private function sessionResponse(TryOnSession $session): array
    {
        return [
            'id' => $session->id,
            'status' => $session->status,
            'quality_mode' => $session->quality_mode,
            'error_message' => $session->error_message,
            'token_cost' => $session->token_cost,
            'result_url' => $this->toPublicUrl($session->result_path),
            'customer_photo_url' => $this->toPublicUrl($session->customer_photo_path),
            'created_at' => optional($session->created_at)?->toISOString(),
            'updated_at' => optional($session->updated_at)?->toISOString(),
        ];
    }

    private function toPublicUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function buildQuotaPayload(Request $request, string $sellerSlug): array
    {
        $seller = Seller::query()->where('slug', $sellerSlug)->with('aiSetting')->first();
        $setting = $seller?->aiSetting;

        $dailyLimit = $this->resolveDailyLimit($setting);
        $minuteLimit = max((int) config('tryon.public_limits.generate_per_minute_per_ip', 3), 1);
        $limitPerIpEnabled = $this->resolvePerIpEnabled($setting);
        $limitPerDeviceEnabled = $this->resolvePerDeviceEnabled($setting);

        $ipDailyRemaining = $limitPerIpEnabled
            ? RateLimiter::remaining($this->dailyIpKey($request, $sellerSlug), $dailyLimit)
            : $dailyLimit;
        $deviceDailyRemaining = $limitPerDeviceEnabled
            ? RateLimiter::remaining($this->dailyDeviceKey($request, $sellerSlug), $dailyLimit)
            : $dailyLimit;
        $minuteIpRemaining = $limitPerIpEnabled
            ? RateLimiter::remaining($this->minuteIpKey($request, $sellerSlug), $minuteLimit)
            : $minuteLimit;

        $dailyCandidates = [];
        if ($limitPerIpEnabled) {
            $dailyCandidates[] = $ipDailyRemaining;
        }
        if ($limitPerDeviceEnabled) {
            $dailyCandidates[] = $deviceDailyRemaining;
        }

        $remaining = count($dailyCandidates) > 0
            ? max(min($dailyCandidates), 0)
            : $dailyLimit;
        $canGenerate = $remaining > 0 && $minuteIpRemaining > 0;

        return [
            'daily_limit' => $dailyLimit,
            'minute_limit_per_ip' => $minuteLimit,
            'remaining' => $remaining,
            'ip_daily_remaining' => max($ipDailyRemaining, 0),
            'device_daily_remaining' => max($deviceDailyRemaining, 0),
            'minute_remaining' => max($minuteIpRemaining, 0),
            'limit_per_ip_enabled' => $limitPerIpEnabled,
            'limit_per_device_enabled' => $limitPerDeviceEnabled,
            'can_generate' => $canGenerate,
        ];
    }

    private function consumeGenerateQuota(Request $request, string $sellerSlug): void
    {
        $seller = Seller::query()->where('slug', $sellerSlug)->with('aiSetting')->first();
        $setting = $seller?->aiSetting;
        $limitPerIpEnabled = $this->resolvePerIpEnabled($setting);
        $limitPerDeviceEnabled = $this->resolvePerDeviceEnabled($setting);

        if ($limitPerIpEnabled) {
            RateLimiter::hit($this->minuteIpKey($request, $sellerSlug), 60);
            RateLimiter::hit($this->dailyIpKey($request, $sellerSlug), 86400);
        }

        if ($limitPerDeviceEnabled) {
            RateLimiter::hit($this->dailyDeviceKey($request, $sellerSlug), 86400);
        }
    }

    private function resolveDailyLimit($setting): int
    {
        $configured = (int) ($setting?->public_generate_per_day ?? 0);

        if ($configured > 0) {
            return $configured;
        }

        return max((int) config('tryon.public_limits.generate_per_day', 3), 1);
    }

    private function resolvePerIpEnabled($setting): bool
    {
        if ($setting === null) {
            return true;
        }

        return (bool) ($setting->public_limit_per_ip_enabled ?? true);
    }

    private function resolvePerDeviceEnabled($setting): bool
    {
        if ($setting === null) {
            return true;
        }

        return (bool) ($setting->public_limit_per_device_enabled ?? true);
    }

    private function minuteIpKey(Request $request, string $sellerSlug): string
    {
        return 'create-minute-ip|'.$sellerSlug.'|'.$request->ip();
    }

    private function dailyIpKey(Request $request, string $sellerSlug): string
    {
        return 'create-day-ip|'.$sellerSlug.'|'.$request->ip();
    }

    private function dailyDeviceKey(Request $request, string $sellerSlug): string
    {
        return 'create-day-device|'.$sellerSlug.'|'.$this->resolveTryOnDeviceId($request);
    }

    private function resolveTryOnDeviceId(Request $request): string
    {
        $rawDeviceId = strtolower(trim((string) $request->header('X-Tryon-Device-Id', '')));
        if ($rawDeviceId === '') {
            return 'missing-device-id';
        }

        $normalized = preg_replace('/[^a-z0-9\-_]/', '', $rawDeviceId);
        if (! is_string($normalized) || $normalized === '') {
            return 'invalid-device-id';
        }

        return substr($normalized, 0, 64);
    }

    private function resolveProviderConfig(?Seller $seller): array
    {
        if (! $seller) {
            return [];
        }

        $setting = $seller->aiSetting;
        if (! $setting) {
            return [];
        }

        $config = [
            'dummy_enabled' => (bool) $setting->fashn_dummy_enabled,
        ];

        if (is_string($setting->fashn_api_key) && trim($setting->fashn_api_key) !== '') {
            $config['api_key'] = $setting->fashn_api_key;
        }

        if (is_string($setting->fashn_model) && trim($setting->fashn_model) !== '') {
            $config['model'] = $setting->fashn_model;
        }

        if (($config['model'] ?? null) === 'tryon-v1.6') {
            if (is_string($setting->fashn_tryon_v16_mode) && trim($setting->fashn_tryon_v16_mode) !== '') {
                $config['v16_mode'] = $setting->fashn_tryon_v16_mode;
            }

            if ((int) ($setting->fashn_tryon_v16_num_samples ?? 0) > 0) {
                $config['v16_num_samples'] = (int) $setting->fashn_tryon_v16_num_samples;
            }

            if (is_string($setting->fashn_tryon_v16_output_format) && trim($setting->fashn_tryon_v16_output_format) !== '') {
                $config['v16_output_format'] = $setting->fashn_tryon_v16_output_format;
            }
        } else {
            if (is_string($setting->fashn_tryon_max_generation_mode) && trim($setting->fashn_tryon_max_generation_mode) !== '') {
                $config['generation_mode'] = $setting->fashn_tryon_max_generation_mode;
            }

            if (is_string($setting->fashn_tryon_max_resolution) && trim($setting->fashn_tryon_max_resolution) !== '') {
                $config['resolution'] = $setting->fashn_tryon_max_resolution;
            }

            if (is_string($setting->fashn_tryon_max_output_format) && trim($setting->fashn_tryon_max_output_format) !== '') {
                $config['output_format'] = $setting->fashn_tryon_max_output_format;
            }
        }

        if (is_string($setting->fashn_dummy_result_url) && trim($setting->fashn_dummy_result_url) !== '') {
            $config['dummy_result_url'] = $setting->fashn_dummy_result_url;
        }

        if (is_string($setting->fashn_dummy_model_image_url) && trim($setting->fashn_dummy_model_image_url) !== '') {
            $config['dummy_model_image_url'] = $setting->fashn_dummy_model_image_url;
        }

        return $config;
    }

    private function canUseProvider(array $providerConfig): bool
    {
        if (($providerConfig['dummy_enabled'] ?? false) === true) {
            return true;
        }

        return isset($providerConfig['api_key']) && trim((string) $providerConfig['api_key']) !== '';
    }

    private function hasTryOnDeviceIdColumn(): bool
    {
        static $hasColumn = null;
        if ($hasColumn !== null) {
            return $hasColumn;
        }

        $hasColumn = Schema::hasColumn('tryon_sessions', 'device_id');

        return $hasColumn;
    }
}
