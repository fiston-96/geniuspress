<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Newsletter intégrée : abonnés (double confirmation), campagnes et envois (un par abonné
 * et par campagne, pour ne jamais envoyer deux fois et reprendre après une coupure).
 * Données personnelles : jamais incluses dans l'export du site (app/Transfer).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 190)->unique();
            $table->string('name', 120)->nullable();
            $table->string('locale', 5)->default('fr');
            $table->string('status', 20)->default('pending'); // pending | subscribed | unsubscribed
            $table->string('token', 64)->unique(); // confirmation et désinscription
            $table->string('source', 120)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 190);
            $table->text('content'); // Markdown simple (titres, gras, listes, liens)
            $table->string('locale', 5)->nullable(); // null = tous les abonnés
            $table->string('status', 20)->default('draft'); // draft | sending | sent
            $table->unsignedInteger('recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('newsletter_campaigns')->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained('newsletter_subscribers')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending | sent | failed
            $table->string('error', 255)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['campaign_id', 'subscriber_id']);
            $table->index(['status', 'campaign_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_deliveries');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
