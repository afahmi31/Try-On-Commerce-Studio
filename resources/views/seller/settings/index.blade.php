<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.settings.title') }} - Try-On Commerce Studio</title>
    @php
        $settingsFavicon = trim((string) ($seller->seo_logo_url ?? ''));
        $settingsFaviconVersion = (string) ($seller->updated_at?->timestamp ?? time());
    @endphp
    @if($settingsFavicon !== '')
        <link rel="icon" type="image/png" href="{{ $settingsFavicon }}?v={{ urlencode($settingsFaviconVersion) }}">
        <link rel="shortcut icon" href="{{ $settingsFavicon }}?v={{ urlencode($settingsFaviconVersion) }}">
        <link rel="apple-touch-icon" href="{{ $settingsFavicon }}?v={{ urlencode($settingsFaviconVersion) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Inter:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/seller-theme.css') }}">
</head>
<body class="settings-page">
<style>
    .settings-page .content {
        max-width: none;
        width: 100%;
    }

    .settings-page h1 {
        margin-bottom: 10px;
    }

    .settings-page .page-subtitle {
        margin: 0 0 22px;
        font-size: 14px;
        color: #4a5a6a;
    }

    .settings-page .cards-wrap {
        gap: 18px;
    }

    .settings-page .top-settings-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
        gap: 18px;
        align-items: stretch;
    }

    .settings-page .top-settings-grid .panel {
        margin: 0;
    }

    .settings-page .top-settings-left {
        display: flex;
        flex-direction: column;
        gap: 18px;
        min-height: 100%;
    }

    .settings-page .panel-generate-limit {
        flex: 1;
    }

    .settings-page .panel {
        border-radius: 14px;
        border-color: #d4dde7;
        box-shadow: 0 8px 22px rgba(20, 59, 100, 0.06);
        padding: 20px;
    }

    .settings-page .panel h2 {
        font-size: 30px;
        line-height: 1.2;
    }

    .settings-page .panel-head {
        margin-bottom: 16px;
    }

    .settings-page .row {
        margin-bottom: 12px;
    }

    .settings-page .row:last-child {
        margin-bottom: 0;
    }

    .settings-page .row > label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        line-height: 1.3;
        letter-spacing: 0.01em;
        color: #324457;
    }

    .settings-page input:not([type="radio"]),
    .settings-page select {
        height: 46px;
        border-radius: 10px;
        border-color: #c7d3df;
        background: #fbfdff;
        font-size: 15px;
    }

    .settings-page input::placeholder {
        color: #8091a3;
    }

    .settings-page .hint {
        margin-top: 6px;
        font-size: 12px;
        line-height: 1.45;
        color: #5f6f7f;
    }

    .settings-page .key-row {
        align-items: flex-end;
        gap: 12px;
    }

    .settings-page .key-input-wrap {
        flex: 1;
    }

    .settings-page .btn-secondary {
        height: 46px;
        border-radius: 10px;
        padding: 0 18px;
        white-space: nowrap;
    }

    .settings-page .status-badge {
        font-size: 12px;
        padding: 5px 12px;
    }

    .settings-page .status-ok {
        border-color: #93d1b9;
        background: #e7f8f1;
        color: #0d6f53;
    }

    .settings-page .status-empty {
        border-color: #dfccd3;
        background: #fff3f5;
        color: #a0475c;
    }

    .settings-page .checks-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 8px;
    }

    .settings-page .limit-toggle {
        margin-bottom: 0;
    }

    .settings-page .limit-toggle-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 0 14px;
        border: 1px solid #bfd0df;
        border-radius: 10px;
        background: #ffffff;
        color: #153450;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.1;
        cursor: pointer;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    }

    .settings-page .limit-toggle input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .settings-page .limit-toggle input[type="checkbox"]:checked + .limit-toggle-chip {
        border-color: #21759b;
        background: #f2f9fd;
        box-shadow: 0 0 0 2px rgba(33, 117, 155, 0.16);
    }

    .settings-page .panel-head .toggle-wrap {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 0;
    }

    .settings-page .panel-head .toggle-wrap > span:last-child {
        font-size: 13px;
        color: #324457;
    }

    .settings-page .store-profile-panel {
        width: 100%;
        max-width: 360px;
    }

    .settings-page .store-locale-grid {
        display: grid;
        grid-template-columns: repeat(2, 360px);
        gap: 18px;
        align-items: start;
        justify-content: start;
    }

    .settings-page .locale-card-title {
        margin: 0;
        font-size: 20px;
        line-height: 1.15;
        color: #163a5d;
    }

    .settings-page .locale-panel {
        width: 100%;
        max-width: 360px;
        justify-self: start;
    }

    .settings-page .store-profile-head {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .settings-page .store-profile-icon {
        width: 46px;
        height: 46px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #edf1f5;
        color: #19324d;
        flex: 0 0 auto;
    }

    .settings-page .store-profile-icon svg {
        width: 22px;
        height: 22px;
        stroke: currentColor;
        fill: none;
        stroke-width: 1.8;
    }

    .settings-page .store-profile-title {
        margin: 0;
        font-size: 20px;
        line-height: 1.15;
        color: #163a5d;
    }

    .settings-page .store-profile-divider {
        border: 0;
        height: 1px;
        background: #d7e0e8;
        margin: 16px 0;
    }

    .settings-page .store-logo-preview {
        width: 92px;
        height: 92px;
        margin: 0 auto 16px;
        border-radius: 999px;
        border: 1px solid #d5dee7;
        background: #eff3f7;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .settings-page .store-logo-upload {
        display: flex;
        width: 100%;
        justify-content: center;
        margin: 0 0 16px;
        cursor: pointer;
    }

    .settings-page .store-logo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .settings-page .store-logo-placeholder {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 2px solid #b9c7d5;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #9ab0c5;
        font-weight: 700;
        font-size: 16px;
    }

    .settings-page .submit-wrap {
        position: sticky;
        bottom: 14px;
        margin-top: 14px;
        display: flex;
        justify-content: flex-end;
        z-index: 3;
    }

    .settings-page .btn-save {
        min-width: 170px;
        height: 46px;
        border-radius: 12px;
        box-shadow: 0 10px 20px rgba(33, 117, 155, 0.2);
    }

    .settings-page .api-test-modal {
        width: min(460px, calc(100vw - 40px));
        border-radius: 16px;
        border: 1px solid #d4dde7;
        padding: 20px;
        background: #ffffff;
        box-shadow: 0 20px 40px rgba(20, 59, 100, 0.18);
    }

    .settings-page .api-test-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #1f3550;
    }

    .settings-page .api-test-message {
        font-size: 14px;
        color: #4f6378;
        margin-top: 10px;
    }

    .settings-page .api-test-close {
        margin-top: 16px;
        border-radius: 10px;
        padding: 10px 14px;
    }

    .settings-page .model-config-panel {
        padding: 0;
        overflow: hidden;
    }

    .settings-page .model-config-inner {
        padding: 20px;
    }

    .settings-page .model-config-divider {
        height: 1px;
        margin: 0;
        border: 0;
        background: linear-gradient(90deg, rgba(150, 170, 190, 0), rgba(150, 170, 190, 0.65) 20%, rgba(150, 170, 190, 0.65) 80%, rgba(150, 170, 190, 0));
    }

    .settings-page .subsection-title {
        margin: 0 0 12px;
        font-size: 18px;
        line-height: 1.3;
        font-weight: 600;
        color: #274563;
    }

    .settings-page .model-choice-group {
        display: flex;
        gap: 8px;
        margin-top: 6px;
    }

    .settings-page .model-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .settings-page .model-option {
        display: block;
    }

    .settings-page .model-option-card {
        display: block;
        width: 100%;
        border: 1px solid #cad7e4;
        border-radius: 10px;
        background: #ffffff;
        padding: 10px 12px 10px 38px;
        position: relative;
        cursor: pointer;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    }

    .settings-page .model-option-card::before {
        content: "";
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 13px;
        height: 13px;
        border-radius: 999px;
        border: 2px solid #9fb0c1;
        background: #fff;
    }

    .settings-page .model-option-title {
        display: block;
        margin: 0;
        font-size: 13px;
        line-height: 1.2;
        font-weight: 600;
        color: #153450;
    }

    .settings-page .model-option input[type="radio"]:checked + .model-option-card {
        border-color: #21759b;
        background: #f2f9fd;
        box-shadow: 0 0 0 2px rgba(33, 117, 155, 0.16);
    }

    .settings-page .model-option input[type="radio"]:checked + .model-option-card::before {
        border-color: #0e6f95;
        background: radial-gradient(circle at center, #0e6f95 0 4px, #ffffff 5px);
    }

    .settings-page .pill-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 6px;
    }

    .settings-page .pill-option {
        display: inline-block;
        margin-bottom: 0;
    }

    .settings-page .pill-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .settings-page .pill-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 0 14px;
        border: 1px solid #bfd0df;
        border-radius: 10px;
        background: #ffffff;
        color: #153450;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.1;
        cursor: pointer;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    }

    .settings-page .pill-option input[type="radio"]:checked + .pill-chip {
        border-color: #21759b;
        background: #f2f9fd;
        box-shadow: 0 0 0 2px rgba(33, 117, 155, 0.16);
    }

    @media (max-width: 900px) {
        .settings-page .top-settings-grid {
            grid-template-columns: 1fr;
        }

        .settings-page .panel-generate-limit {
            flex: 0 0 auto;
        }

        .settings-page .panel h2 {
            font-size: 24px;
        }

        .settings-page .store-locale-grid {
            grid-template-columns: 1fr;
        }

        .settings-page .submit-wrap {
            position: static;
        }
    }

    @media (max-width: 640px) {
        .settings-page .panel {
            padding: 16px;
        }

        .settings-page .model-config-inner {
            padding: 16px;
        }

        .settings-page .key-row {
            flex-direction: column;
            align-items: stretch;
        }

        .settings-page .btn-secondary {
            width: 100%;
        }
    }
