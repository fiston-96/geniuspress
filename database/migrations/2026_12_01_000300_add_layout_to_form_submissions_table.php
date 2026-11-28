<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Messages reçus par un formulaire placé dans un gabarit du thème (popup, en-tête…),
 * et nom du formulaire avancé qui les a envoyés.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->foreignId('layout_id')->nullable()->after('page_id')->constrained()->nullOnDelete();
            $table->string('form_name', 120)->nullable()->after('widget_id');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('layout_id');
            $table->dropColumn('form_name');
        });
    }
};
