<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * SMS de la plateforme (Africa's Talking, puis d'autres prestataires) : journal des envois
 * (état, coût, erreur) et choix du client de recevoir les SMS importants.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('platform_customers')->nullOnDelete();
            $table->string('provider', 30)->nullable();
            $table->string('to', 20);
            $table->string('kind', 60)->nullable(); // notification d'origine (OrderPaid…) ou « test »
            $table->string('status', 20); // sent | failed | skipped
            $table->string('provider_id', 100)->nullable();
            $table->string('cost', 30)->nullable(); // tel que renvoyé : « KES 0.8000 »
            $table->string('error', 255)->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::table('platform_customers', function (Blueprint $table) {
            $table->boolean('sms_opt_in')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_sms_messages');
        Schema::table('platform_customers', fn (Blueprint $table) => $table->dropColumn('sms_opt_in'));
    }
};
