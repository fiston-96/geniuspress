<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Messages envoyés par les formulaires de contact du site public. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('widget_id', 40);
            $table->json('data');
            $table->string('ip_hash', 64)->nullable(); // empreinte de l'adresse IP, jamais l'adresse elle-même
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
