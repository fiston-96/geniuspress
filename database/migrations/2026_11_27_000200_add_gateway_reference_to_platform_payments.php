<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Identifiant du paiement chez le prestataire (ex. l'identifiant d'une collecte Monerails).
        Schema::table('platform_payments', function (Blueprint $table) {
            $table->string('gateway_reference', 100)->nullable()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('platform_payments', function (Blueprint $table) {
            $table->dropColumn('gateway_reference');
        });
    }
};
