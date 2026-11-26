<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Console d'exploitation (/console) : comptes des exploitants de GeniusPress, distincts des
 * utilisateurs du CMS (users) et des clients (platform_customers).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_operators', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 190)->unique();
            $table->string('password');
            $table->string('locale', 5)->default('fr');
            $table->string('status', 20)->default('active'); // active | disabled
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('platform_operator_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Ajustements de crédits faits depuis la console : l'exploitant qui les a faits.
        Schema::table('platform_credit_transactions', function (Blueprint $table) {
            $table->foreignId('operator_id')->nullable()->after('user_id')->constrained('platform_operators')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('platform_credit_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('operator_id');
        });
        Schema::dropIfExists('platform_operator_password_reset_tokens');
        Schema::dropIfExists('platform_operators');
    }
};
