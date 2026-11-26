<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Suivi chez le fournisseur (LWS) : dernière synchronisation de l'échéance (achats différés),
 * et rappels d'échéance envoyés aux clients (domaines et services), pour n'en envoyer qu'un par palier.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_domains', function (Blueprint $table) {
            $table->timestamp('synced_at')->nullable();
            $table->unsignedSmallInteger('reminded_days')->nullable(); // dernier palier de rappel envoyé (30, 7, 1)
        });
        Schema::table('platform_services', function (Blueprint $table) {
            $table->timestamp('synced_at')->nullable();
            $table->unsignedSmallInteger('reminded_days')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('platform_domains', fn (Blueprint $table) => $table->dropColumn(['synced_at', 'reminded_days']));
        Schema::table('platform_services', fn (Blueprint $table) => $table->dropColumn(['synced_at', 'reminded_days']));
    }
};
