<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\NotificationController;
use App\Platform\Http\Controllers\Admin;
use App\Platform\Http\Controllers\Console;
use App\Platform\Http\Controllers\Customer;
use App\Platform\Http\Middleware\EnsurePlatformEnabled;
use Illuminate\Support\Facades\Route;

/*
 * Plateforme commerciale (pages web). Répond 404 sans GP_PLATFORM=true.
 * Voir docs/developpeurs/plateforme-commerciale.md.
 */
Route::middleware(EnsurePlatformEnabled::class)->group(function () {

    // ─── Espace client ───────────────────────────────────────────────────
    Route::prefix('compte')->name('platform.')->group(function () {
        Route::middleware('guest:customer')->group(function () {
            Route::get('/inscription', [Customer\AuthController::class, 'showRegister'])->name('register');
            Route::post('/inscription', [Customer\AuthController::class, 'register'])->middleware('throttle:platform-auth');
            Route::get('/connexion', [Customer\AuthController::class, 'showLogin'])->name('login');
            Route::post('/connexion', [Customer\AuthController::class, 'login'])->middleware('throttle:platform-auth');

            // Mot de passe oublié (courtier « customers », table platform_password_reset_tokens).
            Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'request'])->defaults('audience', 'customer')->name('password.request');
            Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'email'])->defaults('audience', 'customer')->middleware('throttle:6,1')->name('password.email');
            Route::get('/nouveau-mot-de-passe/{token}', [PasswordResetController::class, 'edit'])->defaults('audience', 'customer')->name('password.reset');
            Route::post('/nouveau-mot-de-passe', [PasswordResetController::class, 'update'])->defaults('audience', 'customer')->middleware('throttle:6,1')->name('password.update');

            // « Continuer avec Google » : connexion ou création du compte client.
            Route::get('/google', [GoogleController::class, 'redirect'])->defaults('audience', 'customer')->middleware('throttle:20,1')->name('google.redirect');
            Route::get('/google/retour', [GoogleController::class, 'callback'])->defaults('audience', 'customer')->middleware('throttle:20,1')->name('google.callback');
        });

        Route::middleware('auth:customer')->group(function () {
            Route::post('/deconnexion', [Customer\AuthController::class, 'logout'])->name('logout');

            Route::name('customer.')->group(function () {
                Route::get('/', [Customer\DashboardController::class, 'index'])->name('dashboard');
                Route::get('/credits', [Customer\DashboardController::class, 'credits'])->name('credits');
                Route::get('/affiliation', [Customer\ReferralController::class, 'index'])->name('referrals');
                Route::post('/affiliation/retrait', [Customer\ReferralController::class, 'payout'])->middleware('throttle:5,1')->name('referrals.payout');
                Route::get('/notifications', [NotificationController::class, 'index'])->defaults('audience', 'customer')->name('notifications.index');
                Route::post('/notifications/tout-lire', [NotificationController::class, 'readAll'])->defaults('audience', 'customer')->name('notifications.read');
                Route::get('/notifications/{id}', [NotificationController::class, 'open'])->defaults('audience', 'customer')->whereUuid('id')->name('notifications.open');
                Route::get('/offres', [Customer\ShopController::class, 'plans'])->name('plans');
                Route::post('/offres/{plan:slug}', [Customer\ShopController::class, 'buyPlan'])->name('plans.buy');
                Route::get('/modeles', [Customer\ShopController::class, 'templates'])->name('templates');
                Route::post('/modeles/{template}', [Customer\ShopController::class, 'buyTemplate'])->where('template', '[a-z0-9-]{2,60}')->name('templates.buy');
                Route::post('/abonnements/{subscription}/resilier', [Customer\ShopController::class, 'cancel'])->name('subscriptions.cancel');

                Route::get('/domaines', [Customer\DomainController::class, 'index'])->name('domains');
                Route::post('/domaines', [Customer\DomainController::class, 'buy'])->middleware('throttle:20,1')->name('domains.buy');
                Route::post('/domaines/{domain}/renouveler', [Customer\DomainController::class, 'renew'])->name('domains.renew');

                Route::get('/commandes/{order:number}', [Customer\OrderController::class, 'show'])->name('orders.show');
                Route::post('/commandes/{order:number}/payer', [Customer\OrderController::class, 'pay'])->middleware('throttle:10,1')->name('orders.pay');
                Route::post('/commandes/{order:number}/verifier', [Customer\OrderController::class, 'check'])->middleware('throttle:20,1')->name('orders.check');

                Route::post('/licences', [Customer\LicenseController::class, 'store'])->middleware('throttle:10,1')->name('licenses.store');
                Route::delete('/licences/{license}', [Customer\LicenseController::class, 'destroy'])->name('licenses.destroy');
            });

            // Prestataire de test : simulation du paiement (jamais en production sauf GP_PLATFORM_SANDBOX=true).
            Route::get('/paiement-test/{payment:reference}', [Customer\SandboxController::class, 'show'])->name('sandbox.show');
            Route::post('/paiement-test/{payment:reference}', [Customer\SandboxController::class, 'complete'])->name('sandbox.complete');
        });
    });

    // ─── Administration de l'exploitant ──────────────────────────────────
    // ─── Console d'exploitation (module séparé du CMS, comptes « operator ») ───
    Route::prefix('console')->name('console.')->middleware('guest:operator')->group(function () {
        Route::get('/connexion', [Console\AuthController::class, 'show'])->name('login');
        Route::post('/connexion', [Console\AuthController::class, 'login'])->middleware('throttle:platform-auth');
        Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'request'])->defaults('audience', 'operator')->name('password.request');
        Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'email'])->defaults('audience', 'operator')->middleware('throttle:6,1')->name('password.email');
        Route::get('/nouveau-mot-de-passe/{token}', [PasswordResetController::class, 'edit'])->defaults('audience', 'operator')->name('password.reset');
        Route::post('/nouveau-mot-de-passe', [PasswordResetController::class, 'update'])->defaults('audience', 'operator')->middleware('throttle:6,1')->name('password.update');
    });

    Route::prefix('console')->name('console.')->middleware('auth:operator')->group(function () {
        Route::post('/deconnexion', [Console\AuthController::class, 'logout'])->name('logout');
        Route::get('/notifications', [NotificationController::class, 'index'])->defaults('audience', 'operator')->name('notifications.index');
        Route::post('/notifications/tout-lire', [NotificationController::class, 'readAll'])->defaults('audience', 'operator')->name('notifications.read');
        Route::get('/notifications/{id}', [NotificationController::class, 'open'])->defaults('audience', 'operator')->whereUuid('id')->name('notifications.open');

        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        // Affiliation : réglages, commissions, retraits Mobile Money.
        Route::get('/affiliation', [Console\ReferralController::class, 'index'])->name('referrals.index');
        Route::put('/affiliation/reglages', [Console\ReferralController::class, 'settings'])->name('referrals.settings');
        Route::post('/affiliation/commissions/{commission}/annuler', [Console\ReferralController::class, 'cancel'])->name('referrals.cancel');
        Route::post('/affiliation/retraits/{payout}/envoye', [Console\ReferralController::class, 'paid'])->name('referrals.payouts.paid');
        Route::post('/affiliation/retraits/{payout}/refuser', [Console\ReferralController::class, 'reject'])->name('referrals.payouts.reject');

        Route::get('/customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [Admin\CustomerController::class, 'show'])->name('customers.show');
        Route::put('/customers/{customer}/status', [Admin\CustomerController::class, 'status'])->name('customers.status');
        Route::post('/customers/{customer}/credits', [Admin\CustomerController::class, 'credits'])->name('customers.credits');
        Route::delete('/licenses/{license}', [Admin\CustomerController::class, 'revokeLicense'])->name('licenses.revoke');
        Route::put('/licenses/{license}/cap', [Admin\CustomerController::class, 'licenseCap'])->name('licenses.cap');

        Route::get('/plans', [Admin\PlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [Admin\PlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [Admin\PlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [Admin\PlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [Admin\PlanController::class, 'update'])->name('plans.update');

        Route::get('/pricing', [Admin\PricingController::class, 'index'])->name('pricing.index');
        Route::put('/pricing/settings', [Admin\PricingController::class, 'settings'])->name('pricing.settings');
        Route::post('/pricing/ai-rates', [Admin\PricingController::class, 'storeRate'])->name('pricing.rates.store');
        Route::put('/pricing/ai-rates/{rate}', [Admin\PricingController::class, 'updateRate'])->name('pricing.rates.update');
        Route::delete('/pricing/ai-rates/{rate}', [Admin\PricingController::class, 'destroyRate'])->name('pricing.rates.destroy');
        Route::post('/pricing/templates', [Admin\PricingController::class, 'storeTemplate'])->name('pricing.templates.store');
        Route::delete('/pricing/templates/{price}', [Admin\PricingController::class, 'destroyTemplate'])->name('pricing.templates.destroy');
        Route::post('/pricing/tlds', [Admin\PricingController::class, 'storeTld'])->name('pricing.tlds.store');
        Route::delete('/pricing/tlds/{tld}', [Admin\PricingController::class, 'destroyTld'])->name('pricing.tlds.destroy');

        Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/deliver', [Admin\OrderController::class, 'deliver'])->name('orders.deliver');

        Route::get('/domains', [Admin\OrderController::class, 'domains'])->name('domains.index');
        Route::get('/usage', [Admin\UsageController::class, 'index'])->name('usage.index');
    });
});
