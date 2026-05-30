<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $seoTitle = trim((string) ($seller->seo_title ?? '')) ?: (__('ui.common.products').' '.$seller->store_name);
        $seoDescription = trim((string) ($seller->seo_description ?? '')) ?: ('Best products from '.$seller->store_name.'.');
        $seoImage = trim((string) ($seller->seo_logo_url ?? ''));
        $canonicalUrl = route('public.seller.page', ['seller_slug' => $seller->slug]);
        $faviconVersion = (string) ($seller->updated_at?->timestamp ?? time());
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if($seoImage !== '')
    <meta property="og:image" content="{{ $seoImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $seoImage }}">
    @else
    <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    @if($seoImage !== '')
    <link rel="icon" type="image/png" href="{{ $seoImage }}?v={{ urlencode($faviconVersion) }}">
    <link rel="shortcut icon" href="{{ $seoImage }}?v={{ urlencode($faviconVersion) }}">
    <link rel="apple-touch-icon" href="{{ $seoImage }}?v={{ urlencode($faviconVersion) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Inter:wght@500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f7f9fd;
            --surface: #ffffff;
            --surface-soft: #f3f7fc;
            --text: #11253d;
            --muted: #49627f;
            --primary: #21759b;
            --primary-strong: #005c7e;
            --secondary: #1e3a5f;
            --outline: #d4dfec;
            --radius-xl: 20px;
            --radius-lg: 14px;
            --radius-md: 10px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Hanken Grotesk", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(1200px 420px at 16% -12%, rgba(33, 117, 155, 0.08) 0%, transparent 60%),
                radial-gradient(900px 400px at 90% -10%, rgba(74, 120, 166, 0.11) 0%, transparent 65%),
                var(--bg);
        }

        .topbar-wrap {
            padding: 0 18px 0;
        }

        .topbar {
            max-width: 1300px;
            margin: 0 auto;
            background: var(--surface);
            border-radius: 0 0 22px 22px;
            border: 1px solid var(--outline);
            border-top: 0;
            box-shadow: 0 12px 24px rgba(17, 40, 68, 0.08);
            padding: 18px 26px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0;
        }

        .brand-label {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: .2px;
            color: var(--secondary);
            line-height: 1;
        }

        .wrap {
            max-width: 1300px;
            margin: 0 auto;
            padding: 26px;
            position: relative;
        }

        .section-title {
            margin: 0 0 16px;
            font-size: 46px;
            line-height: 1.06;
            color: #b35e26;
        }

        .section-subtitle {
            margin: 0 0 20px;
            font-size: 18px;
            color: var(--muted);
        }

        .catalog-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
            padding: 14px;
            background: var(--surface);
            border: 1px solid var(--outline);
            border-radius: var(--radius-lg);
        }

        .catalog-toolbar form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            width: 100%;
        }

        .catalog-field {
            height: 42px;
            border-radius: var(--radius-md);
            border: 1px solid #c9d7e6;
            background: #fff;
            color: var(--text);
            padding: 0 12px;
            min-width: 180px;
            font: inherit;
        }

        .catalog-search {
            flex: 1;
            min-width: 220px;
        }

        .catalog-btn {
            border: 1px solid var(--primary);
            background: var(--primary);
            color: #fff;
            height: 42px;
            border-radius: var(--radius-md);
            padding: 0 14px;
            font-family: Inter, "Segoe UI", sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .catalog-btn:hover {
            background: var(--primary-strong);
            border-color: var(--primary-strong);
        }

        .catalog-btn.catalog-btn-ghost {
            background: #fff;
            border-color: #c4d1e0;
            color: var(--secondary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .catalog-btn.catalog-btn-ghost:hover {
            background: #f6faff;
            border-color: #8ca5bf;
        }

        .catalog-meta {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .catalog-footer-row {
            margin-top: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(245px, 1fr));
            gap: 16px;
        }

        .card {
            display: flex;
            flex-direction: column;
            border: 1px solid var(--outline);
            border-radius: var(--radius-lg);
            background: var(--surface);
            overflow: hidden;
            cursor: pointer;
            text-align: left;
            padding: 0;
            transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(17, 40, 68, 0.12);
            border-color: #afc7de;
        }

        .card.selected {
            border-color: var(--primary);
            box-shadow: 0 10px 18px rgba(33, 117, 155, 0.24);
        }

        .thumb-wrap {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #fff;
            border-bottom: 1px solid #e7edf5;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .thumb-fallback {
            color: #99abc1;
            font-weight: 700;
            font-size: 14px;
        }

        .card-body {
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-height: 120px;
        }

        .product-name {
            margin: 0;
            font-size: 18px;
            line-height: 1.22;
            color: var(--text);
        }

        .meta {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.3;
        }

        .empty {
            border: 1px dashed #bfcddb;
            border-radius: var(--radius-lg);
            background: var(--surface);
            padding: 26px;
            text-align: center;
            color: var(--muted);
        }

        .catalog-pagination {
            margin-top: 0;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .catalog-pagination a,
        .catalog-pagination span {
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border-radius: var(--radius-md);
            border: 1px solid #c9d7e6;
            background: #fff;
            color: var(--secondary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-family: Inter, "Segoe UI", sans-serif;
            font-size: 12px;
            font-weight: 600;
        }

        .catalog-pagination .active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 32, 54, 0.55);
            backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 1500;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .24s ease, visibility .24s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .modal {
            width: min(700px, 100%);
            max-height: calc(100vh - 28px);
            overflow: auto;
            background: linear-gradient(180deg, #ffffff, #f7fbff);
            border-radius: var(--radius-xl);
            border: 1px solid #d2dfee;
            box-shadow: 0 22px 60px rgba(17, 40, 68, 0.26);
            padding: 14px;
            transform: translateY(14px) scale(.98);
            opacity: 0;
            transition: transform .24s ease, opacity .24s ease;
        }

        .modal-overlay.active .modal {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .modal-title {
            margin: 0;
            font-size: 42px;
            color: var(--secondary);
            line-height: 1.05;
        }

        .modal-title-row {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .dummy-mode-badge {
            display: inline-flex;
            align-items: center;
            height: 24px;
            padding: 0 10px;
            border-radius: 999px;
            border: 1px solid #b7cce1;
            background: #edf6ff;
            color: #1f4e78;
            font-family: Inter, "Segoe UI", sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .modal-close {
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #edf4ff;
            color: var(--secondary);
            font-size: 22px;
            cursor: pointer;
        }

        .modal-info-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            margin-bottom: 8px;
            align-items: center;
            padding: 10px 12px;
            border: 1px solid #d7e3ef;
            border-radius: 10px;
            background: #f7fbff;
        }

        .selected-product {
            padding: 2px 0;
            font-size: 14px;
            margin: 0;
            color: var(--text);
        }

        .quota-box {
            border: none;
            background: transparent;
            color: #295884;
            padding: 2px 0;
            font-size: 13px;
            margin: 0;
            text-align: right;
            white-space: nowrap;
            justify-self: end;
            width: auto;
            max-width: none;
        }

        .field {
            border-radius: var(--radius-md);
            background: var(--surface);
            border: 1px solid #d7e3ef;
            padding: 10px;
        }

        .label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #2f4d6c;
        }

        .label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
        }

        .label-row .label {
            margin-bottom: 0;
        }

        .dummy-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #2f4d6c;
            user-select: none;
            cursor: pointer;
        }

        .dummy-toggle input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }

        .dummy-toggle-switch {
            position: relative;
            width: 40px;
            height: 22px;
            border-radius: 999px;
            background: #c9d7e6;
            border: 1px solid #b9cadb;
            transition: background .2s ease, border-color .2s ease;
            flex: 0 0 auto;
        }

        .dummy-toggle-switch::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(43, 23, 9, 0.24);
            transition: transform .2s ease;
        }

        .dummy-toggle input:checked + .dummy-toggle-switch {
            background: #8eb9da;
            border-color: #5f98c4;
        }

        .dummy-toggle input:checked + .dummy-toggle-switch::after {
            transform: translateX(18px);
        }

        .dummy-toggle-text {
            font-weight: 700;
            line-height: 1;
        }

        .model-footer-row {
            margin-top: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: nowrap;
        }

        .model-footer-row .label {
            margin: 0;
            margin-bottom: 0;
            font-size: 14px;
            white-space: nowrap;
        }

        .model-footer-row .dummy-toggle {
            font-size: 11px;
            gap: 6px;
            white-space: nowrap;
            flex: 0 0 auto;
        }

        .model-footer-row .dummy-toggle-text {
            font-size: 11px;
            font-weight: 600;
        }

        .input-file {
            display: none;
        }

        .upload-trigger {
            width: 100%;
            height: 40px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(180deg, #2a8ab4, #21759b);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .01em;
            cursor: pointer;
        }

        .status-note {
            margin-top: 10px;
            min-height: 20px;
            font-size: 13px;
            line-height: 1.4;
            color: var(--muted);
        }

        .status-error {
            color: #ba1a1a;
        }

        .status-success {
            color: #0e7a61;
        }

        .modal-preview-grid {
            display: none;
        }

        .tryon-main-grid {
            display: grid;
            grid-template-columns: 250px minmax(0, 1fr);
            gap: 10px;
            margin-bottom: 10px;
            width: 100%;
            align-items: stretch;
            height: auto;
        }

        .tryon-left-rail {
            display: grid;
            grid-template-rows: auto auto;
            gap: 10px;
            min-width: 0;
        }

        .tryon-main-grid > .field {
            min-width: 0;
        }

        .tryon-generated-field {
            width: 100%;
        }

        .tryon-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
        }

        .ready-badge {
            display: inline-flex;
            align-items: center;
            height: 20px;
            padding: 0 8px;
            border-radius: 999px;
            border: 1px solid #c9dff1;
            background: #eef8ff;
            color: #3374a1;
            font-family: Inter, "Segoe UI", sans-serif;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: capitalize;
        }

        .preview-box {
            position: relative;
            width: 100%;
            height: auto;
            border-radius: var(--radius-md);
            border: none;
            background: #fff;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: none;
        }

        .preview-box-product {
            height: 180px;
            aspect-ratio: auto;
        }

        .preview-box-model {
            height: 180px;
            aspect-ratio: auto;
        }

        .preview-box-result {
            width: 100%;
            height: 340px;
            aspect-ratio: auto;
        }

        .selected-product-thumb {
            width: 100%;
            border-radius: 10px;
            background: #f7fbff;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .selected-product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .selected-product-thumb-fallback {
            font-family: Inter, "Segoe UI", sans-serif;
            font-size: 10px;
            font-weight: 600;
            color: #55708c;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .preview-placeholder {
            color: #55708c;
            text-align: center;
            padding: 14px;
            font-size: 14px;
            line-height: 1.35;
        }

        .preview-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            border: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            color: #2f628c;
            font-size: 20px;
            cursor: pointer;
            display: none;
        }

        .loading-dots {
            display: inline-flex;
            align-items: flex-end;
            gap: 8px;
            height: 26px;
        }

        .loading-dots span {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #21759b;
            animation: dot-bounce 0.9s ease-in-out infinite;
        }

        .loading-dots span:nth-child(2) {
            animation-delay: 0.15s;
        }

        .loading-dots span:nth-child(3) {
            animation-delay: 0.3s;
        }

        @keyframes dot-bounce {
            0%,
            80%,
            100% {
                transform: translateY(0);
                opacity: 0.5;
            }

            40% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }

        .generate-btn {
            width: 100%;
            height: 44px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(180deg, #2a8ab4, #21759b);
            color: #fff;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
        }

        .generate-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .floating-history-btn {
            position: fixed;
            right: max(18px, calc((100vw - 1300px) / 2 + 26px));
            bottom: 86px;
            width: 56px;
            height: 56px;
            border: none;
            border-radius: 999px;
            background: linear-gradient(180deg, #2a8ab4, #21759b);
            color: #fff;
            box-shadow: 0 14px 28px rgba(17, 58, 88, 0.28);
            cursor: pointer;
            z-index: 1400;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 900;
        }

        .floating-history-panel {
            position: fixed;
            right: max(18px, calc((100vw - 1300px) / 2 + 26px));
            bottom: 152px;
            width: min(360px, calc(100vw - 36px));
            max-height: 62vh;
            overflow: auto;
            background: #ffffff;
            border: 1px solid #d7e3ef;
            border-radius: 12px;
            box-shadow: 0 18px 36px rgba(15, 44, 73, 0.24);
            z-index: 1400;
            padding: 12px;
            display: none;
        }

        .floating-history-panel.active {
            display: block;
        }

        .floating-history-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }

        .floating-history-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #143050;
        }

        .floating-history-close {
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #edf4ff;
            color: #2b5378;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
        }

        .history-wrap {
            margin: 4px 0 10px;
            border: 1px solid #d7e3ef;
            border-radius: var(--radius-md);
            background: var(--surface);
            padding: 8px;
        }

        .tryon-generated-field .history-wrap {
            margin: 8px 0 0;
        }

        .tryon-generated-field .history-list {
            grid-template-columns: repeat(auto-fill, minmax(72px, 1fr));
            gap: 6px;
        }

        .tryon-generated-field .history-item {
            min-height: 96px;
        }

        .history-title {
            margin: 0 0 8px;
            font-size: 13px;
            font-weight: 800;
            color: #2f4d6c;
        }

        .history-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(84px, 1fr));
            gap: 8px;
        }

        .history-item {
            border: 1px solid #d7e3ef;
            border-radius: 10px;
            background: #fff;
            overflow: hidden;
            padding: 0;
            cursor: pointer;
            min-height: 112px;
        }

        .history-item img {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            display: block;
        }

        .history-item-time {
            display: block;
            font-size: 10px;
            color: #49627f;
            padding: 5px 6px 6px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .history-empty {
            margin: 0;
            font-size: 13px;
            color: #49627f;
            padding: 2px 0 0;
        }

        @media (max-width: 900px) {
            .topbar {
                flex-direction: column;
                align-items: stretch;
            }

            .brand {
                gap: 0;
            }

            .brand-label {
                font-size: 26px;
            }

            .section-title {
                font-size: 36px;
            }

            .modal-preview-grid {
                grid-template-columns: 1fr;
            }

            .modal-info-grid {
                grid-template-columns: 1fr;
            }

            .tryon-main-grid {
                grid-template-columns: 1fr;
                height: auto;
            }

            .preview-box-product {
                height: 180px;
            }

            .preview-box-model {
                height: 240px;
            }

            .preview-box-result {
                height: 320px;
            }

            .catalog-footer-row {
                align-items: flex-start;
                flex-direction: column;
            }

            .catalog-pagination {
                width: 100%;
                justify-content: flex-start;
            }

            .floating-history-btn {
                right: 16px;
                bottom: 96px;
            }

            .floating-history-panel {
                right: 16px;
                bottom: 164px;
            }
        }
    </style>
</head>

<body>
    <div class="topbar-wrap">
        <header class="topbar">
            <div class="brand">
                <span class="brand-label">{{ __('ui.common.products') }}</span>
            </div>
        </header>
    </div>

    <main class="wrap">
        <div class="catalog-toolbar">
            <form method="GET" action="{{ route('public.seller.page', ['seller_slug' => $seller->slug]) }}">
                <input
                    class="catalog-field catalog-search"
                    type="search"
                    name="q"
                    value="{{ $activeFilters['q'] }}"
                    placeholder="{{ __('ui.store.search_placeholder') }}">
                <select class="catalog-field" name="category">
                    <option value="">{{ __('ui.store.all_categories') }}</option>
                    @foreach ($categories as $categoryOption)
                        <option value="{{ $categoryOption }}" {{ $activeFilters['category'] === $categoryOption ? 'selected' : '' }}>
                            {{ $categoryOption }}
                        </option>
                    @endforeach
                </select>
                <select class="catalog-field" name="sort">
                    <option value="latest" {{ $activeFilters['sort'] === 'latest' ? 'selected' : '' }}>{{ __('ui.store.sort_latest') }}</option>
                    <option value="name_asc" {{ $activeFilters['sort'] === 'name_asc' ? 'selected' : '' }}>{{ __('ui.store.sort_name_asc') }}</option>
                    <option value="name_desc" {{ $activeFilters['sort'] === 'name_desc' ? 'selected' : '' }}>{{ __('ui.store.sort_name_desc') }}</option>
                </select>
                <button class="catalog-btn" type="submit">{{ __('ui.store.apply') }}</button>
                <a class="catalog-btn catalog-btn-ghost" href="{{ route('public.seller.page', ['seller_slug' => $seller->slug]) }}">{{ __('ui.store.reset') }}</a>
            </form>
        </div>

        @if($products->isEmpty())
        <div class="empty">{{ __('ui.products_page.no_products') }}</div>
        @else
        <section class="grid" id="productGrid">
            @foreach ($products as $product)
            @php
            $image = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
            $isSelected = !empty($selectedProduct) && $selectedProduct->id === $product->id;
            @endphp
            <button
                type="button"
                class="card {{ $isSelected ? 'selected' : '' }}"
                data-product-id="{{ $product->id }}"
                data-product-name="{{ $product->name }}"
                data-product-slug="{{ $product->slug }}"
                data-product-sku="{{ $product->sku }}"
                data-product-category="{{ $product->category }}"
                data-product-image="{{ $image?->image_url ?? '' }}"
                onclick="openTryOnModal(this)">
                <div class="thumb-wrap">
                    @if($image)
                    <img src="{{ $image->image_url }}" alt="{{ $product->name }}" class="thumb">
                    @else
                    <div class="thumb-fallback">No Image</div>
                    @endif
                </div>
                <div class="card-body">
                    <h3 class="product-name">{{ $product->name }}</h3>
                    <p class="meta">Category: {{ $product->category ?: '-' }}</p>
                </div>
            </button>
            @endforeach
        </section>
        <div class="catalog-footer-row">
            <p class="catalog-meta">
                {{ __('ui.store.showing_summary', ['count' => $products->count(), 'total' => $products->total()]) }}
            </p>
            @if ($products->lastPage() > 1)
            @php
                $currentPage = $products->currentPage();
                $lastPage = $products->lastPage();
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
            @endphp
            <nav class="catalog-pagination" aria-label="Pagination produk">
                @if ($products->onFirstPage())
                    <span>&laquo;</span>
                @else
                    <a href="{{ $products->previousPageUrl() }}" rel="prev">&laquo;</a>
                @endif

                @for ($page = $startPage; $page <= $endPage; $page++)
                    @if ($page === $currentPage)
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $products->url($page) }}">{{ $page }}</a>
                    @endif
                @endfor

                @if ($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}" rel="next">&raquo;</a>
                @else
                    <span>&raquo;</span>
                @endif
            </nav>
            @endif
        </div>
        @endif
        @if(!empty($selectedProduct))
            @php
                $selectedProductImage = $selectedProduct->images->firstWhere('is_primary', true) ?? $selectedProduct->images->first();
            @endphp
            <button
                id="autoOpenTryOnTrigger"
                type="button"
                style="display:none"
                aria-hidden="true"
                data-product-id="{{ $selectedProduct->id }}"
                data-product-name="{{ $selectedProduct->name }}"
                data-product-slug="{{ $selectedProduct->slug }}"
                data-product-sku="{{ $selectedProduct->sku }}"
                data-product-category="{{ $selectedProduct->category }}"
                data-product-image="{{ $selectedProductImage?->image_url ?? '' }}">
            </button>
        @endif
        <button id="floatingHistoryBtn" type="button" class="floating-history-btn" aria-label="{{ __('ui.store.history_button_aria') }}">
            &#128340;
        </button>
        <div id="floatingHistoryPanel" class="floating-history-panel" aria-hidden="true">
            <div class="floating-history-header">
                <p class="floating-history-title">{{ __('ui.store.history_title') }}</p>
                <button id="floatingHistoryClose" type="button" class="floating-history-close" aria-label="{{ __('ui.common.close') }}">&times;</button>
            </div>
            <div id="floatingHistoryList" class="history-list"></div>
            <p id="floatingHistoryEmpty" class="history-empty">{{ __('ui.store.no_history') }}</p>
        </div>
    </main>

    <div class="modal-overlay" id="tryOnModal" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="tryOnModalTitle">
            <div class="modal-header">
                <div class="modal-title-row">
                    <h2 class="modal-title" id="tryOnModalTitle">{{ __('ui.store.tryon_tool_title') }}</h2>
                </div>
                <button type="button" class="modal-close" onclick="closeTryOnModal()" aria-label="{{ __('ui.common.close') }}">&times;</button>
            </div>

            <div class="modal-info-grid">
                <div id="selectedProductInfo" class="selected-product">
                    {{ __('ui.common.products') }}: <strong id="selectedProductName">-</strong>
                </div>
                <div id="quotaBox" class="quota-box">
                    {{ __('ui.store.quota_today_remaining') }}: <strong id="remainingQuotaText">-</strong>
                </div>
            </div>

            <input class="input-file" id="customerPhoto" type="file" accept="image/*">

            <div class="tryon-main-grid">
                <div class="tryon-left-rail">
                    <div class="field">
                        <div class="tryon-panel-head">
                            <label class="label">{{ __('ui.store.product_photo') }}</label>
                        </div>
                        <div class="selected-product-thumb preview-box preview-box-product" aria-label="{{ __('ui.store.selected_product_alt') }}">
                            <img id="selectedProductPreview" alt="{{ __('ui.store.selected_product_alt') }}" style="display:none;">
                            <span id="selectedProductPreviewFallback" class="selected-product-thumb-fallback">{{ __('ui.store.no_image') }}</span>
                        </div>
                    </div>

                    <div class="field">
                        <div class="model-footer-row">
                            <label class="label">{{ __('ui.store.model_photo') }}</label>
                        </div>
                        <div class="preview-box preview-box-model">
                            <img id="customerPreview" alt="Customer preview">
                            <button id="removePhotoBtn" class="preview-remove" type="button" aria-label="{{ __('ui.store.remove_photo_aria') }}">&times;</button>
                            <div id="customerPlaceholder" class="preview-placeholder">
                                <p>{{ __('ui.store.upload_photo') }}</p>
                                <button type="button" class="upload-trigger" onclick="document.getElementById('customerPhoto').click()">{{ __('ui.store.choose_file') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field tryon-generated-field">
                    <div class="tryon-panel-head">
                        <label class="label">{{ __('ui.store.tryon_generated') }}</label>
                        <span class="ready-badge">{{ __('ui.store.ready') }}</span>
                    </div>
                    <div class="preview-box preview-box-result">
                        <img id="resultPreview" alt="Try-on result">
                        <div id="resultPlaceholder" class="preview-placeholder"></div>
                    </div>
                    <div id="statusNote" class="status-note" role="status" aria-live="polite"></div>
                </div>
            </div>

            <button id="generateBtn" class="generate-btn" type="button" onclick="submitTryOn()">{{ __('ui.store.tryon_action') }}</button>
        </div>
    </div>

    <script>
        const I18N = {
            selectProductFirst: @json(__('ui.store.select_product_first')),
            generateDone: @json(__('ui.store.generate_done')),
            uploadModelFirst: @json(__('ui.store.upload_model_first')),
            dailyLimitReached: @json(__('ui.store.daily_limit_reached')),
            failedCreateSession: @json(__('ui.store.failed_create_session')),
            failedCheckStatus: @json(__('ui.store.failed_check_status')),
            failedPolling: @json(__('ui.store.failed_polling')),
            historyResultShown: @json(__('ui.store.history_result_shown')),
            historyItemAria: @json(__('ui.store.history_button_aria')),
            historyImageAlt: @json(__('ui.store.tryon_generated')),
            processingRetryLater: @json(__('ui.store.processing_retry_later')),
        };

        let selectedProductId = @json(optional($selectedProduct)->id);
        let pollTimer = null;
        const TRYON_DEVICE_KEY = 'tryon_device_id_v1';
        const TRYON_HISTORY_URL = @json(route('public.tryon.sessions.history', ['seller_slug' => $seller->slug]));
        let remainingDailyQuota = null;

        function resolveTryOnDeviceId() {
            try {
                const existing = window.localStorage.getItem(TRYON_DEVICE_KEY);
                if (existing && typeof existing === 'string') {
                    return existing;
                }

                const generated = (window.crypto && window.crypto.randomUUID)
                    ? window.crypto.randomUUID()
                    : `dev-${Date.now()}-${Math.random().toString(36).slice(2, 12)}`;

                window.localStorage.setItem(TRYON_DEVICE_KEY, generated);
                return generated;
            } catch (error) {
                return `dev-fallback-${Date.now()}`;
            }
        }

        function selectProduct(el) {
            const cards = document.querySelectorAll('#productGrid .card');
            cards.forEach((card) => card.classList.remove('selected'));
            el.classList.add('selected');

            const productName = el.getAttribute('data-product-name') || '-';
            const productImage = el.getAttribute('data-product-image') || '';
            selectedProductId = Number(el.getAttribute('data-product-id')) || null;
            const productSlug = el.getAttribute('data-product-slug');
            const productSku = el.getAttribute('data-product-sku');
            document.getElementById('selectedProductName').textContent = productName;
            const selectedProductPreview = document.getElementById('selectedProductPreview');
            const selectedProductPreviewFallback = document.getElementById('selectedProductPreviewFallback');
            if (selectedProductPreview && selectedProductPreviewFallback) {
                if (productImage) {
                    selectedProductPreview.src = productImage;
                    selectedProductPreview.style.display = 'block';
                    selectedProductPreviewFallback.style.display = 'none';
                } else {
                    selectedProductPreview.removeAttribute('src');
                    selectedProductPreview.style.display = 'none';
                    selectedProductPreviewFallback.style.display = 'inline';
                }
            }

            const productRef = productSku || productSlug;
            if (productRef) {
                const newUrl = `/${@json($seller->slug)}/${productRef}`;
                window.history.replaceState({}, '', newUrl);
            }
        }

        function openTryOnModal(el) {
            selectProduct(el);
            const modal = document.getElementById('tryOnModal');
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            applyDummyModelSelectionUI();
            refreshHistory();
        }

        function closeTryOnModal() {
            const modal = document.getElementById('tryOnModal');
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.getElementById('tryOnModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeTryOnModal();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeTryOnModal();
            }
        });

        document.getElementById('customerPhoto').addEventListener('change', function() {
            const file = this.files && this.files[0] ? this.files[0] : null;
            const preview = document.getElementById('customerPreview');
            const placeholder = document.getElementById('customerPlaceholder');
            const removeBtn = document.getElementById('removePhotoBtn');

            if (!file) {
                preview.removeAttribute('src');
                preview.style.display = 'none';
                placeholder.style.display = 'block';
                removeBtn.style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                preview.src = event.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
                removeBtn.style.display = 'inline-flex';
            };
            reader.readAsDataURL(file);
        });

        document.getElementById('removePhotoBtn').addEventListener('click', function() {
            const input = document.getElementById('customerPhoto');
            const preview = document.getElementById('customerPreview');
            const placeholder = document.getElementById('customerPlaceholder');
            const removeBtn = document.getElementById('removePhotoBtn');

            input.value = '';
            preview.removeAttribute('src');
            preview.style.display = 'none';
            placeholder.style.display = 'block';
            removeBtn.style.display = 'none';
            setStatus('', '');
        });

        function setStatus(message, type) {
            const note = document.getElementById('statusNote');
            if (!note) {
                return;
            }
            note.textContent = message || '';
            note.classList.remove('status-error', 'status-success');
            if (type === 'error') note.classList.add('status-error');
            if (type === 'success') note.classList.add('status-success');
        }

        function setLoading(loading) {
            const btn = document.getElementById('generateBtn');
            const resultPlaceholder = document.getElementById('resultPlaceholder');
            const mustDisableByQuota = remainingDailyQuota !== null && remainingDailyQuota <= 0;

            btn.disabled = loading || mustDisableByQuota;
            btn.textContent = loading ? 'Processing...' : 'Try-On';

            if (resultPlaceholder) {
                resultPlaceholder.innerHTML = loading
                    ? '<div class="loading-dots" aria-label="Loading"><span></span><span></span><span></span></div>'
                    : '';
            }
        }

        function showResultPlaceholderMessage(message, type = '') {
            const resultPreview = document.getElementById('resultPreview');
            const resultPlaceholder = document.getElementById('resultPlaceholder');
            if (!resultPlaceholder || !resultPreview) {
                return;
            }

            resultPreview.removeAttribute('src');
            resultPreview.style.display = 'none';
            resultPlaceholder.style.display = 'block';
            resultPlaceholder.textContent = message || '';
            resultPlaceholder.classList.remove('status-error', 'status-success');
            if (type === 'error') resultPlaceholder.classList.add('status-error');
            if (type === 'success') resultPlaceholder.classList.add('status-success');
        }

        function formatHistoryDateLabel(isoDate) {
            if (!isoDate) {
                return '-';
            }

            const dt = new Date(isoDate);
            if (Number.isNaN(dt.getTime())) {
                return '-';
            }

            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit',
            }).format(dt);
        }

        function showHistoryResult(url) {
            if (!url) {
                return;
            }

            const resultPreview = document.getElementById('resultPreview');
            const resultPlaceholder = document.getElementById('resultPlaceholder');
            resultPreview.src = url;
            resultPreview.style.display = 'block';
            resultPlaceholder.style.display = 'none';
            setStatus(I18N.historyResultShown, 'success');
        }

        function renderHistory(items) {
            const listEl = document.getElementById('historyList');
            const emptyEl = document.getElementById('historyEmpty');
            if (!listEl || !emptyEl) {
                return;
            }

            listEl.innerHTML = '';

            if (!Array.isArray(items) || items.length === 0) {
                emptyEl.style.display = 'block';
                return;
            }

            emptyEl.style.display = 'none';
            items.forEach((item) => {
                if (!item || !item.result_url) {
                    return;
                }

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'history-item';
                btn.setAttribute('aria-label', I18N.historyItemAria);

                const img = document.createElement('img');
                img.src = item.result_url;
                img.alt = I18N.historyImageAlt;

                const time = document.createElement('span');
                time.className = 'history-item-time';
                time.textContent = formatHistoryDateLabel(item.created_at);

                btn.appendChild(img);
                btn.appendChild(time);
                btn.addEventListener('click', () => showHistoryResult(item.result_url));
                listEl.appendChild(btn);
            });
        }

        function renderFloatingHistory(items) {
            const listEl = document.getElementById('floatingHistoryList');
            const emptyEl = document.getElementById('floatingHistoryEmpty');
            if (!listEl || !emptyEl) {
                return;
            }

            listEl.innerHTML = '';

            if (!Array.isArray(items) || items.length === 0) {
                emptyEl.style.display = 'block';
                return;
            }

            emptyEl.style.display = 'block';
            emptyEl.style.display = 'none';

            items.forEach((item) => {
                if (!item || !item.result_url) {
                    return;
                }

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'history-item';
                btn.setAttribute('aria-label', I18N.historyItemAria);

                const img = document.createElement('img');
                img.src = item.result_url;
                img.alt = I18N.historyImageAlt;

                const time = document.createElement('span');
                time.className = 'history-item-time';
                time.textContent = formatHistoryDateLabel(item.created_at);

                btn.appendChild(img);
                btn.appendChild(time);
                btn.addEventListener('click', () => {
                    const currentCard = document.querySelector('#productGrid .card.selected') || document.querySelector('#productGrid .card');
                    if (currentCard) {
                        openTryOnModal(currentCard);
                    }
                    showHistoryResult(item.result_url);
                    closeFloatingHistoryPanel();
                });
                listEl.appendChild(btn);
            });
        }

        function openFloatingHistoryPanel() {
            const panel = document.getElementById('floatingHistoryPanel');
            if (!panel) {
                return;
            }
            panel.classList.add('active');
            panel.setAttribute('aria-hidden', 'false');
            refreshHistory();
        }

        function closeFloatingHistoryPanel() {
            const panel = document.getElementById('floatingHistoryPanel');
            if (!panel) {
                return;
            }
            panel.classList.remove('active');
            panel.setAttribute('aria-hidden', 'true');
        }

        async function refreshHistory() {
            try {
                const response = await fetch(TRYON_HISTORY_URL, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Tryon-Device-Id': resolveTryOnDeviceId(),
                    },
                });

                const payload = await response.json();
                if (!response.ok) {
                    return;
                }

                renderHistory(payload.items || []);
                renderFloatingHistory(payload.items || []);
            } catch (error) {
                // History failure should not block the page.
            }
        }

        function updateQuotaUI(quota) {
            const el = document.getElementById('remainingQuotaText');
            if (!el || !quota) {
                return;
            }

            const limit = Number(quota.daily_limit ?? 3);
            const remaining = Number(quota.remaining ?? 0);
            remainingDailyQuota = remaining;
            el.textContent = `${remaining} / ${limit}`;

            if (remaining <= 0) {
                setStatus(I18N.dailyLimitReached, 'error');
            }

            const btn = document.getElementById('generateBtn');
            if (btn && !btn.disabled && remaining <= 0) {
                btn.disabled = true;
            }
        }

        async function refreshQuota() {
            try {
                const response = await fetch(@json(route('public.tryon.quota.show', ['seller_slug' => $seller->slug])), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Tryon-Device-Id': resolveTryOnDeviceId(),
                    },
                });

                const payload = await response.json();
                if (!response.ok) {
                    return;
                }

                updateQuotaUI(payload);
            } catch (error) {
                // Quota failure should not block the page.
            }
        }

        async function submitTryOn() {
            const customerPreview = document.getElementById('customerPreview');
            const resultPreview = document.getElementById('resultPreview');
            const resultPlaceholder = document.getElementById('resultPlaceholder');
            const customerPhotoInput = document.getElementById('customerPhoto');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (!selectedProductId) {
                setStatus(I18N.selectProductFirst, 'error');
                return;
            }

            const file = customerPhotoInput.files && customerPhotoInput.files[0] ? customerPhotoInput.files[0] : null;
            if (!file || !customerPreview.getAttribute('src')) {
                setStatus(I18N.uploadModelFirst, 'error');
                showResultPlaceholderMessage(I18N.uploadModelFirst, 'error');
                return;
            }

            if (remainingDailyQuota !== null && remainingDailyQuota <= 0) {
                setStatus(I18N.dailyLimitReached, 'error');
                showResultPlaceholderMessage(I18N.dailyLimitReached, 'error');
                setLoading(false);
                return;
            }

            setLoading(true);
            setStatus('', '');
            resultPreview.removeAttribute('src');
            resultPreview.style.display = 'none';
            resultPlaceholder.style.display = 'block';

            try {
                const formData = new FormData();
                formData.append('product_id', String(selectedProductId));
                formData.append('customer_photo', file);

                const createResponse = await fetch(@json(route('public.tryon.sessions.store', ['seller_slug' => $seller->slug])), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Tryon-Device-Id': resolveTryOnDeviceId(),
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const createPayload = await createResponse.json();
                if (!createResponse.ok) {
                    if (createPayload.quota) {
                        updateQuotaUI(createPayload.quota);
                    }
                    throw new Error(createPayload.message || I18N.failedCreateSession);
                }

                if (createPayload.quota) {
                    updateQuotaUI(createPayload.quota);
                }

                await pollTryOnStatus(createPayload.id);
            } catch (error) {
                setStatus(error.message || 'Terjadi kesalahan saat generate try-on.', 'error');
                showResultPlaceholderMessage(error.message || 'Terjadi kesalahan saat generate try-on.', 'error');
                setLoading(false);
            }
        }

        async function pollTryOnStatus(sessionId) {
            const resultPreview = document.getElementById('resultPreview');
            const resultPlaceholder = document.getElementById('resultPlaceholder');
            const statusUrlBase = @json(url('/'));

            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }

            let attempts = 0;
            const maxAttempts = 90;

            pollTimer = setInterval(async () => {
                attempts += 1;
                try {
                    const response = await fetch(`${statusUrlBase}/${@json($seller->slug)}/try-on/sessions/${sessionId}`, {
                        headers: {
                            'Accept': 'application/json'
                        },
                    });

                    const payload = await response.json();
                    if (!response.ok) {
                        throw new Error(payload.message || I18N.failedCheckStatus);
                    }

                    if (payload.status === 'completed') {
                        clearInterval(pollTimer);
                        pollTimer = null;

                        if (payload.result_url) {
                            resultPreview.src = payload.result_url;
                            resultPreview.style.display = 'block';
                            resultPlaceholder.style.display = 'none';
                        }

                        setStatus(I18N.generateDone, 'success');
                        setLoading(false);
                        refreshHistory();
                        return;
                    }

                    if (payload.status === 'failed') {
                        clearInterval(pollTimer);
                        pollTimer = null;
                        const errorMessage = payload.error_message || 'Try-on gagal diproses.';
                        setStatus(errorMessage, 'error');
                        showResultPlaceholderMessage(errorMessage, 'error');
                        setLoading(false);
                        return;
                    }

                    if (attempts >= maxAttempts) {
                        clearInterval(pollTimer);
                        pollTimer = null;
                        const timeoutMessage = I18N.processingRetryLater;
                        setStatus(timeoutMessage, 'error');
                        showResultPlaceholderMessage(timeoutMessage, 'error');
                        setLoading(false);
                    }
                } catch (error) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                    const pollingErrorMessage = error.message || I18N.failedPolling;
                    setStatus(pollingErrorMessage, 'error');
                    showResultPlaceholderMessage(pollingErrorMessage, 'error');
                    setLoading(false);
                }
            }, 2000);
        }

        (function initPage() {
            const current = document.querySelector('#productGrid .card.selected');
            if (current) {
                selectProduct(current);
            }

            const autoOpenTrigger = document.getElementById('autoOpenTryOnTrigger');
            if (autoOpenTrigger) {
                openTryOnModal(autoOpenTrigger);
            }

            const floatingBtn = document.getElementById('floatingHistoryBtn');
            const floatingClose = document.getElementById('floatingHistoryClose');
            const floatingPanel = document.getElementById('floatingHistoryPanel');
            if (floatingBtn) {
                floatingBtn.addEventListener('click', openFloatingHistoryPanel);
            }
            if (floatingClose) {
                floatingClose.addEventListener('click', closeFloatingHistoryPanel);
            }
            if (floatingPanel) {
                document.addEventListener('click', function(event) {
                    if (!floatingPanel.classList.contains('active')) {
                        return;
                    }
                    if (floatingPanel.contains(event.target) || (floatingBtn && floatingBtn.contains(event.target))) {
                        return;
                    }
                    closeFloatingHistoryPanel();
                });
            }

            refreshQuota();
            refreshHistory();
        })();
    </script>
</body>

</html>
