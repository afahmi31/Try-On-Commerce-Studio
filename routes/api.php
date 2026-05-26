<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Seller\SellerController;
use App\Http\Controllers\Api\TryOn\TryOnSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware(['auth:sanctum', 'role:seller'])->prefix('seller')->group(function (): void {
    Route::get('/me', [SellerController::class, 'profile']);
    Route::get('/profile', [SellerController::class, 'profile']);
    Route::patch('/profile', [SellerController::class, 'updateProfile']);
    Route::get('/products', [SellerController::class, 'products']);
    Route::post('/products', [SellerController::class, 'storeProduct']);
    Route::get('/products/{id}', [SellerController::class, 'showProduct']);
    Route::patch('/products/{id}', [SellerController::class, 'updateProduct']);
    Route::delete('/products/{id}', [SellerController::class, 'destroyProduct']);
    Route::post('/products/{id}/images', [SellerController::class, 'uploadProductImage']);
});

Route::middleware(['auth:sanctum', 'role:seller'])->prefix('tryon')->group(function (): void {
    Route::post('/sessions', [TryOnSessionController::class, 'store']);
    Route::get('/sessions/{id}', [TryOnSessionController::class, 'show']);
});
