<?php

use App\Platform\Http\Controllers\Api;
use App\Platform\Http\Middleware\AuthenticateLicense;
use App\Platform\Http\Middleware\EnsurePlatformEnabled;
use Illuminate\Support\Facades\Route;

/*
 * API de la plateforme pour les sites GeniusPress (/api/platform/v1), sans session ni cookie.
 * Authentification par clé de licence : « Authorization: Bearer gpl_… ».
 */
Route::middleware(EnsurePlatformEnabled::class)->name('platform.api.')->group(function () {
    Route::get('/catalog/prices', [Api\AccountController::class, 'prices'])->middleware('throttle:60,1')->name('prices');
    Route::post('/payments/{gateway}/webhook', Api\WebhookController::class)->middleware('throttle:120,1')->where('gateway', '[a-z0-9_-]{2,30}')->name('webhook');

    Route::get('/account', [Api\AccountController::class, 'show'])->middleware(['throttle:60,1', AuthenticateLicense::class])->name('account');
    Route::post('/ai/messages', [Api\RelayController::class, 'messages'])->middleware(['throttle:platform-relay', AuthenticateLicense::class])->name('ai');
    Route::get('/templates/{template}/download', [Api\AccountController::class, 'template'])->middleware(['throttle:30,1', AuthenticateLicense::class])->name('template');
});
