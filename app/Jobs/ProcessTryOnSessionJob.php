<?php

namespace App\Jobs;

use App\Domain\AI\ProviderRouter;
use App\Models\AuditLog;
use App\Models\TryOnSession;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\Job as QueueJobContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessTryOnSessionJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 30;

    public function __construct(public int $sessionId)
    {
    }

    public function handle(ProviderRouter $providerRouter): void
    {
        $session = TryOnSession::query()->find($this->sessionId);

        if (! $session || $session->status !== 'pending') {
            return;
        }

        $provider = $providerRouter->resolve($session->provider_name);
        $session->loadMissing('product.images', 'seller.aiSetting');
        $providerConfig = $this->resolveProviderConfig($session);
        $productImageUrl = $this->resolveProductImageUrl($session);
        $modelImageUrl = $this->resolveModelImageUrl($session->customer_photo_path, $providerConfig);

        if ($productImageUrl === null || $modelImageUrl === null) {
            $this->failSession($session, null, 'Gambar produk atau foto customer tidak valid untuk dikirim ke provider.');

            return;
        }

        $session->update(['status' => 'processing']);

        try {
            $createRequestStartedAt = now();
            $createRequestStartedMicro = microtime(true);
            $payload = $provider->createJob([
                'product_id' => $session->product_id,
                'customer_photo_path' => $session->customer_photo_path,
                'quality_mode' => $session->quality_mode,
                'product_image_url' => $productImageUrl,
                'model_image_url' => $modelImageUrl,
                'num_images' => 1,
                'product_ai_prompt' => $session->product?->ai_prompt,
                'product_ai_category' => $session->product?->ai_category,
                'product_ai_garment_photo_type' => $session->product?->ai_garment_photo_type,
                'product_ai_segmentation_free' => $session->product?->ai_segmentation_free,
                'provider_config' => $providerConfig,
            ]);
            $createRequestFinishedAt = now();

            $this->logProviderAudit(
                session: $session,
                action: 'tryon_provider_create_job',
                requestStartedAt: $createRequestStartedAt->toISOString(),
                requestFinishedAt: $createRequestFinishedAt->toISOString(),
                durationMs: (int) round((microtime(true) - $createRequestStartedMicro) * 1000),
                requestPayload: [
                    'product_id' => $session->product_id,
                    'customer_photo_path' => $session->customer_photo_path,
                    'quality_mode' => $session->quality_mode,
                    'product_image_url' => $productImageUrl,
                    'model_image_url' => $modelImageUrl,
                    'num_images' => 1,
                ],
                responsePayload: $payload
            );
        } catch (Throwable $exception) {
            $this->logProviderAudit(
                session: $session,
                action: 'tryon_provider_create_job_failed',
                requestStartedAt: isset($createRequestStartedAt) ? $createRequestStartedAt->toISOString() : now()->toISOString(),
                requestFinishedAt: now()->toISOString(),
                durationMs: isset($createRequestStartedMicro) ? (int) round((microtime(true) - $createRequestStartedMicro) * 1000) : 0,
                requestPayload: [
                    'product_id' => $session->product_id,
                    'customer_photo_path' => $session->customer_photo_path,
                    'quality_mode' => $session->quality_mode,
                    'product_image_url' => $productImageUrl,
                    'model_image_url' => $modelImageUrl,
                    'num_images' => 1,
                ],
                responsePayload: [
                    'error_message' => $exception->getMessage(),
                ]
            );

            $this->failSession($session, null, 'Gagal membuat job ke provider. '.$exception->getMessage());

            return;
        }

        Log::info('Try-on provider job created', [
            'session_id' => $session->id,
            'seller_id' => $session->seller_id,
            'provider_name' => $session->provider_name,
            'provider_job_id' => $payload['provider_job_id'] ?? null,
            'quality_mode' => $session->quality_mode,
        ]);

        if (isset($payload['provider_model']) && is_string($payload['provider_model']) && trim($payload['provider_model']) !== '') {
            $session->provider_model = trim($payload['provider_model']);
            $session->save();
        }

        $cost = $provider->estimateCost([
            'quality_mode' => $session->quality_mode,
            'num_images' => 1,
            'provider_config' => $providerConfig,
        ]);

        try {
            $statusRequestStartedAt = now();
            $statusRequestStartedMicro = microtime(true);
            $status = $provider->getJobStatus((string) $payload['provider_job_id'], [
                'provider_config' => $providerConfig,
            ]);
            $statusRequestFinishedAt = now();

            $this->logProviderAudit(
                session: $session,
                action: 'tryon_provider_get_status',
                requestStartedAt: $statusRequestStartedAt->toISOString(),
                requestFinishedAt: $statusRequestFinishedAt->toISOString(),
                durationMs: (int) round((microtime(true) - $statusRequestStartedMicro) * 1000),
                requestPayload: [
                    'provider_job_id' => $payload['provider_job_id'] ?? null,
                ],
                responsePayload: $status
            );
        } catch (Throwable $exception) {
            $this->logProviderAudit(
                session: $session,
                action: 'tryon_provider_get_status_failed',
                requestStartedAt: isset($statusRequestStartedAt) ? $statusRequestStartedAt->toISOString() : now()->toISOString(),
                requestFinishedAt: now()->toISOString(),
                durationMs: isset($statusRequestStartedMicro) ? (int) round((microtime(true) - $statusRequestStartedMicro) * 1000) : 0,
                requestPayload: [
                    'provider_job_id' => $payload['provider_job_id'] ?? null,
                ],
                responsePayload: [
                    'error_message' => $exception->getMessage(),
                ]
            );

            $this->failSession($session, (string) ($payload['provider_job_id'] ?? null), 'Provider status check error. '.$exception->getMessage());

            return;
        }

        if (($status['status'] ?? null) === 'processing') {
            $session->update([
                'provider_job_id' => $payload['provider_job_id'] ?? null,
            ]);

            if ($this->isSyncExecution()) {
                $status = $this->pollStatusSynchronously(
                    session: $session,
                    provider: $provider,
                    providerJobId: (string) ($payload['provider_job_id'] ?? ''),
                    maxAttempts: (int) config('tryon.polling.max_attempts', 30),
                    intervalSeconds: (int) config('tryon.polling.release_seconds', 2),
                    providerConfig: $providerConfig,
                );

                if (($status['status'] ?? null) === 'processing') {
                    $this->failSession($session, (string) ($payload['provider_job_id'] ?? null), 'Provider timeout: status belum completed.');

                    return;
                }
            }

            if (($status['status'] ?? null) !== 'processing') {
                // Continue to completed/failed handling below.
            } else {
            $attempts = $this->currentAttempt();
            if ($attempts >= (int) config('tryon.polling.max_attempts', 30)) {
                $this->failSession($session, (string) ($payload['provider_job_id'] ?? null), 'Provider timeout: status belum completed.');

                return;
            }

            $this->release((int) config('tryon.polling.release_seconds', 2));

            return;
            }
        }

        if (($status['status'] ?? null) === 'completed') {
            $session->update([
                'status' => 'completed',
                'provider_job_id' => $payload['provider_job_id'] ?? null,
                'result_path' => $status['result_path'] ?? $status['result_url'] ?? $session->customer_photo_path,
                'token_cost' => $cost,
                'expires_at' => Carbon::now()->addMinutes((int) config('tryon.retention_minutes')),
            ]);

            AuditLog::query()->create([
                'actor_user_id' => null,
                'action' => 'tryon_session_completed',
                'entity_type' => TryOnSession::class,
                'entity_id' => $session->id,
                'payload_json' => [
                    'seller_id' => $session->seller_id,
                    'product_id' => $session->product_id,
                    'provider_name' => $session->provider_name,
                    'provider_job_id' => $payload['provider_job_id'] ?? null,
                    'token_cost' => $cost,
                ],
            ]);

            Log::info('Try-on session completed', [
                'session_id' => $session->id,
                'seller_id' => $session->seller_id,
                'provider_job_id' => $payload['provider_job_id'] ?? null,
                'token_cost' => $cost,
            ]);

            return;
        }

        $this->failSession(
            $session,
            (string) ($payload['provider_job_id'] ?? null),
            $status['error_message'] ?? 'Provider failure.'
        );

        AuditLog::query()->create([
            'actor_user_id' => null,
            'action' => 'tryon_session_failed',
            'entity_type' => TryOnSession::class,
            'entity_id' => $session->id,
            'payload_json' => [
                'seller_id' => $session->seller_id,
                'product_id' => $session->product_id,
                'provider_name' => $session->provider_name,
                'provider_job_id' => $payload['provider_job_id'] ?? null,
                'error_message' => $status['error_message'] ?? 'Provider failure.',
            ],
        ]);

        Log::warning('Try-on session failed', [
            'session_id' => $session->id,
            'seller_id' => $session->seller_id,
            'provider_job_id' => $payload['provider_job_id'] ?? null,
            'error_message' => $status['error_message'] ?? 'Provider failure.',
        ]);
    }

    private function failSession(TryOnSession $session, ?string $providerJobId, string $errorMessage): void
    {
        $session->update([
            'status' => 'failed',
            'provider_job_id' => $providerJobId,
            'error_message' => $errorMessage,
        ]);
    }

    private function currentAttempt(): int
    {
        $queueJob = $this->job ?? null;
        if (! $queueJob instanceof QueueJobContract) {
            return 1;
        }

        return (int) $queueJob->attempts();
    }

    private function logProviderAudit(
        TryOnSession $session,
        string $action,
        string $requestStartedAt,
        string $requestFinishedAt,
        int $durationMs,
        array $requestPayload,
        array $responsePayload
    ): void {
        $normalizedUsage = $this->normalizeUsage((array) ($responsePayload['provider_usage'] ?? []));

        AuditLog::query()->create([
            'actor_user_id' => null,
            'action' => $action,
            'entity_type' => TryOnSession::class,
            'entity_id' => $session->id,
            'payload_json' => [
                'seller_id' => $session->seller_id,
                'product_id' => $session->product_id,
                'provider_name' => $responsePayload['provider_name'] ?? $session->provider_name,
                'provider_model' => $responsePayload['provider_model'] ?? null,
                'provider_job_id' => $responsePayload['provider_job_id'] ?? null,
                'provider_endpoint' => $responsePayload['provider_endpoint'] ?? null,
                'provider_method' => $responsePayload['provider_method'] ?? null,
                'http_status' => $responsePayload['http_status'] ?? null,
                'provider_usage' => $normalizedUsage,
                'provider_usage_raw' => $responsePayload['provider_usage'] ?? [],
                'request_started_at' => $requestStartedAt,
                'request_finished_at' => $requestFinishedAt,
                'duration_ms' => $durationMs,
                'request_payload' => $requestPayload,
                'response_payload' => $this->truncatePayload($responsePayload),
            ],
        ]);
    }

    private function truncatePayload(array $payload): array
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (! is_string($json)) {
            return $payload;
        }

        if (strlen($json) <= 4000) {
            return $payload;
        }

        return [
            'truncated' => true,
            'snippet' => substr($json, 0, 4000),
        ];
    }

    private function normalizeUsage(array $usage): array
    {
        $tokensUsed = $usage['tokens_used'] ?? null;
        $creditsUsed = $usage['credits_used'] ?? null;
        $estimatedCost = $usage['estimated_cost'] ?? null;
        $currency = $usage['currency'] ?? null;

        $billingUnit = 'unknown';
        $billingValue = null;

        if ($creditsUsed !== null) {
            $billingUnit = 'credit';
            $billingValue = $creditsUsed;
        } elseif ($tokensUsed !== null) {
            $billingUnit = 'token';
            $billingValue = $tokensUsed;
        }

        return [
            'tokens_used' => $tokensUsed,
            'credits_used' => $creditsUsed,
            'estimated_cost' => $estimatedCost,
            'currency' => $currency,
            'billing_unit' => $billingUnit,
            'billing_value' => $billingValue,
        ];
    }

    private function resolveModelImageUrl(?string $customerPhotoPath, array $providerConfig = []): ?string
    {
        $sellerDummyUrl = trim((string) ($providerConfig['dummy_model_image_url'] ?? ''));
        if ($sellerDummyUrl !== '') {
            return $sellerDummyUrl;
        }

        if (! is_string($customerPhotoPath) || $customerPhotoPath === '') {
            return null;
        }

        if (str_starts_with($customerPhotoPath, 'http://') || str_starts_with($customerPhotoPath, 'https://')) {
            return $customerPhotoPath;
        }

        return url(Storage::disk('public')->url($customerPhotoPath));
    }

    private function resolveProductImageUrl(TryOnSession $session): ?string
    {
        $product = $session->product;
        if (! $product) {
            return null;
        }

        $image = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
        if (! $image) {
            return null;
        }

        return (string) $image->image_url;
    }

    private function isSyncExecution(): bool
    {
        return (string) config('queue.default') === 'sync';
    }

    private function pollStatusSynchronously(
        TryOnSession $session,
        object $provider,
        string $providerJobId,
        int $maxAttempts,
        int $intervalSeconds,
        array $providerConfig
    ): array {
        $attempts = 1;
        $latestStatus = ['status' => 'processing'];

        while ($attempts < $maxAttempts) {
            sleep(max(1, $intervalSeconds));

            $requestStartedAt = now();
            $startedMicro = microtime(true);
            $latestStatus = $provider->getJobStatus($providerJobId, [
                'provider_config' => $providerConfig,
            ]);
            $requestFinishedAt = now();

            $this->logProviderAudit(
                session: $session,
                action: 'tryon_provider_get_status',
                requestStartedAt: $requestStartedAt->toISOString(),
                requestFinishedAt: $requestFinishedAt->toISOString(),
                durationMs: (int) round((microtime(true) - $startedMicro) * 1000),
                requestPayload: ['provider_job_id' => $providerJobId],
                responsePayload: $latestStatus
            );

            if (($latestStatus['status'] ?? null) !== 'processing') {
                return $latestStatus;
            }

            $attempts++;
        }

        return $latestStatus;
    }

    private function resolveProviderConfig(TryOnSession $session): array
    {
        $config = [];
        $aiSetting = $session->seller?->aiSetting;
        if (! $aiSetting) {
            return $config;
        }

        if (is_string($aiSetting->fashn_api_key) && trim($aiSetting->fashn_api_key) !== '') {
            $config['api_key'] = $aiSetting->fashn_api_key;
        }

        if (is_string($aiSetting->fashn_model) && trim($aiSetting->fashn_model) !== '') {
            $config['model'] = $aiSetting->fashn_model;
        }

        if (($config['model'] ?? null) === 'tryon-v1.6') {
            if (is_string($aiSetting->fashn_tryon_v16_mode) && trim($aiSetting->fashn_tryon_v16_mode) !== '') {
                $config['v16_mode'] = $aiSetting->fashn_tryon_v16_mode;
            }

            if ((int) ($aiSetting->fashn_tryon_v16_num_samples ?? 0) > 0) {
                $config['v16_num_samples'] = (int) $aiSetting->fashn_tryon_v16_num_samples;
            }

            if (is_string($aiSetting->fashn_tryon_v16_output_format) && trim($aiSetting->fashn_tryon_v16_output_format) !== '') {
                $config['v16_output_format'] = $aiSetting->fashn_tryon_v16_output_format;
            }
        } else {
            if (is_string($aiSetting->fashn_tryon_max_generation_mode) && trim($aiSetting->fashn_tryon_max_generation_mode) !== '') {
                $config['generation_mode'] = $aiSetting->fashn_tryon_max_generation_mode;
            }

            if (is_string($aiSetting->fashn_tryon_max_resolution) && trim($aiSetting->fashn_tryon_max_resolution) !== '') {
                $config['resolution'] = $aiSetting->fashn_tryon_max_resolution;
            }

            if (is_string($aiSetting->fashn_tryon_max_output_format) && trim($aiSetting->fashn_tryon_max_output_format) !== '') {
                $config['output_format'] = $aiSetting->fashn_tryon_max_output_format;
            }
        }

        $config['dummy_enabled'] = (bool) $aiSetting->fashn_dummy_enabled;

        if (is_string($aiSetting->fashn_dummy_result_url) && trim($aiSetting->fashn_dummy_result_url) !== '') {
            $config['dummy_result_url'] = $aiSetting->fashn_dummy_result_url;
        }

        if (is_string($aiSetting->fashn_dummy_model_image_url) && trim($aiSetting->fashn_dummy_model_image_url) !== '') {
            $config['dummy_model_image_url'] = $aiSetting->fashn_dummy_model_image_url;
        }

        return $config;
    }
}
