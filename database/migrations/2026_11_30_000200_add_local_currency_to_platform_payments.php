<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Paiement Mobile Money dans la devise du pays du client (CDF, XAF, XOF…) pour une commande
 * dans une autre devise (USD) : montant d'origine gardé à côté du montant payé. Et validation
 * par code (OTP) pour les opérateurs qui en envoient un.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_payments', function (Blueprint $table) {
            $table->string('country', 2)->nullable(); // pays du portefeuille Mobile Money
            $table->unsignedBigInteger('original_amount')->nullable(); // total de la commande converti
            $table->string('original_currency', 3)->nullable();
            $table->string('awaiting', 20)->nullable(); // « otp » : code à saisir par le client
        });
    }

    public function down(): void
    {
        Schema::table('platform_payments', fn (Blueprint $table) => $table->dropColumn(['country', 'original_amount', 'original_currency', 'awaiting']));
    }
};
