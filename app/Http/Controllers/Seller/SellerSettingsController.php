<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerAiSetting;
use App\Support\CurrentSellerResolver;
use App\Support\SellerSlug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class SellerSettingsController extends Controller
{
    public function __construct(private readonly CurrentSellerResolver $currentSellerResolver)
    {
    }

    public function index()
    {
        $seller = $this->currentSellerResolver->resolveForUser(auth()->user());
        $setting = $seller->aiSetting;

        return view('seller.settings.index', compact('seller', 'setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user());

        $payload = $request->validate([
            'seller_slug' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('sellers', 'slug')->ignore($seller->id),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (SellerSlug::isReserved((string) $value)) {
                        $fail('Seller URL menggunakan keyword yang tidak diizinkan.');
                    }
                },
            ],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'ui_locale' => ['required', 'in:id,en'],
            'fashn_api_key' => ['nullable', 'string', 'max:500'],
            'fashn_model' => ['required', 'in:tryon-v1.6,tryon-max'],
            'fashn_tryon_max_generation_mode' => ['nullable', 'in:balanced,quality'],
            'fashn_tryon_max_resolution' => ['nullable', 'in:1k,2k,4k'],
            'fashn_tryon_max_output_format' => ['nullable', 'in:png,jpeg'],
            'fashn_tryon_v16_mode' => ['nullable', 'in:performance,balanced,quality'],
            'fashn_tryon_v16_num_samples' => ['nullable', 'integer', 'min:1', 'max:4'],
            'fashn_tryon_v16_output_format' => ['nullable', 'in:png,jpeg'],
            'fashn_dummy_enabled' => ['nullable', 'boolean'],
            'fashn_dummy_result_url' => ['nullable', 'url', 'max:2048'],
            'fashn_dummy_model_image_url' => ['nullable', 'url', 'max:2048'],
            'public_generate_per_day' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'public_limit_per_ip_enabled' => ['nullable', 'boolean'],
            'public_limit_per_device_enabled' => ['nullable', 'boolean'],
        ]);

        $limitPerIpEnabled = (bool) ($payload['public_limit_per_ip_enabled'] ?? false);
        $limitPerDeviceEnabled = (bool) ($payload['public_limit_per_device_enabled'] ?? false);

        if (! $limitPerIpEnabled && ! $limitPerDeviceEnabled) {
            throw ValidationException::withMessages([
                'public_limit_per_ip_enabled' => 'Minimal salah satu limit harus aktif: Per IP atau Per Device.',
            ]);
        }

        $seller->slug = strtolower((string) $payload['seller_slug']);
        $seller->seo_title = isset($payload['seo_title']) && trim((string) $payload['seo_title']) !== ''
            ? trim((string) $payload['seo_title'])
            : null;
        $seller->seo_description = isset($payload['seo_description']) && trim((string) $payload['seo_description']) !== ''
            ? trim((string) $payload['seo_description'])
            : null;
        if ($request->hasFile('seo_logo_file')) {
            $file = $request->file('seo_logo_file');

            $targetDir = public_path('uploads/seller-logos');
            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $extension = strtolower((string) $file->getClientOriginalExtension());
            $filename = 'seller_'.$seller->id.'_'.time().'.'.$extension;
            $file->move($targetDir, $filename);

            // Cleanup old logo file if it was previously uploaded in the same folder.
            $oldLogoUrl = (string) ($seller->seo_logo_url ?? '');
            if ($oldLogoUrl !== '' && str_contains($oldLogoUrl, '/uploads/seller-logos/')) {
                $oldPath = parse_url($oldLogoUrl, PHP_URL_PATH);
                if (is_string($oldPath) && $oldPath !== '') {
                    $oldFullPath = public_path(ltrim($oldPath, '/'));
                    if (is_file($oldFullPath)) {
                        @unlink($oldFullPath);
                    }
                }
            }

            $seller->seo_logo_url = asset('uploads/seller-logos/'.$filename);
        }
        $seller->ui_locale = (string) $payload['ui_locale'];
        $seller->save();

        $setting = SellerAiSetting::query()->firstOrNew(['seller_id' => $seller->id]);
        $setting->provider_name = 'fashn';

        if (array_key_exists('fashn_api_key', $payload) && trim((string) $payload['fashn_api_key']) !== '') {
            $setting->fashn_api_key = $payload['fashn_api_key'];
        }

        $setting->fashn_model = $payload['fashn_model'];
        $setting->fashn_tryon_max_generation_mode = $payload['fashn_tryon_max_generation_mode'] ?? 'balanced';
        $setting->fashn_tryon_max_resolution = $payload['fashn_tryon_max_resolution'] ?? '1k';
        $setting->fashn_tryon_max_output_format = $payload['fashn_tryon_max_output_format'] ?? 'png';
        $setting->fashn_tryon_v16_mode = $payload['fashn_tryon_v16_mode'] ?? 'balanced';
        $setting->fashn_tryon_v16_num_samples = (int) ($payload['fashn_tryon_v16_num_samples'] ?? 1);
        $setting->fashn_tryon_v16_output_format = $payload['fashn_tryon_v16_output_format'] ?? 'png';

        $setting->fashn_dummy_enabled = (bool) ($payload['fashn_dummy_enabled'] ?? false);
        $setting->fashn_dummy_result_url = $payload['fashn_dummy_result_url'] ?? null;
        $setting->fashn_dummy_model_image_url = $payload['fashn_dummy_model_image_url'] ?? null;
        $setting->public_generate_per_day = isset($payload['public_generate_per_day'])
            ? (int) $payload['public_generate_per_day']
            : null;
        $setting->public_limit_per_ip_enabled = $limitPerIpEnabled;
        $setting->public_limit_per_device_enabled = $limitPerDeviceEnabled;
        $setting->save();

        return redirect()->route('seller.settings.index')->with('success', __('ui.settings.saved'));
    }

    public function testApiKey(Request $request): JsonResponse
    {
        $seller = $this->currentSellerResolver->resolveForUser($request->user());
        $setting = $seller->aiSetting;

        $payload = $request->validate([
            'fashn_api_key' => ['nullable', 'string', 'max:500'],
        ]);

        $candidateKey = trim((string) ($payload['fashn_api_key'] ?? ''));
        $apiKey = $candidateKey !== '' ? $candidateKey : trim((string) ($setting?->fashn_api_key ?? ''));

        if ($apiKey === '') {
            $this->saveTestResult($seller->id, false, 'API key kosong. Isi API key terlebih dahulu.');

            return response()->json([
                'ok' => false,
                'message' => 'API key kosong. Isi API key terlebih dahulu.',
            ], 422);
        }

        $creditsUrl = 'https://api.fashn.ai/v1/credits';
        $timeoutSeconds = max((int) config('ai.providers.fashn.timeout_seconds', 60), 5);

        try {
            $response = Http::acceptJson()
                ->withToken($apiKey)
                ->timeout($timeoutSeconds)
                ->get($creditsUrl);

            if ($response->status() === 401 || $response->status() === 403) {
                $this->saveTestResult($seller->id, false, 'API key tidak valid (unauthorized).');

                return response()->json([
                    'ok' => false,
                    'message' => 'API key tidak valid (unauthorized).',
                ], 422);
            }

            if (! $response->successful()) {
                $message = 'Gagal menguji API key. HTTP '.$response->status();
                $this->saveTestResult($seller->id, false, $message);

                return response()->json([
                    'ok' => false,
                    'message' => $message,
                ], 422);
            }

            $body = (array) $response->json();
            if ($candidateKey !== '') {
                $setting = SellerAiSetting::query()->firstOrNew(['seller_id' => $seller->id]);
                $setting->provider_name = 'fashn';
                $setting->fashn_api_key = $candidateKey;
                $setting->save();
            }
            $this->saveTestResult($seller->id, true, 'API key valid dan dapat mengakses endpoint credits.');

            return response()->json([
                'ok' => true,
                'message' => 'API key valid dan dapat mengakses endpoint credits.',
                'credits' => $body,
                'configured' => true,
            ]);
        } catch (\Throwable $exception) {
            $message = 'API key test gagal: '.$exception->getMessage();
            $this->saveTestResult($seller->id, false, $message);

            return response()->json([
                'ok' => false,
                'message' => $message,
            ], 422);
        }
    }

    private function saveTestResult(int $sellerId, bool $ok, string $message): void
    {
        $setting = SellerAiSetting::query()->firstOrNew(['seller_id' => $sellerId]);
        $setting->provider_name = $setting->provider_name ?: 'fashn';
        $setting->fashn_api_key_last_test_ok = $ok;
        $setting->fashn_api_key_last_test_message = $message;
        $setting->fashn_api_key_last_tested_at = Carbon::now();
        $setting->save();
    }
}
