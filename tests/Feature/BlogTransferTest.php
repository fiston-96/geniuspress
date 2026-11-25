<?php

namespace Tests\Feature;

use App\Models\Layout;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use App\Models\User;
use App\Transfer\SiteExporter;
use App\Transfer\SiteImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/** Export et import des articles, catégories, étiquettes, gabarits du thème et réglages du blog. */
class BlogTransferTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        Setting::write(['site_name' => 'École', 'locales' => ['fr', 'en'],
            'blog' => ['locales' => ['fr' => ['base' => 'actualites', 'title' => 'Actualités']], 'per_page' => 6]]);
    }

    private function heading(string $text): array
    {
        return ['version' => 2, 'elements' => [['id' => 'w'.substr(md5($text), 0, 9), 'type' => 'heading', 'settings' => ['text' => $text]]]];
    }

    /** Un blog complet : catégories (avec parent), étiquette, articles FR et EN, image à la une, gabarits avec conditions. */
    private function buildBlog(): array
    {
        $file = UploadedFile::fake()->image('rentree.jpg', 40, 30);
        $path = Storage::disk('public')->putFileAs('uploads/2026/11', $file, 'rentree.jpg');
        $media = Media::create(['disk' => 'public', 'path' => $path, 'original_name' => 'rentree.jpg', 'mime_type' => 'image/jpeg', 'size' => $file->getSize(), 'alt' => 'Rentrée']);

        $page = Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => 'published', 'content' => $this->heading('Bienvenue')]);
        $vie = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Vie scolaire']);
        $sport = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Sport', 'parent_id' => $vie->id, 'description' => 'Matchs et tournois.']);
        $tag = Term::create(['taxonomy' => Term::TAG, 'name' => 'Rentrée']);
        $news = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'News', 'locale' => 'en']);

        $post = Post::create(['title' => 'La rentrée', 'slug' => 'la-rentree', 'status' => 'published', 'published_at' => now()->subDay(),
            'excerpt' => 'Tout savoir.', 'featured_media_id' => $media->id, 'content' => $this->heading('Corps de la rentrée')]);
        $post->terms()->attach([$sport->id, $tag->id]);
        $en = Post::create(['title' => 'Back to school', 'slug' => 'back-to-school', 'locale' => 'en', 'translation_group' => $post->translation_group,
            'status' => 'published', 'published_at' => now()->subDay(), 'content' => $this->heading('Body')]);
        $en->terms()->attach($news);
        Post::create(['title' => 'Brouillon', 'slug' => 'brouillon', 'content' => $this->heading('Pas encore')]);

        Layout::create(['name' => 'Article sport', 'type' => 'single', 'status' => 'published', 'priority' => 4, 'content' => $this->heading('GABARIT SPORT'),
            'conditions' => [['mode' => 'include', 'target' => 'in_category', 'id' => $sport->id], ['mode' => 'exclude', 'target' => 'post', 'id' => 9999]]]);
        Layout::create(['name' => 'En-tête', 'type' => 'header', 'status' => 'published', 'content' => $this->heading('EN-TÊTE'),
            'conditions' => [['mode' => 'include', 'target' => 'all'], ['mode' => 'exclude', 'target' => 'page', 'id' => $page->id]]]);

        return compact('post', 'sport');
    }

    public function test_round_trip_into_an_empty_site(): void
    {
        $this->buildBlog();
        $zip = app(SiteExporter::class)->export(storage_path('app/test-export.zip'));

        $archive = new ZipArchive;
        $archive->open($zip);
        foreach (['posts.json', 'terms.json', 'layouts.json'] as $file) {
            $this->assertNotFalse($archive->statName($file), $file);
        }
        $archive->close();

        // Site vide, puis import.
        Layout::query()->forceDelete();
        Post::query()->forceDelete();
        Term::query()->delete();
        Page::query()->forceDelete();
        Media::query()->delete();
        Setting::write(['blog' => ['locales' => [], 'per_page' => 9]]);

        $report = app(SiteImporter::class)->import($zip, $this->admin, replace: true);
        $this->assertSame([3, 4, 2], [$report['posts'], $report['terms'], $report['layouts']]);

        $post = Post::where('slug', 'la-rentree')->sole();
        $sport = Term::where('slug', 'sport')->sole();
        $this->assertEqualsCanonicalizing(['Sport', 'Rentrée'], $post->terms->pluck('name')->all());
        $this->assertSame(Term::where('slug', 'vie-scolaire')->sole()->id, $sport->parent_id);
        $this->assertSame($post->translation_group, Post::where('slug', 'back-to-school')->sole()->translation_group, 'Traductions toujours liées.');
        $this->assertSame(Media::sole()->id, $post->featured_media_id, 'Image à la une remappée.');
        $this->assertSame('draft', Post::where('slug', 'brouillon')->sole()->status);

        // Conditions remappées ; celle qui visait un article absent disparaît.
        $layout = Layout::where('name', 'Article sport')->sole();
        $this->assertSame([['mode' => 'include', 'target' => 'in_category', 'id' => $sport->id]], $layout->conditions);
        $this->assertSame(Page::where('slug', 'accueil')->sole()->id, Layout::where('name', 'En-tête')->sole()->conditions[1]['id']);

        // Réglages du blog repris, et le site fonctionne.
        $this->assertSame('actualites', Setting::read('blog')['locales']['fr']['base']);
        $this->get('/actualites/la-rentree')->assertOk()->assertSee('GABARIT SPORT')->assertSee('EN-TÊTE');
        $this->get('/')->assertOk()->assertDontSee('EN-TÊTE');
    }

    public function test_merging_keeps_existing_posts_and_reuses_categories(): void
    {
        $this->buildBlog();
        $zip = app(SiteExporter::class)->export(storage_path('app/test-export.zip'));

        $report = app(SiteImporter::class)->import($zip, $this->admin, replace: false, withSettings: false);

        $this->assertSame(0, $report['terms'], 'Catégories de même adresse réutilisées.');
        $this->assertSame(2, Post::where('title', 'La rentrée')->count());
        $this->assertTrue(Post::where('slug', 'la-rentree-2')->exists(), 'Adresse libre pour le double.');
        $this->assertSame(4, Layout::count());
    }

    public function test_importing_one_language_only(): void
    {
        $this->buildBlog();
        $zip = app(SiteExporter::class)->export(storage_path('app/test-export.zip'));
        Post::query()->forceDelete();
        Term::query()->delete();

        $report = app(SiteImporter::class)->import($zip, $this->admin, replace: true, locales: ['en']);

        $this->assertSame(1, $report['posts']);
        $this->assertSame(['back-to-school'], Post::pluck('slug')->all());
        $this->assertSame(['News'], Term::pluck('name')->all());
    }
}
