<?php

namespace App\Domain\AI\Providers;

use App\Domain\AI\Contracts\TryOnProviderContract;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FashnProvider implements TryOnProviderContract
{
    public function createJob(array $input): array
    {
        $providerConfig = $this->mergeProviderConfig($input);

        if ($this->isDummyMode($providerConfig)) {
            return [
                'provider_name' => 'fashn',
                'provider_model' => (string) ($providerConfig['model'] ?? config('ai.providers.fashn.model', 'tryon-max')),
                'provider_job_id' => 'dummy-'.(string) str()->uuid(),
                'status' => 'processing',
                'result_path' => null,
                'result_url' => null,
                'error_message' => null,
                'provider_usage' => [
                    'credits_used' => 0,
                    'estimated_cost' => 0,
                    'currency' => 'credit',
                ],
                'http_status' => 200,
                'provider_endpoint' => 'dummy://fashn/run',
                'provider_method' => 'POST',
                'raw' => ['dummy' => true],
            ];
        }

        $createUrl = $this->resolveCreateUrl($providerConfig);
        $qualityMode = (string) ($input['quality_mode'] ?? 'standard');
        $generationMode = $this->resolveGenerationMode($qualityMode, $providerConfig);
        $resolution = $this->resolveResolution($qualityMode, $providerConfig);
        $outputFormat = $this->resolveOutputFormat($providerConfig);
        $modelName = (string) ($providerConfig['model'] ?? config('ai.providers.fashn.model', 'tryon-max'));

        $requestBody = [
            'model_name' => $modelName,
            'inputs' => [
                'product_image' => $input['product_image_url'] ?? null,
                'model_image' => $input['model_image_url'] ?? null,
            ],
            'generation_mode' => $generationMode,
            'resolution' => $resolution,
            'num_images' => 1,
            'output_format' => $outputFormat,
            'prompt' => $this->resolveTryOnMaxPrompt($input),
            'return_base64' => false,
        ];

        if ($modelName === 'tryon-v1.6') {
            $requestBody = [
                'model_name' => $modelName,
                'inputs' => [
                    'model_image' => $input['model_image_url'] ?? null,
                    'garment_image' => $input['product_image_url'] ?? null,
                ],
                'mode' => $this->resolveV16Mode($providerConfig),
                'num_samples' => $this->resolveV16NumSamples($providerConfig),
                'output_format' => $this->resolveV16OutputFormat($providerConfig),
                'category' => $this->resolveV16Category($input),
                'garment_photo_type' => $this->resolveV16GarmentPhotoType($input),
                'segmentation_free' => $this->resolveV16SegmentationFree($input),
                'return_base64' => false,
            ];
        }

        $response = $this->httpClient($providerConfig)->post($createUrl, $requestBody);

        $body = $response->json();
        $providerJobId = $body['id'] ?? $body['job_id'] ?? null;

        if (! is_string($providerJobId) || $providerJobId === '') {
            throw new RuntimeException('FASHN response missing job id.');
        }

        return [
            'provider_name' => 'fashn',
            'provider_model' => (string) ($providerConfig['model'] ?? config('ai.providers.fashn.model', 'unknown')),
            'provider_job_id' => $providerJobId,
            'status' => 'processing',
            'result_path' => $body['result_path'] ?? null,
            'result_url' => $body['result_url'] ?? ($body['result']['url'] ?? null),
            'error_message' => $body['error_message'] ?? ($body['error']['message'] ?? null),
            'provider_usage' => $this->extractUsage($body),
            'http_status' => $response->status(),
            'provider_endpoint' => $createUrl,
            'provider_method' => 'POST',
            'raw' => $body,
        ];
    }

    public function getJobStatus(string $jobId, array $context = []): array
    {
        $providerConfig = $this->mergeProviderConfig($context);
        if ($this->isDummyMode($providerConfig)) {
            $dummyResultUrl = trim((string) ($providerConfig['dummy_result_url'] ?? ''));
            if ($dummyResultUrl === '') {
                throw new RuntimeException('Dummy result URL is required when seller dummy mode is enabled.');
            }

            return [
                'provider_name' => 'fashn',
                'provider_model' => (string) ($providerConfig['model'] ?? config('ai.providers.fashn.model', 'tryon-max')),
                'provider_job_id' => $jobId,
                'status' => 'completed',
                'result_path' => $dummyResultUrl,
                'result_url' => $dummyResultUrl,
                'error_message' => null,
                'provider_usage' => [
                    'credits_used' => 0,
                    'estimated_cost' => 0,
                    'currency' => 'credit',
                ],
                'http_status' => 200,
                'provider_endpoint' => 'dummy://fashn/status/'.$jobId,
                'provider_method' => 'GET',
                'raw' => [
                    'id' => $jobId,
                    'status' => 'completed',
                    'output' => [$dummyResultUrl],
                    'dummy' => true,
                ],
            ];
        }

        $statusUrl = $this->resolveStatusUrl($jobId, $providerConfig);

        $response = $this->httpClient($providerConfig)->get($statusUrl);
        $body = $response->json();
        $output = $body['output'] ?? [];
        $resultUrl = is_array($output) && isset($output[0]) && is_string($output[0]) ? $output[0] : null;

        return [
            'provider_name' => 'fashn',
            'provider_model' => (string) ($providerConfig['model'] ?? config('ai.providers.fashn.model', 'unknown')),
            'provider_job_id' => $jobId,
            'status' => $this->normalizeStatus($body['status'] ?? 'processing'),
            'result_path' => $body['result_path'] ?? null,
            'result_url' => $body['result_url'] ?? ($body['result']['url'] ?? $resultUrl),
            'error_message' => $body['error_message'] ?? ($body['error']['message'] ?? ($body['error'] ?? null)),
            'provider_usage' => $this->extractUsage($body),
            'http_status' => $response->status(),
            'provider_endpoint' => $statusUrl,
            'provider_method' => 'GET',
            'raw' => $body,
        ];
    }

    public function estimateCost(array $input): int
    {
        $qualityMode = (string) ($input['quality_mode'] ?? 'standard');
        $providerConfig = [];
        if (isset($input['provider_config']) && is_array($input['provider_config'])) {
            $providerConfig = $input['provider_config'];
        }
        $modelName = (string) ($providerConfig['model'] ?? config('ai.providers.fashn.model', 'tryon-max'));

        if ($modelName === 'tryon-v1.6') {
            return $this->resolveV16NumSamples($providerConfig);
        }

        $generationMode = $this->resolveGenerationMode($qualityMode, $providerConfig);
        $resolution = $this->resolveResolution($qualityMode, $providerConfig);
        $numImages = max(1, (int) ($input['num_images'] ?? 1));

        $baseCost = match ($generationMode.'|'.$resolution) {
            'balanced|1k' => 2,
            'balanced|2k' => 3,
            'balanced|4k' => 4,
            'quality|1k' => 3,
            'quality|2k' => 4,
            'quality|4k' => 5,
            default => 2,
        };

        return $baseCost * $numImages;
    }

    private function httpClient(array $providerConfig = [])
    {
        $config = $providerConfig === [] ? (array) config('ai.providers.fashn') : $providerConfig;
        $apiKey = (string) ($config['api_key'] ?? '');

        if ($apiKey === '') {
            throw new RuntimeException('FASHN provider configuration is missing.');
        }

        $timeoutSeconds = max((int) ($config['timeout_seconds'] ?? 60), 1);
        $retryTimes = max((int) ($config['retry_times'] ?? 2), 0);
        $retrySleepMs = max((int) ($config['retry_sleep_ms'] ?? 300), 0);

        return Http::acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->timeout($timeoutSeconds)
            ->retry($retryTimes, $retrySleepMs, function ($exception): bool {
                return $exception instanceof ConnectionException || $exception instanceof RequestException;
            })
            ->throw(function ($response, $e): void {
                $this->throwMappedException($response->status(), (array) $response->json(), $e);
            });
    }

    private function resolveCreateUrl(array $providerConfig = []): string
    {
        $config = $providerConfig === [] ? (array) config('ai.providers.fashn') : $providerConfig;
        $runUrl = trim((string) ($config['run_url'] ?? ''));
        $baseUrl = trim((string) ($config['base_url'] ?? ''));

        if ($runUrl !== '') {
            return $runUrl;
        }

        if ($baseUrl !== '') {
            return $baseUrl;
        }

        throw new RuntimeException('FASHN run URL is missing. Set FASHN_RUN_URL or FASHN_BASE_URL.');
    }

    private function resolveStatusUrl(string $jobId, array $providerConfig = []): string
    {
        $config = $providerConfig === [] ? (array) config('ai.providers.fashn') : $providerConfig;
        $template = trim((string) ($config['status_url_template'] ?? ''));
        if ($template !== '') {
            return str_replace('{job_id}', $jobId, $template);
        }

        $createUrl = $this->resolveCreateUrl($providerConfig);
        if (str_ends_with($createUrl, '/run')) {
            return preg_replace('#/run$#', '/status/'.$jobId, $createUrl) ?? $createUrl;
        }

        if (str_ends_with($createUrl, '/jobs')) {
            return rtrim($createUrl, '/').'/'.$jobId;
        }

        throw new RuntimeException('FASHN status URL is missing. Set FASHN_STATUS_URL_TEMPLATE (example: https://api.fashn.ai/v1/status/{job_id}).');
    }

    private function normalizeStatus(string $status): string
    {
        return match (strtolower($status)) {
            'completed', 'success', 'succeeded', 'done' => 'completed',
            'starting', 'queued', 'pending', 'running', 'in_progress', 'processing' => 'processing',
            'failed', 'error', 'cancelled', 'canceled' => 'failed',
            default => 'processing',
        };
    }

    private function throwMappedException(int $statusCode, array $body, RequestException $exception): never
    {
        $providerError = (string) ($body['error_message'] ?? '');
        if ($providerError === '' && isset($body['error']) && is_string($body['error'])) {
            $providerError = $body['error'];
        }
        if ($providerError === '' && isset($body['error']) && is_array($body['error'])) {
            $providerError = (string) ($body['error']['message'] ?? '');
        }
        if ($providerError === '' && isset($body['detail']) && is_string($body['detail'])) {
            $providerError = $body['detail'];
        }
        $message = $providerError !== '' ? $providerError : 'FASHN provider request failed.';

        if ($statusCode === 422 || $statusCode === 400) {
            throw new RuntimeException('FASHN invalid payload: '.$message, previous: $exception);
        }

        if ($statusCode === 401 || $statusCode === 403) {
            throw new RuntimeException('FASHN unauthorized request: '.$message, previous: $exception);
        }

        if ($statusCode >= 500) {
            throw new RuntimeException('FASHN server error: '.$message, previous: $exception);
        }

        throw new RuntimeException($message, previous: $exception);
    }

    private function extractUsage(array $body): array
    {
        $usage = (array) ($body['usage'] ?? []);
        $billing = (array) ($body['billing'] ?? []);

        return array_filter([
            'tokens_used' => $usage['tokens_used'] ?? $usage['total_tokens'] ?? $body['tokens_used'] ?? null,
            'credits_used' => $usage['credits_used'] ?? $billing['credits_used'] ?? $body['credits_used'] ?? null,
            'estimated_cost' => $usage['estimated_cost'] ?? $billing['estimated_cost'] ?? $body['estimated_cost'] ?? null,
            'currency' => $usage['currency'] ?? $billing['currency'] ?? $body['currency'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');
    }

    private function resolveGenerationMode(string $qualityMode, array $providerConfig = []): string
    {
        $configured = strtolower(trim((string) ($providerConfig['generation_mode'] ?? '')));
        if (in_array($configured, ['balanced', 'quality'], true)) {
            return $configured;
        }

        return match ($qualityMode) {
            'ultra' => 'quality',
            default => 'balanced',
        };
    }

    private function resolveResolution(string $qualityMode, array $providerConfig = []): string
    {
        $configured = strtolower(trim((string) ($providerConfig['resolution'] ?? '')));
        if (in_array($configured, ['1k', '2k', '4k'], true)) {
            return $configured;
        }

        return match ($qualityMode) {
            'ultra' => '4k',
            'hd' => '2k',
            default => '1k',
        };
    }

    private function resolveOutputFormat(array $providerConfig = []): string
    {
        $configured = strtolower(trim((string) ($providerConfig['output_format'] ?? '')));

        if (in_array($configured, ['png', 'jpeg'], true)) {
            return $configured;
        }

        return 'png';
    }

    private function resolveV16Mode(array $providerConfig = []): string
    {
        $configured = strtolower(trim((string) ($providerConfig['v16_mode'] ?? '')));
        if (in_array($configured, ['performance', 'balanced', 'quality'], true)) {
            return $configured;
        }

        return 'balanced';
    }

    private function resolveV16NumSamples(array $providerConfig = []): int
    {
        $configured = (int) ($providerConfig['v16_num_samples'] ?? 1);

        return max(1, min(4, $configured));
    }

    private function resolveV16OutputFormat(array $providerConfig = []): string
    {
        $configured = strtolower(trim((string) ($providerConfig['v16_output_format'] ?? '')));
        if (in_array($configured, ['png', 'jpeg'], true)) {
            return $configured;
        }

        return 'png';
    }

    private function resolveTryOnMaxPrompt(array $input = []): string
    {
        $prompt = trim((string) ($input['product_ai_prompt'] ?? ''));

        return $prompt;
    }

    private function resolveV16Category(array $input = []): string
    {
        $category = strtolower(trim((string) ($input['product_ai_category'] ?? 'auto')));
        if (in_array($category, ['auto', 'tops', 'bottoms', 'one-pieces'], true)) {
            return $category;
        }

        return 'auto';
    }

    private function resolveV16GarmentPhotoType(array $input = []): string
    {
        $type = strtolower(trim((string) ($input['product_ai_garment_photo_type'] ?? 'auto')));
        if (in_array($type, ['auto', 'flat-lay', 'model'], true)) {
            return $type;
        }

        return 'auto';
    }

    private function resolveV16SegmentationFree(array $input = []): bool
    {
        if (array_key_exists('product_ai_segmentation_free', $input)) {
            return (bool) $input['product_ai_segmentation_free'];
        }

        return true;
    }

    private function isDummyMode(array $providerConfig = []): bool
    {
        if ($providerConfig !== []) {
            return (bool) ($providerConfig['dummy_enabled'] ?? false);
        }

        return (bool) config('ai.providers.fashn.dummy_enabled', false);
    }

    private function mergeProviderConfig(array $input): array
    {
        $config = (array) config('ai.providers.fashn');
        // API key must come from seller settings, never from global env/config fallback.
        $config['api_key'] = null;

        if (isset($input['provider_config']) && is_array($input['provider_config'])) {
            $override = $input['provider_config'];
            foreach (['api_key', 'model', 'generation_mode', 'resolution', 'output_format', 'v16_mode', 'v16_num_samples', 'v16_output_format', 'dummy_enabled', 'dummy_result_url'] as $field) {
                if (array_key_exists($field, $override) && $override[$field] !== null && $override[$field] !== '') {
                    $config[$field] = $override[$field];
                }
            }
        }

        return $config;
    }
}
