<?php

use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\Admin\AppearanceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LayoutController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\TermController;
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

    // Articles, catégories et étiquettes
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::get('/posts/{post}/builder', [PostController::class, 'builder'])->name('posts.builder');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::put('/posts/{post}/content', [PostController::class, 'saveContent'])->name('posts.content');
    Route::post('/posts/{post}/preview', [PostController::class, 'preview'])->name('posts.preview');
    Route::post('/posts/{post}/revisions/{revision}/restore', [PostController::class, 'restoreRevision'])->name('posts.revisions.restore');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::get('/terms', [TermController::class, 'index'])->name('terms.index');
    Route::post('/terms', [TermController::class, 'store'])->name('terms.store');
    Route::put('/terms/{term}', [TermController::class, 'update'])->name('terms.update');
    Route::delete('/terms/{term}', [TermController::class, 'destroy'])->name('terms.destroy');

    // Assistant IA
    Route::get('/ai/status', [AiController::class, 'status'])->name('ai.status');
    Route::put('/pages/{page}/meta', [AiController::class, 'applyMeta'])->name('pages.meta');
    Route::middleware('throttle:ai')->group(function () {
        Route::post('/ai/text', [AiController::class, 'text'])->name('ai.text');
        Route::post('/ai/pages/{page}/generate', [AiController::class, 'generate'])->name('ai.generate');
        Route::post('/ai/pages/{page}/translate', [AiController::class, 'translate'])->name('ai.translate');
        Route::post('/ai/pages/{page}/audit', [AiController::class, 'audit'])->name('ai.audit');
        Route::post('/ai/pages/{page}/fill', [AiController::class, 'fill'])->middleware('can:admin')->name('ai.fill');
    });

    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::patch('/media/{media}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    Route::middleware('can:admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
        Route::post('/templates/recommend', [TemplateController::class, 'recommend'])->middleware('throttle:ai')->name('templates.recommend');
        Route::post('/templates/{template}/install', [TemplateController::class, 'install'])->where('template', '[a-z0-9-]{2,60}')->name('templates.install');
        Route::get('/templates/fill', [TemplateController::class, 'fill'])->name('templates.fill');

        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
        Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

        Route::put('/posts-settings', [PostController::class, 'settings'])->name('posts.settings');

        // Constructeur de thème
        Route::get('/layouts', [LayoutController::class, 'index'])->name('layouts.index');
        Route::post('/layouts', [LayoutController::class, 'store'])->name('layouts.store');
        Route::get('/layouts/{layout}/edit', [LayoutController::class, 'edit'])->name('layouts.edit');
        Route::get('/layouts/{layout}/builder', [LayoutController::class, 'builder'])->name('layouts.builder');
        Route::put('/layouts/{layout}', [LayoutController::class, 'update'])->name('layouts.update');
        Route::put('/layouts/{layout}/content', [LayoutController::class, 'saveContent'])->name('layouts.content');
        Route::post('/layouts/{layout}/preview', [LayoutController::class, 'preview'])->name('layouts.preview');
        Route::post('/layouts/{layout}/revisions/{revision}/restore', [LayoutController::class, 'restoreRevision'])->name('layouts.revisions.restore');
        Route::delete('/layouts/{layout}', [LayoutController::class, 'destroy'])->name('layouts.destroy');

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

// Plan du site pour les moteurs de recherche
Route::get('/sitemap.xml', \App\Http\Controllers\SitemapController::class)->name('sitemap');

// Site public. La route de secours passe après toutes les autres, y compris
// celles déclarées par les plugins.
Route::get('/', PublicPageController::class)->name('home');
Route::fallback(PublicPageController::class)->name('page.show');
