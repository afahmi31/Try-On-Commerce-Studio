<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.products_page.title') }} - Try-On Commerce Studio</title>
    @php
        $productsFavicon = trim((string) ($seller->seo_logo_url ?? ''));
        $productsFaviconVersion = (string) ($seller->updated_at?->timestamp ?? time());
    @endphp
    @if($productsFavicon !== '')
        <link rel="icon" type="image/png" href="{{ $productsFavicon }}?v={{ urlencode($productsFaviconVersion) }}">
        <link rel="shortcut icon" href="{{ $productsFavicon }}?v={{ urlencode($productsFaviconVersion) }}">
        <link rel="apple-touch-icon" href="{{ $productsFavicon }}?v={{ urlencode($productsFaviconVersion) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Inter:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/seller-theme.css') }}">
</head>
<body class="products-page">
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
        <a class="menu-item active" href="{{ route('seller.products.index') }}"><span>{{ __('ui.common.products') }}</span></a>
        <a class="menu-item" href="{{ route('seller.settings.index') }}"><span>{{ __('ui.common.settings') }}</span></a>
    </aside>

    <main class="content">
        <section class="products-hero">
            <div>
                <h1>{{ __('ui.products_page.title') }}</h1>
                <p class="products-hero-subtitle">{{ __('ui.products_page.subtitle') }}</p>
            </div>
            <div class="products-toolbar">
                <form method="GET" action="{{ route('seller.products.index') }}" class="products-toolbar-form">
                    <div class="search-input-wrap">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M21 21l-4.35-4.35"></path>
                            <circle cx="11" cy="11" r="6"></circle>
                        </svg>
                        <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="{{ __('ui.products_page.search_placeholder') }}">
                    </div>
                    <details class="filter-popover">
                        <summary class="btn btn-ghost toolbar-btn" aria-label="Filter">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 6h16"></path>
                                <path d="M7 12h10"></path>
                                <path d="M10 18h4"></path>
                            </svg>
                            <span>Filter</span>
                        </summary>
                        <div class="filter-popover-panel">
                            <label for="statusFilter">Status</label>
                            <select id="statusFilter" name="status" aria-label="Filter status">
                                <option value="all" @selected(($status ?? 'all') === 'all')>Semua Status</option>
                                <option value="active" @selected(($status ?? 'all') === 'active')>Active</option>
                                <option value="inactive" @selected(($status ?? 'all') === 'inactive')>Inactive</option>
                            </select>
                            <label for="perPageFilter">Per Page</label>
                            <select id="perPageFilter" name="per_page" aria-label="Jumlah data per halaman">
                                <option value="10" @selected(($perPage ?? 20) === 10)>10</option>
                                <option value="20" @selected(($perPage ?? 20) === 20)>20</option>
                                <option value="50" @selected(($perPage ?? 20) === 50)>50</option>
                            </select>
                            <label for="aiCategoryFilter">AI Category</label>
                            <select id="aiCategoryFilter" name="ai_category" aria-label="Filter AI category">
                                <option value="all" @selected(($aiCategory ?? 'all') === 'all')>Semua AI Category</option>
                                <option value="auto" @selected(($aiCategory ?? 'all') === 'auto')>auto</option>
                                <option value="tops" @selected(($aiCategory ?? 'all') === 'tops')>tops</option>
                                <option value="bottoms" @selected(($aiCategory ?? 'all') === 'bottoms')>bottoms</option>
                                <option value="one-pieces" @selected(($aiCategory ?? 'all') === 'one-pieces')>one-pieces</option>
                            </select>
                            <label for="photoTypeFilter">Photo Type</label>
                            <select id="photoTypeFilter" name="photo_type" aria-label="Filter photo type">
                                <option value="all" @selected(($photoType ?? 'all') === 'all')>Semua Photo Type</option>
                                <option value="auto" @selected(($photoType ?? 'all') === 'auto')>auto</option>
                                <option value="flat-lay" @selected(($photoType ?? 'all') === 'flat-lay')>flat-lay</option>
                                <option value="model" @selected(($photoType ?? 'all') === 'model')>model</option>
                            </select>
                            <button class="btn btn-primary filter-apply-btn" type="submit">Apply Filter</button>
                        </div>
                    </details>
                </form>
                <button class="btn btn-primary toolbar-add-btn" type="button" onclick="openCreateModal()">+ {{ __('ui.products_page.add') }}</button>
            </div>
        </section>

        @if(session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="flash flash-error">
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        <section class="panel products-panel">
            <div class="table-wrap">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Name</th>
                        <th>AI Category</th>
                        <th>Photo Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first(); @endphp
                        <tr>
                            <td data-label="ID"><span class="cell-id">#{{ $product->id }}</span></td>
                            <td data-label="Product">
                                @if($primaryImage)
                                    <img src="{{ $primaryImage->image_url }}" alt="{{ $product->name }}" class="thumb">
                                @else
                                    <div class="thumb-fallback" aria-label="No image">IMG</div>
                                @endif
                            </td>
                            <td data-label="Name">
                                <div class="product-name">{{ $product->name }}</div>
                                <div class="product-slug-row">
                                    <code class="slug-chip">{{ $product->slug }}</code>
                                    <a
                                        class="slug-link-icon"
                                        href="{{ route('public.seller.page', ['seller_slug' => $seller->slug, 'product_ref' => $product->slug]) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        title="Buka produk di halaman store"
                                        aria-label="Buka produk di halaman store"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M14 4h6v6"></path>
                                            <path d="M10 14L20 4"></path>
                                            <path d="M20 14v6H4V4h6"></path>
                                        </svg>
                                    </a>
                                </div>
                                @if($product->sku)
                                    <div class="product-meta">SKU: {{ $product->sku }}</div>
                                @endif
                            </td>
                            <td data-label="AI Category">
                                <span class="ai-category-badge">{{ $product->ai_category ?? 'auto' }}</span>
                            </td>
                            <td data-label="Photo Type">
                                <span class="ai-photo-type-badge">{{ $product->ai_garment_photo_type ?? 'auto' }}</span>
                            </td>
                            <td data-label="Status">
                                <span class="status-badge {{ $product->status === 'inactive' ? 'status-inactive' : '' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td data-label="Actions">
                                <div class="actions">
                                    <button class="btn btn-ghost" type="button" onclick="openEditModal({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($product->sku ?? '') }}', '{{ addslashes($product->category ?? '') }}', '{{ $product->status }}', '{{ addslashes($primaryImage?->image_url ?? '') }}', '{{ addslashes($product->ai_prompt ?? '') }}', '{{ addslashes($product->ai_category ?? 'auto') }}', '{{ addslashes($product->ai_garment_photo_type ?? 'auto') }}', '{{ (int) ($product->ai_segmentation_free ?? true) }}')">Edit</button>
                                    <form method="POST" action="{{ route('seller.products.destroy', $product->id) }}" onsubmit="return openDeleteConfirm(event, this);" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-ghost" type="submit" style="color:var(--danger); border-color: rgba(248,113,113,.4);">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">{{ __('ui.products_page.no_products') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="products-footer-row">
                <div class="products-footer-meta">
                    Menampilkan {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk
                </div>
                <div class="pagination-wrap">{{ $products->links() }}</div>
            </div>
        </section>
    </main>
</div>

<div id="createModal" class="modal" onclick="closeOnBackdrop(event, 'createModal')">
    <div class="modal-card">
        <div class="modal-head">
            <h3 class="modal-title">{{ __('ui.products_page.create_title') }}</h3>
            <div class="modal-head-right">
                <label class="status-toggle">
                    <span>{{ __('ui.products_page.status') }}</span>
                    <input id="createStatusToggle" type="checkbox" checked>
                    <span class="status-toggle-switch"></span>
                    <span id="createStatusLabel" class="status-toggle-label">{{ __('ui.common.active') }}</span>
                </label>
                <button class="close-btn" type="button" onclick="closeModal('createModal')">{{ __('ui.common.close') }}</button>
            </div>
        </div>
        <form method="POST" action="{{ route('seller.products.store') }}" enctype="multipart/form-data">
            @csrf
            <input id="createStatus" type="hidden" name="status" value="active">
            <div class="product-form-grid">
                <div class="product-left-col">
                    <div class="preview-wrap">
                        <img id="createImagePreview" class="preview-img" alt="Create preview">
                        <button class="preview-change-overlay" type="button" onclick="document.getElementById('createImageFile').click()">{{ __('ui.products_page.change_image') }}</button>
                    </div>
                    <input id="createImageFile" type="file" name="image" accept="image/*" style="display:none;">
                    <div class="url-label">{{ __('ui.products_page.replace_public_url') }}</div>
                    <input id="createImageUrl" type="url" name="image_url" placeholder="https://...">
                </div>
                <div class="product-right-col">
                    <section class="form-section product-info-section">
                        <div class="form-section-head">
                            <h4>{{ __('ui.products_page.product_info_title') }}</h4>
                            <p>{{ __('ui.products_page.product_info_create_help') }}</p>
                        </div>
                        <div class="field"><label>{{ __('ui.products_page.product_name') }}</label><input id="createName" name="name" required></div>
                        <div class="field"><label>SKU</label><input name="sku"></div>
                        <div class="field"><label>{{ __('ui.products_page.category') }}</label><input id="createCategory" name="category" placeholder="{{ __('ui.products_page.category_placeholder') }}"></div>
                    </section>
                    <section class="form-section ai-config-section" id="createAiConfigSection">
                        <div class="form-section-head">
                            <div class="section-head-row">
                                <h4>{{ __('ui.products_page.ai_config_title') }}</h4>
                                <button type="button" class="section-toggle-btn" onclick="toggleAiSection('createAiConfigSection', this)">{{ __('ui.common.collapse') }}</button>
                            </div>
                            <p>{{ __('ui.products_page.ai_config_create_help') }}</p>
                            <div class="ai-summary" id="createAiSummary">
                                <span id="createSummaryCategory" class="summary-chip">AI Category: auto</span>
                                <span id="createSummaryPhotoType" class="summary-chip">Garment Photo Type: auto</span>
                                <span id="createSummarySegmentation" class="summary-chip">Segmentation: enabled</span>
                            </div>
                        </div>
                        <div class="field">
                            <label>AI Prompt <span class="preview-hint">(Try-On Max)</span></label>
                            <input name="ai_prompt" placeholder="{{ __('ui.products_page.ai_prompt_placeholder') }}">
                        </div>
                        <div class="field">
                            <label>AI Category <span class="preview-hint">(Try-On v1.6)</span></label>
                            <input id="createAiCategory" type="hidden" name="ai_category" value="auto">
                            <div class="pill-group" role="radiogroup" aria-label="AI Category">
                                <button type="button" class="pill-option active" data-value="auto" onclick="setAiOption('create', 'category', 'auto')">auto</button>
                                <button type="button" class="pill-option" data-value="tops" onclick="setAiOption('create', 'category', 'tops')">tops</button>
                                <button type="button" class="pill-option" data-value="bottoms" onclick="setAiOption('create', 'category', 'bottoms')">bottoms</button>
                                <button type="button" class="pill-option" data-value="one-pieces" onclick="setAiOption('create', 'category', 'one-pieces')">one-pieces</button>
                            </div>
                            <div class="preview-hint">{{ __('ui.products_page.ai_category_hint') }}</div>
                        </div>
                        <div class="field">
                            <label>Garment Photo Type <span class="preview-hint">(Try-On v1.6)</span></label>
                            <input id="createAiGarmentPhotoType" type="hidden" name="ai_garment_photo_type" value="auto">
                            <div class="pill-group" role="radiogroup" aria-label="Photo Type">
                                <button type="button" class="pill-option active" data-value="auto" onclick="setAiOption('create', 'photoType', 'auto')">auto</button>
                                <button type="button" class="pill-option" data-value="flat-lay" onclick="setAiOption('create', 'photoType', 'flat-lay')">flat-lay</button>
                                <button type="button" class="pill-option" data-value="model" onclick="setAiOption('create', 'photoType', 'model')">model</button>
                            </div>
                            <div class="preview-hint">{{ __('ui.products_page.photo_type_hint') }}</div>
                        </div>
                        <div class="field">
                            <input type="hidden" name="ai_segmentation_free" value="0">
                            <label class="status-toggle" style="justify-content:flex-start;">
                                <span>Segmentation Free <span class="preview-hint">(Try-On v1.6)</span></span>
                                <input id="createAiSegmentationFree" type="checkbox" name="ai_segmentation_free" value="1" checked>
                                <span class="status-toggle-switch"></span>
                                <span class="status-toggle-label">{{ __('ui.common.enabled') }}</span>
                            </label>
                            <div class="preview-hint">{{ __('ui.products_page.segmentation_hint') }}</div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="modal-bottom-row">
                <div class="modal-actions" style="margin-top:0; margin-left:auto;">
                    <button class="btn btn-cancel" type="button" onclick="closeModal('createModal')">{{ __('ui.common.cancel') }}</button>
                    <button class="btn btn-primary" type="submit">{{ __('ui.products_page.create_action') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal" onclick="closeOnBackdrop(event, 'editModal')">
    <div class="modal-card">
        <div class="modal-head">
            <h3 class="modal-title">{{ __('ui.products_page.edit_title') }}</h3>
            <div class="modal-head-right">
                <label class="status-toggle">
                    <span>{{ __('ui.products_page.status') }}</span>
                    <input id="editStatusToggle" type="checkbox" checked>
                    <span class="status-toggle-switch"></span>
                    <span id="editStatusLabel" class="status-toggle-label">{{ __('ui.common.active') }}</span>
                </label>
                <button class="close-btn" type="button" onclick="closeModal('editModal')">{{ __('ui.common.close') }}</button>
            </div>
        </div>
        <form id="editForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input id="editProductId" type="hidden" name="edit_product_id" value="">
            <input id="editStatus" type="hidden" name="status" value="active">
            <div class="product-form-grid">
                <div class="product-left-col">
                    <div class="preview-wrap">
                        <img id="editImagePreview" class="preview-img" alt="Edit preview">
                        <button class="preview-change-overlay" type="button" onclick="document.getElementById('editImageFile').click()">{{ __('ui.products_page.change_image') }}</button>
                    </div>
                    <input id="editImageFile" type="file" name="image" accept="image/*" style="display:none;">
                    <div class="url-label">{{ __('ui.products_page.replace_public_url') }}</div>
                    <input id="editImageUrl" type="url" name="image_url" placeholder="https://...">
                </div>
                <div class="product-right-col">
                    <section class="form-section product-info-section">
                        <div class="form-section-head">
                            <h4>{{ __('ui.products_page.product_info_title') }}</h4>
                            <p>{{ __('ui.products_page.product_info_edit_help') }}</p>
                        </div>
                        <div class="field"><label>{{ __('ui.products_page.product_name') }}</label><input id="editName" name="name" required></div>
                        <div class="field"><label>SKU</label><input id="editSku" name="sku"></div>
                        <div class="field"><label>{{ __('ui.products_page.category') }}</label><input id="editCategory" name="category"></div>
                    </section>
                    <section class="form-section ai-config-section" id="editAiConfigSection">
                        <div class="form-section-head">
                            <div class="section-head-row">
                                <h4>{{ __('ui.products_page.ai_config_title') }}</h4>
                                <button type="button" class="section-toggle-btn" onclick="toggleAiSection('editAiConfigSection', this)">{{ __('ui.common.collapse') }}</button>
                            </div>
                            <p>{{ __('ui.products_page.ai_config_edit_help') }}</p>
                            <div class="ai-summary" id="editAiSummary">
                                <span id="editSummaryCategory" class="summary-chip">AI Category: auto</span>
                                <span id="editSummaryPhotoType" class="summary-chip">Garment Photo Type: auto</span>
                                <span id="editSummarySegmentation" class="summary-chip">Segmentation: enabled</span>
                            </div>
                        </div>
                        <div class="field">
                            <label>AI Prompt <span class="preview-hint">(Try-On Max)</span></label>
                            <input id="editAiPrompt" name="ai_prompt" placeholder="{{ __('ui.products_page.ai_prompt_placeholder') }}">
                        </div>
                        <div class="field">
                            <label>AI Category <span class="preview-hint">(Try-On v1.6)</span></label>
                            <input id="editAiCategory" type="hidden" name="ai_category" value="auto">
                            <div class="pill-group" role="radiogroup" aria-label="AI Category">
                                <button type="button" class="pill-option active" data-value="auto" onclick="setAiOption('edit', 'category', 'auto')">auto</button>
                                <button type="button" class="pill-option" data-value="tops" onclick="setAiOption('edit', 'category', 'tops')">tops</button>
                                <button type="button" class="pill-option" data-value="bottoms" onclick="setAiOption('edit', 'category', 'bottoms')">bottoms</button>
                                <button type="button" class="pill-option" data-value="one-pieces" onclick="setAiOption('edit', 'category', 'one-pieces')">one-pieces</button>
                            </div>
                            <div class="preview-hint">{{ __('ui.products_page.ai_category_hint') }}</div>
                        </div>
                        <div class="field">
                            <label>Garment Photo Type <span class="preview-hint">(Try-On v1.6)</span></label>
                            <input id="editAiGarmentPhotoType" type="hidden" name="ai_garment_photo_type" value="auto">
                            <div class="pill-group" role="radiogroup" aria-label="Photo Type">
                                <button type="button" class="pill-option active" data-value="auto" onclick="setAiOption('edit', 'photoType', 'auto')">auto</button>
                                <button type="button" class="pill-option" data-value="flat-lay" onclick="setAiOption('edit', 'photoType', 'flat-lay')">flat-lay</button>
                                <button type="button" class="pill-option" data-value="model" onclick="setAiOption('edit', 'photoType', 'model')">model</button>
                            </div>
                            <div class="preview-hint">{{ __('ui.products_page.photo_type_hint') }}</div>
                        </div>
                        <div class="field">
                            <input type="hidden" name="ai_segmentation_free" value="0">
                            <label class="status-toggle" style="justify-content:flex-start;">
                                <span>Segmentation Free <span class="preview-hint">(Try-On v1.6)</span></span>
                                <input id="editAiSegmentationFree" type="checkbox" name="ai_segmentation_free" value="1" checked>
                                <span class="status-toggle-switch"></span>
                                <span id="editAiSegmentationFreeLabel" class="status-toggle-label">{{ __('ui.common.enabled') }}</span>
                            </label>
                            <div class="preview-hint">{{ __('ui.products_page.segmentation_hint') }}</div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="modal-bottom-row">
                <div class="modal-actions" style="margin-top:0; margin-left:auto;">
                    <button class="btn btn-cancel" type="button" onclick="closeModal('editModal')">{{ __('ui.common.cancel') }}</button>
                    <button class="btn btn-primary" type="submit">{{ __('ui.products_page.save_action') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="deleteConfirmModal" class="modal delete-confirm-modal" onclick="closeOnBackdrop(event, 'deleteConfirmModal')">
    <div class="modal-card delete-confirm-card" role="dialog" aria-modal="true" aria-labelledby="deleteConfirmTitle" aria-describedby="deleteConfirmMessage">
        <div class="delete-confirm-icon" aria-hidden="true">!</div>
        <h3 id="deleteConfirmTitle">{{ __('ui.products_page.delete_title') }}</h3>
        <p id="deleteConfirmMessage">{{ __('ui.products_page.delete_message') }}</p>
        <div class="delete-confirm-actions">
            <button id="deleteConfirmCancelBtn" class="btn btn-cancel" type="button" onclick="closeDeleteConfirm()">{{ __('ui.common.cancel') }}</button>
            <button class="btn btn-danger-solid" type="button" onclick="submitDeleteConfirm()">{{ __('ui.products_page.delete_action') }}</button>
        </div>
    </div>
</div>

<script>
    const I18N_PRODUCTS = {
        active: @json(__('ui.common.active')),
        inactive: @json(__('ui.common.inactive')),
        collapse: @json(__('ui.common.collapse')),
        expand: @json(__('ui.common.expand')),
        enabled: @json(__('ui.common.enabled')),
        disabled: @json(__('ui.common.disabled')),
        summaryAiCategory: @json(__('ui.products_page.summary_ai_category')),
        summaryPhotoType: @json(__('ui.products_page.summary_photo_type')),
        summarySegmentation: @json(__('ui.products_page.summary_segmentation')),
    };

    let pendingDeleteForm = null;

    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }
    function closeOnBackdrop(event, id) { if (event.target.id === id) closeModal(id); }
    function openCreateModal() {
        resetPreview('createImageFile', 'createImageUrl', 'createImagePreview');
        setCreateStatus('active');
        expandAiSection('createAiConfigSection');
        updateAiSummary('create');
        openModal('createModal');
    }

    function openEditModal(id, name, sku, category, status, imageUrl, aiPrompt, aiCategory, aiGarmentPhotoType, aiSegmentationFree) {
        const form = document.getElementById('editForm');
        form.action = `/dashboard/products/${id}`;
        document.getElementById('editProductId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editSku').value = sku;
        document.getElementById('editCategory').value = category;
        document.getElementById('editAiPrompt').value = aiPrompt || '';
        setAiOption('edit', 'category', aiCategory || 'auto');
        setAiOption('edit', 'photoType', aiGarmentPhotoType || 'auto');
        document.getElementById('editAiSegmentationFree').checked = String(aiSegmentationFree) !== '0';
        const editSegmentationLabel = document.getElementById('editAiSegmentationFreeLabel');
        if (editSegmentationLabel) {
            editSegmentationLabel.textContent = document.getElementById('editAiSegmentationFree').checked ? I18N_PRODUCTS.enabled : I18N_PRODUCTS.disabled;
        }
        setEditStatus(status || 'active');
        expandAiSection('editAiConfigSection');
        updateAiSummary('edit');
        resetPreview('editImageFile', 'editImageUrl', 'editImagePreview');
        document.getElementById('editImageUrl').value = imageUrl || '';
        setPreviewFromUrl('editImagePreview', imageUrl);
        openModal('editModal');
    }

    function setEditStatus(status) {
        const normalizedStatus = String(status || 'active').toLowerCase() === 'inactive' ? 'inactive' : 'active';
        const statusInput = document.getElementById('editStatus');
        const statusToggle = document.getElementById('editStatusToggle');
        const statusLabel = document.getElementById('editStatusLabel');
        if (!statusInput) {
            return;
        }

        statusInput.value = normalizedStatus;
        if (statusToggle) statusToggle.checked = normalizedStatus === 'active';
        if (statusLabel) statusLabel.textContent = normalizedStatus === 'active' ? I18N_PRODUCTS.active : I18N_PRODUCTS.inactive;
    }

    function setCreateStatus(status) {
        const normalizedStatus = String(status || 'active').toLowerCase() === 'inactive' ? 'inactive' : 'active';
        const statusInput = document.getElementById('createStatus');
        const statusToggle = document.getElementById('createStatusToggle');
        const statusLabel = document.getElementById('createStatusLabel');
        if (!statusInput) {
            return;
        }

        statusInput.value = normalizedStatus;
        if (statusToggle) statusToggle.checked = normalizedStatus === 'active';
        if (statusLabel) statusLabel.textContent = normalizedStatus === 'active' ? I18N_PRODUCTS.active : I18N_PRODUCTS.inactive;
    }

    function resetPreview(fileInputId, urlInputId, previewId) {
        const fileInput = document.getElementById(fileInputId);
        const urlInput = document.getElementById(urlInputId);
        const preview = document.getElementById(previewId);
        if (fileInput) fileInput.value = '';
        if (urlInput) urlInput.value = '';
        if (preview) {
            preview.removeAttribute('src');
            preview.style.display = 'none';
        }
    }

    function bindImagePreview(fileInputId, urlInputId, previewId) {
        const fileInput = document.getElementById(fileInputId);
        const urlInput = document.getElementById(urlInputId);
        const preview = document.getElementById(previewId);
        if (!fileInput || !urlInput || !preview) return;

        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) {
                preview.removeAttribute('src');
                preview.style.display = 'none';
                return;
            }
            urlInput.value = '';
            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });

        urlInput.addEventListener('input', function () {
            const value = this.value.trim();
            if (!value) {
                preview.removeAttribute('src');
                preview.style.display = 'none';
                return;
            }
            fileInput.value = '';
            setPreviewFromUrl(previewId, value);
        });
    }

    function setPreviewFromUrl(previewId, value) {
        const preview = document.getElementById(previewId);
        if (!preview) return;

        const url = (value || '').trim();
        if (!url) {
            preview.removeAttribute('src');
            preview.style.display = 'none';
            return;
        }

        preview.src = url;
        preview.style.display = 'block';
    }

    function openDeleteConfirm(event, form) {
        if (event) event.preventDefault();
        pendingDeleteForm = form || null;
        openModal('deleteConfirmModal');
        const cancelBtn = document.getElementById('deleteConfirmCancelBtn');
        if (cancelBtn) cancelBtn.focus();
        return false;
    }

    function closeDeleteConfirm() {
        pendingDeleteForm = null;
        closeModal('deleteConfirmModal');
    }

    function submitDeleteConfirm() {
        const form = pendingDeleteForm;
        pendingDeleteForm = null;
        closeModal('deleteConfirmModal');
        if (form) form.submit();
    }

    function toggleAiSection(sectionId, triggerBtn) {
        const section = document.getElementById(sectionId);
        if (!section) return;
        section.classList.toggle('collapsed');
        if (triggerBtn) {
            triggerBtn.textContent = section.classList.contains('collapsed') ? I18N_PRODUCTS.expand : I18N_PRODUCTS.collapse;
        }
    }

    function expandAiSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (!section) return;
        section.classList.remove('collapsed');
        const btn = section.querySelector('.section-toggle-btn');
        if (btn) btn.textContent = I18N_PRODUCTS.collapse;
    }
    function updateAiSummary(prefix) {
        const aiCategory = document.getElementById(prefix === 'create' ? 'createAiCategory' : 'editAiCategory');
        const photoType = document.getElementById(prefix === 'create' ? 'createAiGarmentPhotoType' : 'editAiGarmentPhotoType');
        const segmentation = document.getElementById(prefix === 'create' ? 'createAiSegmentationFree' : 'editAiSegmentationFree');
        const summaryCategory = document.getElementById(prefix === 'create' ? 'createSummaryCategory' : 'editSummaryCategory');
        const summaryPhotoType = document.getElementById(prefix === 'create' ? 'createSummaryPhotoType' : 'editSummaryPhotoType');
        const summarySegmentation = document.getElementById(prefix === 'create' ? 'createSummarySegmentation' : 'editSummarySegmentation');

        if (summaryCategory && aiCategory) summaryCategory.textContent = `${I18N_PRODUCTS.summaryAiCategory}: ${aiCategory.value || 'auto'}`;
        if (summaryPhotoType && photoType) summaryPhotoType.textContent = `${I18N_PRODUCTS.summaryPhotoType}: ${photoType.value || 'auto'}`;
        if (summarySegmentation && segmentation) summarySegmentation.textContent = `${I18N_PRODUCTS.summarySegmentation}: ${segmentation.checked ? I18N_PRODUCTS.enabled : I18N_PRODUCTS.disabled}`;
    }

    function setAiOption(prefix, field, value) {
        const isCategory = field === 'category';
        const inputId = prefix === 'create'
            ? (isCategory ? 'createAiCategory' : 'createAiGarmentPhotoType')
            : (isCategory ? 'editAiCategory' : 'editAiGarmentPhotoType');
        const input = document.getElementById(inputId);
        if (!input) return;

        input.value = value;

        const fieldWrap = input.closest('.field');
        if (fieldWrap) {
            fieldWrap.querySelectorAll('.pill-option').forEach(function (btn) {
                const active = btn.getAttribute('data-value') === value;
                btn.classList.toggle('active', active);
                btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        }

        updateAiSummary(prefix);
    }

    bindImagePreview('createImageFile', 'createImageUrl', 'createImagePreview');
    bindImagePreview('editImageFile', 'editImageUrl', 'editImagePreview');

    const createAiCategory = document.getElementById('createAiCategory');
    const createAiGarmentPhotoType = document.getElementById('createAiGarmentPhotoType');
    const createAiSegmentationFree = document.getElementById('createAiSegmentationFree');
    const editAiCategory = document.getElementById('editAiCategory');
    const editAiGarmentPhotoType = document.getElementById('editAiGarmentPhotoType');
    if (createAiSegmentationFree) createAiSegmentationFree.addEventListener('change', function () { updateAiSummary('create'); });

    setAiOption('create', 'category', createAiCategory ? createAiCategory.value : 'auto');
    setAiOption('create', 'photoType', createAiGarmentPhotoType ? createAiGarmentPhotoType.value : 'auto');

    const editStatusToggle = document.getElementById('editStatusToggle');
    if (editStatusToggle) {
        editStatusToggle.addEventListener('change', function () {
            setEditStatus(this.checked ? 'active' : 'inactive');
        });
    }
    const createStatusToggle = document.getElementById('createStatusToggle');
    if (createStatusToggle) {
        createStatusToggle.addEventListener('change', function () {
            setCreateStatus(this.checked ? 'active' : 'inactive');
        });
    }

    const editAiSegmentationFree = document.getElementById('editAiSegmentationFree');
    const editAiSegmentationFreeLabel = document.getElementById('editAiSegmentationFreeLabel');
    if (editAiSegmentationFree && editAiSegmentationFreeLabel) {
        editAiSegmentationFree.addEventListener('change', function () {
            editAiSegmentationFreeLabel.textContent = this.checked ? I18N_PRODUCTS.enabled : I18N_PRODUCTS.disabled;
            updateAiSummary('edit');
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            const deleteConfirm = document.getElementById('deleteConfirmModal');
            if (deleteConfirm && deleteConfirm.classList.contains('active')) {
                closeDeleteConfirm();
            }
        }
    });

    document.addEventListener('click', function (event) {
        const openPopovers = document.querySelectorAll('.filter-popover[open]');
        openPopovers.forEach(function (popover) {
            if (!popover.contains(event.target)) {
                popover.removeAttribute('open');
            }
        });
    });

    @if($errors->any() && old('edit_product_id'))
        openEditModal(
            {{ (int) old('edit_product_id') }},
            @json(old('name', '')),
            @json(old('sku', '')),
            @json(old('category', '')),
            @json(old('status', 'active')),
            @json(old('image_url', '')),
            @json(old('ai_prompt', '')),
            @json(old('ai_category', 'auto')),
            @json(old('ai_garment_photo_type', 'auto')),
            @json((int) old('ai_segmentation_free', 1))
        );
    @endif
</script>
</body>
</html>
