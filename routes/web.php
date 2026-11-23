<?php

use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

// Installation (uniquement tant qu'aucun compte n'existe)
Route::get('/install', [InstallController::class, 'show'])->name('install');
Route::post('/install', [InstallController::class, 'store'])->middleware('throttle:10,1');

// Connexion
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'show'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'login'])->middleware('throttle:login');
});
Route::post('/admin/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Administration
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/create', [PageController::class, 'create'])->name('pages.create');
    Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
    Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
    Route::get('/pages/{page}/builder', [PageController::class, 'builder'])->name('pages.builder');
    Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
    Route::put('/pages/{page}/content', [PageController::class, 'saveContent'])->name('pages.content');
    Route::post('/pages/{page}/preview', [PageController::class, 'preview'])->name('pages.preview');
    Route::post('/pages/{page}/revisions/{revision}/restore', [PageController::class, 'restoreRevision'])->name('pages.revisions.restore');
    Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');

    // Assistant IA
    Route::get('/ai/status', [AiController::class, 'status'])->name('ai.status');
    Route::put('/pages/{page}/meta', [AiController::class, 'applyMeta'])->name('pages.meta');
    Route::middleware('throttle:ai')->group(function () {
        Route::post('/ai/text', [AiController::class, 'text'])->name('ai.text');
        Route::post('/ai/pages/{page}/generate', [AiController::class, 'generate'])->name('ai.generate');
        Route::post('/ai/pages/{page}/translate', [AiController::class, 'translate'])->name('ai.translate');
        Route::post('/ai/pages/{page}/audit', [AiController::class, 'audit'])->name('ai.audit');
    });

    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::patch('/media/{media}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    Route::middleware('can:admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

// Site public (en dernier : attrape toutes les autres adresses)
Route::get('/{path?}', PublicPageController::class)
    ->where('path', '^(?!admin|install|storage|build|up).*$')
    ->name('page.show');
