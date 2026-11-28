<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * « Mes blocs » du builder : un conteneur ou un widget enregistré pour être réutilisé
 * sur d'autres pages du site (contenu repassé par ContentSanitizer à l'enregistrement).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('builder_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->json('node');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builder_blocks');
    }
};
