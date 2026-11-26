<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Identifiant Google (« sub ») des comptes reliés à « Continuer avec Google ». */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'platform_customers'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'google_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('google_id', 64)->nullable()->unique()->after('email');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['users', 'platform_customers'] as $table) {
            if (Schema::hasColumn($table, 'google_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropUnique(['google_id']);
                    $t->dropColumn('google_id');
                });
            }
        }
    }
};
