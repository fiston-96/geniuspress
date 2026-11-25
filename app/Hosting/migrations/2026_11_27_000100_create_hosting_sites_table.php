<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Sites hébergés par la plateforme (essais et offres payantes). Table de la
// plateforme seulement : chargée par HostingServiceProvider (app/Hosting/migrations) hors des sites hébergés.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_sites', function (Blueprint $table) {
            $table->id();
            // Adresse ({slug}.{domaine}) ; libérée (null) à la suppression.
            $table->string('slug', 40)->nullable()->unique();
            $table->string('name');
            $table->string('account_id', 100)->index();
            $table->string('owner_email')->nullable();
            $table->string('kind', 20)->default('trial');
            $table->string('plan', 50)->default('trial');
            $table->string('status', 20)->index();
            $table->string('host')->nullable();
            $table->string('database', 64)->nullable();
            $table->json('limits')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspend_reason', 500)->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->string('error', 1000)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_sites');
    }
};
