<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.common.dashboard') }} - Try-On Commerce Studio</title>
    @php
        $dashboardFavicon = trim((string) ($seller->seo_logo_url ?? ''));
        $dashboardFaviconVersion = (string) ($seller->updated_at?->timestamp ?? time());
    @endphp
    @if($dashboardFavicon !== '')
        <link rel="icon" type="image/png" href="{{ $dashboardFavicon }}?v={{ urlencode($dashboardFaviconVersion) }}">
        <link rel="shortcut icon" href="{{ $dashboardFavicon }}?v={{ urlencode($dashboardFaviconVersion) }}">
        <link rel="apple-touch-icon" href="{{ $dashboardFavicon }}?v={{ urlencode($dashboardFaviconVersion) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Inter:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/seller-theme.css') }}">
</head>
<body class="dashboard-page">
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

@php
    $resolvePublicPath = static function (?string $path): ?string {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    };
@endphp

<div class="layout">
    <aside class="sidebar">
        <a class="menu-item active" href="{{ route('seller.dashboard') }}"><span>{{ __('ui.common.dashboard') }}</span></a>
        <a class="menu-item" href="{{ route('seller.products.index') }}"><span>{{ __('ui.common.products') }}</span></a>
        <a class="menu-item" href="{{ route('seller.settings.index') }}"><span>{{ __('ui.common.settings') }}</span></a>
    </aside>

    <main class="content">
        <header class="dashboard-hero">
            <h1>{{ __('ui.common.dashboard') }}</h1>
            <p class="dashboard-subtitle">{{ __('ui.dashboard.subtitle') }}</p>
        </header>

        <section class="cards">
            <article class="card">
                <div class="card-label">Total Products</div>
                <div class="card-value">{{ number_format($stats['total_products']) }}</div>
            </article>
            <article class="card credits-card">
                <div class="card-label">FASHN Credits</div>
                <div class="card-value">{{ number_format($stats['fashn_credits']['total'] ?? 0) }}</div>
                <div class="credit-breakdown">
                    <div class="credit-item">
                        <span>Subscription</span>
                        <strong>{{ number_format($stats['fashn_credits']['subscription'] ?? 0) }}</strong>
                    </div>
                    <div class="credit-item">
                        <span>On Demand</span>
                        <strong>{{ number_format($stats['fashn_credits']['on_demand'] ?? 0) }}</strong>
                    </div>
                </div>
            </article>
            <article class="card model-card">
                <div class="card-label">Model</div>
                <div class="model-picker" role="group" aria-label="Select model">
                    <button
                        type="button"
                        class="model-choice-btn {{ $stats['fashn_model'] === 'tryon-v1.6' ? 'is-active' : '' }}"
                        data-model-choice="tryon-v1.6"
                        aria-pressed="{{ $stats['fashn_model'] === 'tryon-v1.6' ? 'true' : 'false' }}"
                    >
                        FASHN Virtual Try-On v1.6
                    </button>
                    <button
                        type="button"
                        class="model-choice-btn {{ $stats['fashn_model'] === 'tryon-max' ? 'is-active' : '' }}"
                        data-model-choice="tryon-max"
                        aria-pressed="{{ $stats['fashn_model'] === 'tryon-max' ? 'true' : 'false' }}"
                    >
                        Try-On Max
                    </button>
                </div>
                <input type="hidden" id="dashboardModelSelect" value="{{ $stats['fashn_model'] }}">
                <div id="dashboardModelStatus" class="model-update-status"></div>
                <div class="credit-breakdown">
                    <div class="credit-item">
                        <span id="modelMetaLabel1">{{ $stats['fashn_model'] === 'tryon-v1.6' ? 'Mode' : 'Mode' }}</span>
                        <strong id="modelMetaValue1">
                            {{ $stats['fashn_model'] === 'tryon-v1.6'
                                ? ucfirst($stats['fashn_model_config']['mode'] ?? 'balanced')
                                : ucfirst($stats['fashn_model_config']['generation_mode'] ?? 'balanced') }}
                        </strong>
                    </div>
                    <div class="credit-item">
                        <span id="modelMetaLabel2">{{ $stats['fashn_model'] === 'tryon-v1.6' ? 'Samples' : 'Resolution' }}</span>
                        <strong id="modelMetaValue2">
                            {{ $stats['fashn_model'] === 'tryon-v1.6'
                                ? (int) ($stats['fashn_model_config']['samples'] ?? 1)
                                : strtoupper((string) ($stats['fashn_model_config']['resolution'] ?? '1k')) }}
                        </strong>
                    </div>
                    <div class="credit-item">
                        <span>Format</span>
                        <strong id="modelMetaValue3">
                            {{ $stats['fashn_model'] === 'tryon-v1.6'
                                ? strtoupper((string) ($stats['fashn_model_config']['format'] ?? 'png'))
                                : strtoupper((string) ($stats['fashn_model_config']['format'] ?? 'png')) }}
                        </strong>
                    </div>
                </div>
            </article>
        </section>

        <section class="split">
            <div class="panel dashboard-panel dashboard-recent-panel">
                <h2>{{ __('ui.dashboard.recent_tryon') }}</h2>
                <div class="table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Model</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Product</th>
                            <th>IP</th>
                            <th>Preview</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['recent_tryon'] as $session)
                            @php
                                $productImage = optional($session->product?->images?->firstWhere('is_primary', true) ?? $session->product?->images?->first())->image_url;
                                $modelImage = $resolvePublicPath($session->customer_photo_path);
                                $resultImage = $resolvePublicPath($session->result_path);
                                $sessionModel = is_string($session->provider_model) && trim($session->provider_model) !== ''
                                    ? trim($session->provider_model)
                                    : $stats['fashn_model'];
                                $statusClass = $session->status === 'failed' ? 'badge-failed' : (($session->status === 'processing' || $session->status === 'pending') ? 'badge-processing' : '');
                                $requestPayload = [
                                    'model' => $sessionModel,
                                    'product' => $session->product?->name,
                                    'source_channel' => $session->source_channel,
                                    'ip' => $session->ip_address,
                                    'inputs' => [
                                        'model_image' => $modelImage,
                                        'product_image' => $productImage,
                                    ],
                                ];
                                $responsePayload = [
                                    'id' => $session->provider_job_id ?: ('local-'.$session->id),
                                    'status' => $session->status,
                                    'error' => $session->error_message,
                                    'output' => $resultImage ? [$resultImage] : [],
                                    'token_cost' => (int) ($session->token_cost ?? 0),
                                ];
                            @endphp
                            <tr>
                                <td class="req-id">{{ $session->provider_job_id ?: ('local-'.$session->id) }}</td>
                                <td><span class="req-model">{{ $sessionModel }}</span></td>
                                <td>{{ $session->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <span class="badge {{ $statusClass }}">
                                        {{ ucfirst($session->status) }}
                                    </span>
                                </td>
                                <td>{{ $session->product?->name ?? '-' }}</td>
                                <td>{{ \App\Support\IpMasker::mask($session->ip_address) }}</td>
                                <td>
                                    @if($resultImage)
                                        <img class="preview-thumb" src="{{ $resultImage }}" alt="Preview">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="details-btn js-open-request-modal"
                                        data-request-id="{{ $session->provider_job_id ?: ('local-'.$session->id) }}"
                                        data-model="{{ $sessionModel }}"
                                        data-created="{{ $session->created_at->format('Y-m-d H:i:s') }}"
                                        data-status="{{ $session->status }}"
                                        data-credits="{{ (int) ($session->token_cost ?? 0) }}"
                                        data-product="{{ $session->product?->name ?? '-' }}"
                                        data-ip="{{ \App\Support\IpMasker::mask($session->ip_address) }}"
                                        data-model-image="{{ $modelImage }}"
                                        data-product-image="{{ $productImage }}"
                                        data-output-image="{{ $resultImage }}"
                                        data-request-json='@json($requestPayload)'
                                        data-response-json='@json($responsePayload)'
                                    >
                                        {{ __('ui.dashboard.details') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8">{{ __('ui.dashboard.no_requests') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </section>
    </main>
</div>

<div id="requestModal" class="modal-backdrop request-modal-backdrop" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-card request-modal-card">
        <div class="modal-header request-modal-header">
            <h3 class="modal-title">{{ __('ui.dashboard.request_details') }}</h3>
            <button type="button" id="requestModalClose" class="modal-close request-modal-close" aria-label="Close request details">&times;</button>
        </div>
        <div class="modal-tabs request-modal-tabs">
            <button type="button" class="tab-btn active" data-tab="tabRequestInfo">Request Info</button>
            <button type="button" class="tab-btn" data-tab="tabInput">Input</button>
            <button type="button" class="tab-btn" data-tab="tabOutput">Output</button>
        </div>
        <section id="tabRequestInfo" class="tab-content request-tab-content active">
            <div class="modal-grid request-modal-grid">
                <div class="modal-kv"><div class="k">Request ID</div><div class="v" id="mRequestId">-</div></div>
                <div class="modal-kv"><div class="k">Model</div><div class="v" id="mModel">-</div></div>
                <div class="modal-kv"><div class="k">Status</div><div class="v" id="mStatus">-</div></div>
                <div class="modal-kv"><div class="k">Credits</div><div class="v" id="mCredits">0</div></div>
                <div class="modal-kv"><div class="k">Product</div><div class="v" id="mProduct">-</div></div>
                <div class="modal-kv"><div class="k">IP</div><div class="v" id="mIp">-</div></div>
                <div class="modal-kv"><div class="k">Created at</div><div class="v" id="mCreated">-</div></div>
            </div>
        </section>
        <section id="tabInput" class="tab-content request-tab-content">
            <div class="image-grid request-image-grid">
                <div class="image-block">
                    <h4>Model</h4>
                    <img id="mModelImage" src="" alt="Model image">
                    <div id="mModelImageEmpty" class="image-empty" style="display:none;">No image</div>
                </div>
                <div class="image-block">
                    <h4>Product</h4>
                    <img id="mProductImage" src="" alt="Product image">
                    <div id="mProductImageEmpty" class="image-empty" style="display:none;">No image</div>
                </div>
            </div>
            <div class="json-box request-json-box"><pre id="mRequestJson">{}</pre></div>
        </section>
        <section id="tabOutput" class="tab-content request-tab-content">
            <div class="image-grid request-image-grid">
                <div class="image-block">
                    <h4>Output</h4>
                    <img id="mOutputImage" src="" alt="Output image">
                    <div id="mOutputImageEmpty" class="image-empty" style="display:none;">No output image</div>
                </div>
            </div>
            <div class="json-box request-json-box"><pre id="mResponseJson">{}</pre></div>
        </section>
    </div>
</div>

<script>
    const dashboardModelSelect = document.getElementById('dashboardModelSelect');
    const modelChoiceButtons = document.querySelectorAll('[data-model-choice]');
    const dashboardModelStatus = document.getElementById('dashboardModelStatus');
    const modelMetaLabel2 = document.getElementById('modelMetaLabel2');
    const modelMetaValue1 = document.getElementById('modelMetaValue1');
    const modelMetaValue2 = document.getElementById('modelMetaValue2');
    const modelMetaValue3 = document.getElementById('modelMetaValue3');
    const csrfToken = @json(csrf_token());

    const updateModelMeta = (model, config) => {
        if (model === 'tryon-v1.6') {
            modelMetaValue1.textContent = String((config.mode || 'balanced')).replace(/^./, (c) => c.toUpperCase());
            modelMetaLabel2.textContent = 'Samples';
            modelMetaValue2.textContent = String(config.samples ?? 1);
            modelMetaValue3.textContent = String((config.format || 'png')).toUpperCase();
            return;
        }

        modelMetaValue1.textContent = String((config.generation_mode || 'balanced')).replace(/^./, (c) => c.toUpperCase());
        modelMetaLabel2.textContent = 'Resolution';
        modelMetaValue2.textContent = String((config.resolution || '1k')).toUpperCase();
        modelMetaValue3.textContent = String((config.format || 'png')).toUpperCase();
    };

    const setActiveModelButton = (model) => {
        modelChoiceButtons.forEach((btn) => {
            const isActive = btn.dataset.modelChoice === model;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    };

    if (dashboardModelSelect && modelChoiceButtons.length > 0) {
        dashboardModelSelect.dataset.current = dashboardModelSelect.value;

        modelChoiceButtons.forEach((btn) => {
            btn.addEventListener('click', async () => {
                const selectedModel = btn.dataset.modelChoice;
                const previousModel = dashboardModelSelect.dataset.current || dashboardModelSelect.value;

                if (!selectedModel || selectedModel === previousModel) {
                    return;
                }

                modelChoiceButtons.forEach((item) => {
                    item.disabled = true;
                });
                setActiveModelButton(selectedModel);
                dashboardModelStatus.className = 'model-update-status';
                dashboardModelStatus.textContent = 'Updating model...';

                try {
                    const response = await fetch(@json(route('seller.dashboard.model.update')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            fashn_model: selectedModel,
                        }),
                    });

                    const payload = await response.json();
                    if (!response.ok || !payload.ok) {
                        throw new Error(payload.message || 'Gagal update model.');
                    }

                    dashboardModelSelect.value = payload.model;
                    dashboardModelSelect.dataset.current = payload.model;
                    setActiveModelButton(payload.model);
                    updateModelMeta(payload.model, payload.config || {});
                    dashboardModelStatus.className = 'model-update-status ok';
                    dashboardModelStatus.textContent = 'Model aktif berhasil diperbarui.';
                } catch (error) {
                    dashboardModelSelect.value = previousModel;
                    setActiveModelButton(previousModel);
                    dashboardModelStatus.className = 'model-update-status fail';
                    dashboardModelStatus.textContent = error.message || 'Gagal update model.';
                } finally {
                    modelChoiceButtons.forEach((item) => {
                        item.disabled = false;
                    });
                }
            });
        });
    }

    const requestModal = document.getElementById('requestModal');
    const requestModalClose = document.getElementById('requestModalClose');
    const detailButtons = document.querySelectorAll('.js-open-request-modal');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    const mRequestId = document.getElementById('mRequestId');
    const mModel = document.getElementById('mModel');
    const mStatus = document.getElementById('mStatus');
    const mCredits = document.getElementById('mCredits');
    const mProduct = document.getElementById('mProduct');
    const mIp = document.getElementById('mIp');
    const mCreated = document.getElementById('mCreated');
    const mModelImage = document.getElementById('mModelImage');
    const mProductImage = document.getElementById('mProductImage');
    const mOutputImage = document.getElementById('mOutputImage');
    const mModelImageEmpty = document.getElementById('mModelImageEmpty');
    const mProductImageEmpty = document.getElementById('mProductImageEmpty');
    const mOutputImageEmpty = document.getElementById('mOutputImageEmpty');
    const mRequestJson = document.getElementById('mRequestJson');
    const mResponseJson = document.getElementById('mResponseJson');

    const showImage = (imgEl, emptyEl, url) => {
        if (url && String(url).trim() !== '') {
            imgEl.src = url;
            imgEl.style.display = 'block';
            emptyEl.style.display = 'none';
        } else {
            imgEl.removeAttribute('src');
            imgEl.style.display = 'none';
            emptyEl.style.display = 'flex';
        }
    };

    detailButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            mRequestId.textContent = btn.dataset.requestId || '-';
            mModel.textContent = btn.dataset.model || '-';
            mStatus.textContent = btn.dataset.status || '-';
            mCredits.textContent = btn.dataset.credits || '0';
            mProduct.textContent = btn.dataset.product || '-';
            mIp.textContent = btn.dataset.ip || '-';
            mCreated.textContent = btn.dataset.created || '-';

            showImage(mModelImage, mModelImageEmpty, btn.dataset.modelImage || '');
            showImage(mProductImage, mProductImageEmpty, btn.dataset.productImage || '');
            showImage(mOutputImage, mOutputImageEmpty, btn.dataset.outputImage || '');

            let requestJson = {};
            let responseJson = {};
            try { requestJson = JSON.parse(btn.dataset.requestJson || '{}'); } catch (e) {}
            try { responseJson = JSON.parse(btn.dataset.responseJson || '{}'); } catch (e) {}
            mRequestJson.textContent = JSON.stringify(requestJson, null, 2);
            mResponseJson.textContent = JSON.stringify(responseJson, null, 2);

            tabButtons.forEach((tabBtn) => tabBtn.classList.remove('active'));
            tabContents.forEach((content) => content.classList.remove('active'));
            document.querySelector('[data-tab="tabRequestInfo"]').classList.add('active');
            document.getElementById('tabRequestInfo').classList.add('active');

            requestModal.classList.add('open');
            requestModal.setAttribute('aria-hidden', 'false');
        });
    });

    tabButtons.forEach((tabBtn) => {
        tabBtn.addEventListener('click', () => {
            const target = tabBtn.dataset.tab;
            tabButtons.forEach((x) => x.classList.remove('active'));
            tabContents.forEach((x) => x.classList.remove('active'));
            tabBtn.classList.add('active');
            document.getElementById(target).classList.add('active');
        });
    });

    const closeModal = () => {
        requestModal.classList.remove('open');
        requestModal.setAttribute('aria-hidden', 'true');
    };

    requestModalClose.addEventListener('click', closeModal);
    requestModal.addEventListener('click', (event) => {
        if (event.target === requestModal) {
            closeModal();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && requestModal.classList.contains('open')) {
            closeModal();
        }
    });
</script>
</body>
</html>
