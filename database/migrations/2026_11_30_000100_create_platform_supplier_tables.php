<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Fournisseurs (LWS) : coûts d'achat à côté des prix de vente, offres partenaires
 * (hébergement web, mail pro, stockage), services livrés aux clients, adresse
 * du titulaire des domaines et domaine branché sur un site hébergé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_customers', function (Blueprint $table) {
            // Coordonnées du titulaire, exigées par les registres de noms de domaine.
            $table->string('address', 190)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city', 100)->nullable();
            // Identifiants du client chez les fournisseurs (« lws » → numéro de contact).
            $table->json('provider_ids')->nullable();
        });

        Schema::table('platform_plans', function (Blueprint $table) {
            // null : offre GeniusPress ; « lws » : livrée par l'API LWS ; « manual » : livrée par l'équipe.
            $table->string('provider', 20)->nullable();
            $table->string('provider_ref', 120)->nullable(); // nom de l'offre chez le fournisseur (« LWS Perso »)
            $table->unsignedBigInteger('cost')->nullable(); // coût d'achat par période, plus petite unité
            $table->string('cost_currency', 3)->nullable();
            $table->boolean('needs_domain')->default(false);
        });

        Schema::table('platform_tld_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_register')->nullable();
            $table->unsignedBigInteger('cost_renew')->nullable();
            $table->string('cost_currency', 3)->nullable();
        });

        Schema::table('platform_domains', function (Blueprint $table) {
            $table->string('site_slug', 40)->nullable(); // site hébergé sur lequel le domaine est branché
        });

        Schema::create('platform_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('platform_plans')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('platform_orders')->nullOnDelete();
            $table->string('kind', 20); // web_hosting | mail | storage
            $table->string('provider', 20); // lws | manual
            $table->string('provider_ref', 120)->nullable();
            $table->string('domain', 190)->nullable();
            $table->text('request')->nullable(); // précisions du client (adresses souhaitées…)
            $table->string('status', 20)->default('pending'); // pending | active | failed | canceled | expired
            $table->string('pending_action', 20)->nullable(); // create | renew : à faire par l'équipe
            $table->string('error', 255)->nullable();
            $table->text('note')->nullable(); // message de l'équipe au client (jamais de mot de passe)
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
            $table->index('pending_action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_services');
        Schema::table('platform_domains', fn (Blueprint $table) => $table->dropColumn('site_slug'));
        Schema::table('platform_tld_prices', fn (Blueprint $table) => $table->dropColumn(['cost_register', 'cost_renew', 'cost_currency']));
        Schema::table('platform_plans', fn (Blueprint $table) => $table->dropColumn(['provider', 'provider_ref', 'cost', 'cost_currency', 'needs_domain']));
        Schema::table('platform_customers', fn (Blueprint $table) => $table->dropColumn(['address', 'postal_code', 'city', 'provider_ids']));
    }
};
