<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plateforme commerciale (app/Platform) : comptes clients, offres, commandes,
 * paiements, domaines, crédits IA et licences. Voir docs/developpeurs/plateforme-commerciale.md.
 * Montants : entiers dans la plus petite unité de la devise. Crédits IA : entiers en micro-dollars.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 190)->unique();
            $table->string('password');
            $table->string('phone', 30)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('company', 160)->nullable();
            $table->string('locale', 5)->default('fr');
            $table->string('status', 20)->default('active'); // active | suspended
            $table->timestamp('trial_used_at')->nullable();
            $table->bigInteger('ai_balance')->default(0); // µ$, recopie du grand livre
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('platform_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('kind', 20); // hosting | option | ai_credits
            $table->json('name');
            $table->json('description')->nullable();
            $table->unsignedBigInteger('price');
            $table->string('currency', 3);
            $table->string('period', 10); // month | year | once
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->unsignedBigInteger('ai_credits')->default(0); // µ$ inclus
            $table->json('features')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('platform_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('platform_plans')->restrictOnDelete();
            $table->string('status', 20); // trialing | active | canceled | expired
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->string('site_reference', 120)->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
        });

        Schema::create('platform_template_prices', function (Blueprint $table) {
            $table->id();
            $table->string('template_id', 60)->unique();
            $table->unsignedBigInteger('price');
            $table->string('currency', 3);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('platform_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();
            $table->foreignId('customer_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending | paid | failed | canceled
            $table->unsignedBigInteger('total');
            $table->string('currency', 3);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
        });

        Schema::create('platform_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('platform_orders')->cascadeOnDelete();
            $table->string('kind', 20); // plan | template | ai_credits | domain_register | domain_renew
            $table->string('reference', 190);
            $table->string('description', 255);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedBigInteger('amount');
            $table->json('meta')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->string('error', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('platform_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('platform_orders')->cascadeOnDelete();
            $table->string('gateway', 30);
            $table->string('method', 20); // card | mobile_money
            $table->string('operator', 30)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('reference', 100)->unique();
            $table->string('status', 20)->default('pending'); // pending | succeeded | failed
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);
            $table->string('failure', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('platform_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->string('kind', 20); // template
            $table->string('reference', 190);
            $table->foreignId('order_id')->nullable()->constrained('platform_orders')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'kind', 'reference']);
        });

        Schema::create('platform_tld_prices', function (Blueprint $table) {
            $table->id();
            $table->string('tld', 30)->unique(); // sans point : « com », « cd », « co.za »
            $table->unsignedBigInteger('register_price');
            $table->unsignedBigInteger('renew_price');
            $table->string('currency', 3);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('platform_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->string('name', 190)->unique();
            $table->string('status', 20); // active | expired | failed
            $table->string('registrar', 30);
            $table->string('registrar_reference', 120)->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_ai_rates', function (Blueprint $table) {
            $table->id();
            $table->string('model', 80)->unique();
            $table->string('label', 80);
            // USD par million de jetons, tels que publiés par Anthropic.
            $table->decimal('input_price', 12, 6);
            $table->decimal('output_price', 12, 6);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('platform_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('key_prefix', 20)->unique();
            $table->string('key_hash', 64);
            $table->string('site_url', 255)->nullable();
            $table->string('status', 20)->default('active'); // active | revoked
            $table->unsignedBigInteger('monthly_cap')->nullable(); // µ$
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_ai_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->foreignId('license_id')->nullable()->constrained('platform_licenses')->nullOnDelete();
            $table->string('action', 40)->nullable();
            $table->string('model', 80);
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedBigInteger('cost')->default(0);   // coût réel, µ$
            $table->unsignedBigInteger('billed')->default(0); // débité au client, µ$
            $table->string('status', 20); // ok | refused | error | blocked
            $table->string('error', 255)->nullable();
            $table->string('ip', 45)->nullable();
            $table->unsignedInteger('duration_ms')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['license_id', 'created_at']);
        });

        Schema::create('platform_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->bigInteger('amount'); // µ$, positif = crédit
            $table->bigInteger('balance_after');
            $table->string('kind', 20); // purchase | grant | usage | adjustment
            $table->string('reference', 60)->nullable();
            $table->string('description', 255);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // exploitant, pour un ajustement
            $table->timestamp('created_at')->useCurrent();
            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['credit_transactions', 'ai_usages', 'licenses', 'ai_rates', 'domains', 'tld_prices', 'entitlements', 'payments', 'order_items', 'orders', 'template_prices', 'subscriptions', 'plans', 'customers'] as $table) {
            Schema::dropIfExists('platform_'.$table);
        }
    }
};
