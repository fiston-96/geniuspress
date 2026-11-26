<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parrainage : un client parraine un autre client et gagne une commission (25 % par défaut)
 * sur la première commande payée de son filleul, hors noms de domaine. La commission devient
 * disponible après un délai (30 jours) sur le solde de parrainage du parrain, qu'il utilise
 * pour payer ses commandes ou retire par Mobile Money (validé dans la console).
 * Montants en plus petite unité de la devise, comme partout dans la plateforme (voir Money).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_customers', function (Blueprint $table) {
            $table->string('referral_code', 16)->nullable()->unique()->after('email');
            $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('platform_customers')->nullOnDelete();
            $table->timestamp('referred_at')->nullable()->after('referred_by');
        });

        Schema::create('platform_referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->foreignId('order_id')->unique()->constrained('platform_orders')->cascadeOnDelete();
            $table->bigInteger('base_amount');
            $table->bigInteger('amount');
            $table->string('currency', 3);
            $table->unsignedSmallInteger('rate_bps'); // 2500 = 25 %
            $table->string('status', 20)->default('pending'); // pending | available | canceled
            $table->timestamp('available_at');
            $table->timestamp('released_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamps();
            $table->index(['status', 'available_at']);
        });

        Schema::create('platform_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->string('currency', 3);
            $table->bigInteger('amount'); // signé
            $table->bigInteger('balance_after');
            $table->string('kind', 20); // referral | payment | payout | payout_refund | reversal | adjustment
            $table->string('reference', 60)->nullable();
            $table->string('description', 255);
            $table->foreignId('operator_id')->nullable()->constrained('platform_operators')->nullOnDelete();
            $table->timestamps();
            $table->index(['customer_id', 'currency', 'id']);
        });

        Schema::create('platform_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('platform_customers')->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->string('currency', 3);
            $table->string('method', 20)->default('mobile_money');
            $table->string('mobile_operator', 30);
            $table->string('phone', 30);
            $table->string('status', 20)->default('requested'); // requested | paid | rejected
            $table->string('transfer_reference', 120)->nullable();
            $table->string('note', 255)->nullable();
            $table->foreignId('operator_id')->nullable()->constrained('platform_operators')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_payouts');
        Schema::dropIfExists('platform_wallet_transactions');
        Schema::dropIfExists('platform_referral_commissions');
        Schema::table('platform_customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by');
            $table->dropUnique(['referral_code']);
            $table->dropColumn(['referral_code', 'referred_at']);
        });
    }
};
