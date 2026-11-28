<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Réglages propres à un type de gabarit. Popups : déclencheur, fréquence, position,
 * apparence (App\Site\Popups::sanitize).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layouts', function (Blueprint $table) {
            $table->json('options')->nullable()->after('conditions');
        });
    }

    public function down(): void
    {
        Schema::table('layouts', fn (Blueprint $table) => $table->dropColumn('options'));
    }
};
