<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Jetons « mot de passe oublié » des clients de la plateforme (config/auth.php, courtier customers). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_password_reset_tokens');
    }
};