</style>
<header class="topbar">
    <div class="brand">Try-On Commerce Studio</div>
    <nav class="topnav">
        @php
            $storeUrl = route('public.seller.page', ['seller_slug' => $seller->slug]);
        @endphp
        <a class="store-logo-link" href="{{ $storeUrl }}" target="_blank" rel="noopener noreferrer" title="Open Store: {{ $storeUrl }}">
            <svg viewBox="0 0 24 24" aria-hidden="true" class="store-icon-svg">
                <path d="M3 9l2-4h14l2 4"></path>
                <path d="M4 9h16v10a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9z"></path>
                <path d="M9 20v-5h6v5"></path>
            </svg>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">{{ __('ui.common.logout') }}</button>
        </form>
    </nav>
</header>
<div class="layout">
    <aside class="sidebar">
        <a class="menu-item" href="{{ route('seller.dashboard') }}"><span>{{ __('ui.common.dashboard') }}</span></a>
        <a class="menu-item" href="{{ route('seller.products.index') }}"><span>{{ __('ui.common.products') }}</span></a>
        <a class="menu-item active" href="{{ route('seller.settings.index') }}"><span>{{ __('ui.common.settings') }}</span></a>
    </aside>
    <main class="content">
        <h1>{{ __('ui.settings.title') }}</h1>
        <p class="page-subtitle">{{ __('ui.settings.subtitle') }}</p>
        @if(session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="flash-error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('seller.settings.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="cards-wrap">
                <div class="top-settings-grid">
                    <div class="top-settings-left">
                        <section class="panel panel-credentials">
                            <div class="panel-head">
                                <h2>FASHN AI Credentials</h2>
                                <span id="fashnStatusBadge" class="status-badge {{ $setting?->fashn_api_key ? 'status-ok' : 'status-empty' }}">
                                    {{ $setting?->fashn_api_key ? 'Configured' : 'Not Configured' }}
                                </span>
                            </div>
                            <div class="row key-row">
                                <div class="key-input-wrap">
                                    <label>FASHN API Key</label>
                                    <input type="text" id="fashnApiKeyInput" name="fashn_api_key" placeholder="fa-xxxxxx">
                                </div>
                                <button type="button" id="testApiKeyBtn" class="btn-secondary">Test API Key</button>
                            </div>
                            <div id="apiTestOverlay" class="api-test-overlay" aria-live="polite">
                                <div id="apiTestModal" class="api-test-modal">
                                    <div id="apiTestTitle" class="api-test-title">Testing API Key</div>
                                    <div id="apiTestDots" class="dots">
                                        <span class="dot"></span>
                                        <span class="dot"></span>
                                        <span class="dot"></span>
                                        <span class="dot"></span>
                                        <span class="dot"></span>
                                    </div>
                                    <div id="apiTestMessage" class="api-test-message">Memvalidasi API key ke FASHN endpoint credits...</div>
                                    <button type="button" id="apiTestCloseBtn" class="api-test-close">Close</button>
                                </div>
                            </div>
                        </section>

                        <section class="panel panel-generate-limit">
                            <h2>Generate Limit</h2>
                            @php
                                $publicGeneratePerDay = (int) old('public_generate_per_day', $setting?->public_generate_per_day ?? config('tryon.public_limits.generate_per_day', 3));
                                $publicLimitPerIpEnabled = (int) old('public_limit_per_ip_enabled', isset($setting) && $setting !== null ? (int) ($setting->public_limit_per_ip_enabled ?? 1) : 1);
                                $publicLimitPerDeviceEnabled = (int) old('public_limit_per_device_enabled', isset($setting) && $setting !== null ? (int) ($setting->public_limit_per_device_enabled ?? 1) : 1);
                            @endphp
                            <div class="row">
                                <label>Generate Per Day</label>
                                <input type="text" name="public_generate_per_day" value="{{ $publicGeneratePerDay }}" inputmode="numeric" pattern="[0-9]*">
                            </div>
                            <div class="checks-wrap">
                                <label class="limit-toggle">
                                    <input type="checkbox" name="public_limit_per_ip_enabled" value="1" {{ $publicLimitPerIpEnabled ? 'checked' : '' }}>
                                    <span class="limit-toggle-chip">Per IP</span>
                                </label>
                                <label class="limit-toggle">
                                    <input type="checkbox" name="public_limit_per_device_enabled" value="1" {{ $publicLimitPerDeviceEnabled ? 'checked' : '' }}>
                                    <span class="limit-toggle-chip">Per Device</span>
                                </label>
                            </div>
                        </section>
                    </div>

                    <section class="panel model-config-panel">
                        <div class="model-config-inner">
                        <h2>FASHN Model</h2>
                        <div class="row">
                            <label>Pilih Model</label>
                            @php
                                $selectedModel = old('fashn_model', $setting?->fashn_model ?: config('ai.providers.fashn.model', 'tryon-max'));
                            @endphp
                            <div class="model-choice-group" role="radiogroup" aria-label="Pilih model FASHN">
                                <label class="model-option">
                                    <input type="radio" name="fashn_model" value="tryon-v1.6" {{ $selectedModel === 'tryon-v1.6' ? 'checked' : '' }} required>
                                    <span class="model-option-card">
                                        <span class="model-option-title">FASHN Virtual Try-On v1.6</span>
                                    </span>
                                </label>
                                <label class="model-option">
                                    <input type="radio" name="fashn_model" value="tryon-max" {{ $selectedModel === 'tryon-max' ? 'checked' : '' }}>
                                    <span class="model-option-card">
                                        <span class="model-option-title">Try-On Max</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                        <hr class="model-config-divider">

                        <div class="model-config-inner" id="panelTryonMaxConfig">
                            <h3 class="subsection-title">Try-On Max Config</h3>
                            @php
                                $selectedGenerationMode = old('fashn_tryon_max_generation_mode', $setting?->fashn_tryon_max_generation_mode ?: 'balanced');
                                $selectedResolution = old('fashn_tryon_max_resolution', $setting?->fashn_tryon_max_resolution ?: '1k');
                                $selectedOutputFormat = old('fashn_tryon_max_output_format', $setting?->fashn_tryon_max_output_format ?: 'png');
                            @endphp
                            <div class="row">
                                <label>Generation Mode</label>
                                <div class="pill-group" id="fashnGenerationModeSelect">
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_max_generation_mode" value="balanced" {{ $selectedGenerationMode === 'balanced' ? 'checked' : '' }}>
                                        <span class="pill-chip">Balanced</span>
                                    </label>
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_max_generation_mode" value="quality" {{ $selectedGenerationMode === 'quality' ? 'checked' : '' }}>
                                        <span class="pill-chip">Quality</span>
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <label>Resolution</label>
                                <div class="pill-group" id="fashnResolutionSelect">
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_max_resolution" value="1k" {{ $selectedResolution === '1k' ? 'checked' : '' }}>
                                        <span class="pill-chip">1K</span>
                                    </label>
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_max_resolution" value="2k" {{ $selectedResolution === '2k' ? 'checked' : '' }}>
                                        <span class="pill-chip">2K</span>
                                    </label>
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_max_resolution" value="4k" {{ $selectedResolution === '4k' ? 'checked' : '' }}>
                                        <span class="pill-chip">4K</span>
                                    </label>
                                </div>
                                <div class="hint" id="fashnCreditUseHint"></div>
                            </div>
                            <div class="row">
                                <label>Output Format</label>
                                <div class="pill-group">
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_max_output_format" value="png" {{ $selectedOutputFormat === 'png' ? 'checked' : '' }}>
                                        <span class="pill-chip">PNG</span>
                                    </label>
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_max_output_format" value="jpeg" {{ $selectedOutputFormat === 'jpeg' ? 'checked' : '' }}>
                                        <span class="pill-chip">JPEG</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="model-config-divider" id="dividerTryonV16">

                        <div class="model-config-inner" id="panelTryonV16Config">
                            <h3 class="subsection-title">Try-On v1.6 Config</h3>
                            @php
                                $selectedV16Mode = old('fashn_tryon_v16_mode', $setting?->fashn_tryon_v16_mode ?: 'balanced');
                                $selectedV16NumSamples = (int) old('fashn_tryon_v16_num_samples', (int) ($setting?->fashn_tryon_v16_num_samples ?: 1));
                                $selectedV16OutputFormat = old('fashn_tryon_v16_output_format', $setting?->fashn_tryon_v16_output_format ?: 'png');
                            @endphp
                            <div class="row">
                                <label>Mode</label>
                                <div class="pill-group">
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_v16_mode" value="performance" {{ $selectedV16Mode === 'performance' ? 'checked' : '' }}>
                                        <span class="pill-chip">Performance</span>
                                    </label>
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_v16_mode" value="balanced" {{ $selectedV16Mode === 'balanced' ? 'checked' : '' }}>
                                        <span class="pill-chip">Balanced</span>
                                    </label>
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_v16_mode" value="quality" {{ $selectedV16Mode === 'quality' ? 'checked' : '' }}>
                                        <span class="pill-chip">Quality</span>
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <label>Number of Samples</label>
                                <div class="pill-group" id="fashnV16NumSamplesSelect">
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_v16_num_samples" value="1" {{ $selectedV16NumSamples === 1 ? 'checked' : '' }}>
                                        <span class="pill-chip">1</span>
                                    </label>
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_v16_num_samples" value="2" {{ $selectedV16NumSamples === 2 ? 'checked' : '' }}>
                                        <span class="pill-chip">2</span>
                                    </label>
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_v16_num_samples" value="3" {{ $selectedV16NumSamples === 3 ? 'checked' : '' }}>
                                        <span class="pill-chip">3</span>
                                    </label>
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_v16_num_samples" value="4" {{ $selectedV16NumSamples === 4 ? 'checked' : '' }}>
                                        <span class="pill-chip">4</span>
                                    </label>
                                </div>
                                <div class="hint" id="fashnV16CreditUseHint"></div>
                            </div>
                            <div class="row">
                                <label>Output Format</label>
                                <div class="pill-group">
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_v16_output_format" value="png" {{ $selectedV16OutputFormat === 'png' ? 'checked' : '' }}>
                                        <span class="pill-chip">PNG</span>
                                    </label>
                                    <label class="pill-option">
                                        <input type="radio" name="fashn_tryon_v16_output_format" value="jpeg" {{ $selectedV16OutputFormat === 'jpeg' ? 'checked' : '' }}>
                                        <span class="pill-chip">JPEG</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="panel">
                    <div class="panel-head">
                        <h2>Dummy</h2>
                        <label class="toggle-wrap">
                            <input type="checkbox" name="fashn_dummy_enabled" value="1" {{ old('fashn_dummy_enabled', (int)($setting?->fashn_dummy_enabled ?? 0)) ? 'checked' : '' }}>
                            <span class="toggle-switch"></span>
                            <span>Enabled</span>
                        </label>
                    </div>
                    <div class="row">
                        <label>Dummy Result URL</label>
                        <input type="url" name="fashn_dummy_result_url" value="{{ old('fashn_dummy_result_url', $setting?->fashn_dummy_result_url ?: '') }}">
                    </div>
                    <div class="row">
                        <label>Dummy Model Image URL</label>
                        <input type="url" name="fashn_dummy_model_image_url" value="{{ old('fashn_dummy_model_image_url', $setting?->fashn_dummy_model_image_url ?: '') }}">
                    </div>
                </section>

                <div class="store-locale-grid">
                    @php
                        $sellerSlug = old('seller_slug', $seller->slug);
                        $sellerSeoTitle = old('seo_title', $seller->seo_title ?? '');
                        $sellerSeoDescription = old('seo_description', $seller->seo_description ?? '');
                        $sellerSeoLogoUrl = trim((string) ($seller->seo_logo_url ?? ''));
                        $storeUrlPreview = url('/' . $sellerSlug);
                        $selectedLocale = old('ui_locale', $seller->ui_locale ?? 'id');
                    @endphp
                        <section class="panel store-profile-panel">
                            <div class="store-profile-head">
                                <div class="store-profile-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M3 9l2-4h14l2 4"></path>
                                        <path d="M4 9h16v10a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9z"></path>
                                        <path d="M9 20v-5h6v5"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="store-profile-title">{{ __('ui.settings.store_profile_title') }}</h2>
                                </div>
                            </div>
                            <hr class="store-profile-divider">
                            <label class="store-logo-upload" for="storeLogoFileInput" title="Klik untuk upload logo">
                                <div class="store-logo-preview" aria-hidden="true">
                                    @if($sellerSeoLogoUrl)
                                        <img id="storeLogoPreviewImage" src="{{ $sellerSeoLogoUrl }}" alt="Store Logo Preview">
                                        <span id="storeLogoPlaceholder" class="store-logo-placeholder" style="display:none;">IMG</span>
                                    @else
                                        <img id="storeLogoPreviewImage" src="" alt="Store Logo Preview" style="display:none;">
                                        <span id="storeLogoPlaceholder" class="store-logo-placeholder">IMG</span>
                                    @endif
                                </div>
                            </label>
                            <input type="file" id="storeLogoFileInput" name="seo_logo_file" accept=".jpg,.jpeg,.png,.webp" style="display:none;">
                            <div class="row">
                                <label>URL Store</label>
                                <input type="text" name="seller_slug" value="{{ $sellerSlug }}" placeholder="contoh: ceriakid" required>
                                <div class="hint">URL : {{ $storeUrlPreview }}</div>
                            </div>
                            <div class="row">
                                <label>Nama Toko</label>
                                <input type="text" name="seo_title" value="{{ $sellerSeoTitle }}" placeholder="Judul SEO halaman seller">
                            </div>
                            <div class="row">
                                <label>Description</label>
                                <input type="text" name="seo_description" value="{{ $sellerSeoDescription }}" placeholder="Deskripsi SEO halaman seller">
                            </div>
                        </section>

                        <section class="panel locale-panel">
                            <h2 class="locale-card-title">{{ __('ui.settings.language') }}</h2>
                            <div class="hint">{{ __('ui.settings.language_help') }}</div>
                            <div class="row" style="margin-top: 12px;">
                                <div class="pill-group" role="radiogroup" aria-label="{{ __('ui.settings.language') }}">
                                    <label class="pill-option">
                                        <input type="radio" name="ui_locale" value="id" {{ $selectedLocale === 'id' ? 'checked' : '' }} required>
                                        <span class="pill-chip">{{ __('ui.settings.language_options.id') }}</span>
                                    </label>
                                    <label class="pill-option">
                                        <input type="radio" name="ui_locale" value="en" {{ $selectedLocale === 'en' ? 'checked' : '' }}>
                                        <span class="pill-chip">{{ __('ui.settings.language_options.en') }}</span>
                                    </label>
                                </div>
                            </div>
                        </section>
                </div>
            </div>
            <div class="submit-wrap">
                <button type="submit" class="btn btn-save">{{ __('ui.common.save') }} Settings</button>
            </div>
        </form>
    </main>
</div>
<script>
    const testApiKeyBtn = document.getElementById('testApiKeyBtn');
    const apiKeyInput = document.getElementById('fashnApiKeyInput');
    const statusBadge = document.getElementById('fashnStatusBadge');
    const overlay = document.getElementById('apiTestOverlay');
    const overlayModal = document.getElementById('apiTestModal');
    const overlayTitle = document.getElementById('apiTestTitle');
    const overlayDots = document.getElementById('apiTestDots');
    const overlayMessage = document.getElementById('apiTestMessage');
    const overlayCloseBtn = document.getElementById('apiTestCloseBtn');
    const generationModeOptions = document.querySelectorAll('input[name="fashn_tryon_max_generation_mode"]');
    const resolutionOptions = document.querySelectorAll('input[name="fashn_tryon_max_resolution"]');
    const creditUseHint = document.getElementById('fashnCreditUseHint');
    const modelRadios = document.querySelectorAll('input[name="fashn_model"]');
    const panelTryonMaxConfig = document.getElementById('panelTryonMaxConfig');
    const panelTryonV16Config = document.getElementById('panelTryonV16Config');
    const dividerTryonV16 = document.getElementById('dividerTryonV16');
    const v16NumSampleOptions = document.querySelectorAll('input[name="fashn_tryon_v16_num_samples"]');
    const v16CreditUseHint = document.getElementById('fashnV16CreditUseHint');
    const storeLogoFileInput = document.getElementById('storeLogoFileInput');
    const storeLogoPreviewImage = document.getElementById('storeLogoPreviewImage');
    const storeLogoPlaceholder = document.getElementById('storeLogoPlaceholder');
    const csrfToken = document.querySelector('input[name=\"_token\"]')?.value || '';

    function updateCreditUseHint() {
        if (!generationModeOptions.length || !resolutionOptions.length || !creditUseHint) {
            return;
        }

        const generationMode = document.querySelector('input[name="fashn_tryon_max_generation_mode"]:checked')?.value === 'quality' ? 'quality' : 'balanced';
        const resolution = document.querySelector('input[name="fashn_tryon_max_resolution"]:checked')?.value || '1k';
        const creditTable = {
            balanced: { '1k': 2, '2k': 3, '4k': 4 },
            quality: { '1k': 3, '2k': 4, '4k': 5 },
        };
        const creditUse = creditTable[generationMode]?.[resolution] ?? 2;
        creditUseHint.textContent = `Credit Use: ${creditUse} credits / image`;
    }

    function updateV16CreditUseHint() {
        if (!v16NumSampleOptions.length || !v16CreditUseHint) {
            return;
        }

        const selectedSamples = document.querySelector('input[name="fashn_tryon_v16_num_samples"]:checked')?.value || '1';
        const samples = parseInt(selectedSamples, 10);
        const safeSamples = Number.isNaN(samples) ? 1 : Math.max(1, Math.min(4, samples));
        v16CreditUseHint.textContent = `Credit Use: ${safeSamples} credits / request (1 credit / image)`;
    }

    function syncModelConfigPanels() {
        if (!modelRadios.length || !panelTryonMaxConfig || !panelTryonV16Config) {
            return;
        }

        const selectedModel = document.querySelector('input[name="fashn_model"]:checked');
        const model = selectedModel ? selectedModel.value : 'tryon-max';
        const isV16 = model === 'tryon-v1.6';
        panelTryonMaxConfig.style.display = isV16 ? 'none' : '';
        panelTryonV16Config.style.display = isV16 ? '' : 'none';
        if (dividerTryonV16) {
            dividerTryonV16.style.display = isV16 ? '' : 'none';
        }
    }

    function showOverlayLoading() {
        overlay.classList.add('visible');
        overlayModal.classList.remove('success', 'failed');
        overlayTitle.textContent = 'Testing API Key';
        overlayMessage.textContent = 'Memvalidasi API key ke FASHN endpoint credits...';
        overlayDots.style.display = 'flex';
        overlayCloseBtn.classList.remove('visible');
    }

    function showOverlayResult(ok, message) {
        overlayModal.classList.remove('success', 'failed');
        overlayModal.classList.add(ok ? 'success' : 'failed');
        overlayTitle.textContent = ok ? 'API Key Valid' : 'API Key Tidak Valid';
        overlayMessage.textContent = message || 'Tidak ada response message.';
        overlayDots.style.display = 'none';
        overlayCloseBtn.classList.add('visible');
    }

    function setConfiguredBadge(configured) {
        if (!statusBadge) {
            return;
        }
        statusBadge.classList.remove('status-ok', 'status-empty');
        statusBadge.classList.add(configured ? 'status-ok' : 'status-empty');
        statusBadge.textContent = configured ? 'Configured' : 'Not Configured';
    }

    if (overlayCloseBtn) {
        overlayCloseBtn.addEventListener('click', () => {
            overlay.classList.remove('visible');
        });
    }

    if (testApiKeyBtn) {
        testApiKeyBtn.addEventListener('click', async () => {
            showOverlayLoading();
            testApiKeyBtn.disabled = true;

            try {
                const response = await fetch(@json(route('seller.settings.test-api-key')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        fashn_api_key: apiKeyInput ? apiKeyInput.value : '',
                    }),
                });

                const payload = await response.json();
                showOverlayResult(!!payload.ok, payload.message || 'Tidak ada response message.');
                if (payload.ok) {
                    setConfiguredBadge(true);
                }
            } catch (error) {
                showOverlayResult(false, 'Gagal test API key: ' + (error.message || 'unknown error'));
            } finally {
                testApiKeyBtn.disabled = false;
            }
        });
    }

    generationModeOptions.forEach((radio) => radio.addEventListener('change', updateCreditUseHint));
    resolutionOptions.forEach((radio) => radio.addEventListener('change', updateCreditUseHint));
    v16NumSampleOptions.forEach((radio) => radio.addEventListener('change', updateV16CreditUseHint));

    modelRadios.forEach((radio) => {
        radio.addEventListener('change', syncModelConfigPanels);
    });

    if (storeLogoFileInput && storeLogoPreviewImage) {
        storeLogoFileInput.addEventListener('change', () => {
            const file = storeLogoFileInput.files && storeLogoFileInput.files[0];
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                const src = event.target && typeof event.target.result === 'string' ? event.target.result : '';
                if (!src) {
                    return;
                }
                storeLogoPreviewImage.src = src;
                storeLogoPreviewImage.style.display = 'block';
                if (storeLogoPlaceholder) {
                    storeLogoPlaceholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        });
    }

    updateCreditUseHint();
    updateV16CreditUseHint();
    syncModelConfigPanels();
</script>
</body>
</html>
