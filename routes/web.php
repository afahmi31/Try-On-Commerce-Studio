<?php

use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\Auth\SetupController;
use App\Http\Controllers\Public\SellerPublicController;
use App\Http\Controllers\Public\TryOnPublicController;
use App\Http\Controllers\Seller\SellerDashboardController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Controllers\Seller\SellerSettingsController;
use App\Support\InitialSetup;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (InitialSetup::isCompleted()) {
        return redirect()->route('login');
    }

    return redirect()->route('setup');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/setup', [SetupController::class, 'show'])->name('setup');
    Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [WebAuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('dashboard')->middleware(['auth', 'seller.locale', 'role:seller'])->group(function (): void {
    Route::get('/', [SellerDashboardController::class, 'index'])->name('seller.dashboard');
    Route::post('/model', [SellerDashboardController::class, 'updateModel'])->name('seller.dashboard.model.update');
    Route::get('/products', [SellerProductController::class, 'index'])->name('seller.products.index');
    Route::get('/settings', [SellerSettingsController::class, 'index'])->name('seller.settings.index');
    Route::post('/settings', [SellerSettingsController::class, 'update'])->name('seller.settings.update');
    Route::post('/settings/test-api-key', [SellerSettingsController::class, 'testApiKey'])->name('seller.settings.test-api-key');
    Route::post('/products', [SellerProductController::class, 'store'])->name('seller.products.store');
    Route::patch('/products/{productId}', [SellerProductController::class, 'update'])->name('seller.products.update');
    Route::delete('/products/{productId}', [SellerProductController::class, 'destroy'])->name('seller.products.destroy');
    Route::post('/products/{productId}/images', [SellerProductController::class, 'addImage'])->name('seller.products.images.store');
});

Route::prefix('{seller_slug}/try-on')->middleware('seller.locale')->group(function (): void {
    Route::post('/sessions', [TryOnPublicController::class, 'store'])
        ->name('public.tryon.sessions.store');

    Route::get('/quota', [TryOnPublicController::class, 'quota'])
        ->name('public.tryon.quota.show');

    Route::get('/sessions/{sessionId}', [TryOnPublicController::class, 'show'])
        ->middleware('throttle:tryon-public-polling')
        ->name('public.tryon.sessions.show');

    Route::get('/sessions', [TryOnPublicController::class, 'history'])
        ->name('public.tryon.sessions.history');
});

$reservedSellerSlugPattern = implode('|', array_map(static fn (string $slug): string => preg_quote($slug, '/'), config('tryon.reserved_seller_slugs', [])));

Route::get('/{seller_slug}/{product_ref?}', [SellerPublicController::class, 'index'])->middleware('seller.locale')
    ->where('seller_slug', '^(?!('.$reservedSellerSlugPattern.')$)[A-Za-z0-9\-]+$')
    ->where('product_ref', '[A-Za-z0-9\-]+')
    ->name('public.seller.page');
