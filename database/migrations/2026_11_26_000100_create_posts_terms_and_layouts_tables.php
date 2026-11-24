<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Contenu dynamique et constructeur de thème :
 *   - posts : articles (blog, actualités), rédigés dans le builder comme les pages ;
 *   - terms + post_term : catégories et étiquettes ;
 *   - layouts : gabarits du thème (en-tête, pied de page, article, archive, recherche, 404)
 *     conçus dans le builder, avec leurs conditions d'affichage ;
 *   - content_revisions : historique des versions des articles et des gabarits.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('locale', 10)->default('fr');
            $table->uuid('translation_group')->index();
            $table->string('status', 20)->default('draft'); // draft | published (programmé si published_at est futur)
            $table->json('content')->nullable();
            $table->json('draft_content')->nullable();
            $table->text('excerpt')->nullable();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['locale', 'slug']);
            $table->index(['status', 'published_at']);
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('taxonomy', 20); // category | tag
            $table->string('name', 120);
            $table->string('slug', 120);
            $table->string('locale', 10)->default('fr');
            $table->uuid('translation_group')->index();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->timestamps();

            $table->unique(['taxonomy', 'locale', 'slug']);
        });

        Schema::create('post_term', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->primary(['post_id', 'term_id']);
        });

        Schema::create('layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('type', 20); // header | footer | single | archive | search | not_found
            $table->string('locale', 10)->nullable(); // null : toutes les langues
            $table->string('status', 20)->default('draft'); // draft | published (= actif)
            $table->json('conditions')->nullable();
            $table->smallInteger('priority')->default(0);
            $table->json('content')->nullable();
            $table->json('draft_content')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
        });

        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->morphs('revisable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('content');
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
        Schema::dropIfExists('layouts');
        Schema::dropIfExists('post_term');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('posts');
    }
};
