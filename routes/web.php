<?php

use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\Admin\AppearanceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TransferController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CatalogDemoController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\PluginAssetController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

// Installation (inaccessible une fois GeniusPress installé)
Route::prefix('install')->middleware(\App\Http\Middleware\InstallerOpen::class)->group(function () {
    Route::get('/', [InstallController::class, 'show'])->name('install');
    Route::get('/database', [InstallController::class, 'database'])->name('install.database');
    Route::post('/database', [InstallController::class, 'saveDatabase'])->middleware('throttle:20,1');
    Route::get('/site', [InstallController::class, 'site'])->name('install.site');
    Route::post('/site', [InstallController::class, 'saveSite'])->middleware('throttle:10,1');
    Route::get('/language/{locale}', [InstallController::class, 'language'])->name('install.language');
});

// Connexion
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'show'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'login'])->middleware('throttle:login');
});
Route::post('/admin/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Administration
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::put('/locale', LocaleController::class)->name('locale');

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

        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
        Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('/appearance', [AppearanceController::class, 'edit'])->name('appearance.edit');
        Route::put('/appearance', [AppearanceController::class, 'update'])->name('appearance.update');

        Route::get('/transfer', [TransferController::class, 'index'])->name('transfer.index');
        Route::get('/transfer/export', [TransferController::class, 'export'])->name('transfer.export');
        Route::post('/transfer/import', [TransferController::class, 'import'])->name('transfer.import');

        Route::get('/plugins', [PluginController::class, 'index'])->name('plugins.index');
        Route::post('/plugins/{slug}/activate', [PluginController::class, 'activate'])->name('plugins.activate');
        Route::post('/plugins/{slug}/deactivate', [PluginController::class, 'deactivate'])->name('plugins.deactivate');
    });
});

// Démonstrations des modèles du catalogue (en local, et sur geniuspress.site)
Route::get('/catalogue/{template}/media/{file}', [CatalogDemoController::class, 'media'])->name('catalog.media');
Route::get('/catalogue/{template}/{path?}', [CatalogDemoController::class, 'show'])->where('path', '.*')->name('catalog.demo');

// Formulaires de contact du site public
Route::post('/formulaire', [FormController::class, 'submit'])->middleware('throttle:forms')->name('forms.submit');

// Fichiers publics des plugins (dossier public/ de chaque plugin)
Route::get('/plugin-assets/{slug}/{path}', PluginAssetController::class)
    ->where('path', '.*')->name('plugin.asset');

// Site public. La route de secours passe après toutes les autres, y compris
// celles déclarées par les plugins.
Route::get('/', PublicPageController::class)->name('home');
Route::fallback(PublicPageController::class)->name('page.show');
