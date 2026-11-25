<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Référencement : robots.txt, plan du site, adresse canonique, partage, données structurées, réglages. */
class SeoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        Setting::write(['site_name' => 'Espoir', 'site_tagline' => 'Ensemble pour la dignité', 'locales' => ['fr', 'en'], 'logo_url' => '/logo.png']);
        $group = 'g-accueil';
        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => 'published', 'locale' => 'fr', 'translation_group' => $group, 'content' => ['version' => 2, 'elements' => [
            ['id' => 'c000000001', 'type' => 'container', 'settings' => ['background_image' => '/storage/fond.jpg'], 'children' => [['id' => 'w000000001', 'type' => 'heading', 'settings' => ['text' => 'Bonjour']]]],
        ]]]);
        Page::create(['title' => 'Home', 'slug' => 'home', 'is_home' => true, 'status' => 'published', 'locale' => 'en', 'translation_group' => $group, 'content' => ['version' => 2, 'elements' => []]]);
    }

    public function test_robots_txt_points_to_the_sitemap_and_follows_the_setting(): void
    {
        $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin')->assertSee('Sitemap: '.url('/sitemap.xml'));

        Setting::write(['search_engines' => false]);
        $robots = $this->get('/robots.txt')->getContent();
        $this->assertSame("User-agent: *\nDisallow: /\n", $robots);
        $this->assertStringContainsString('<meta name="robots" content="noindex">', $this->get('/')->getContent(), 'Site fermé aux moteurs.');
    }

    public function test_home_page_head(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="'.url('/').'">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="x-default" href="'.url('/').'">', $html);
        $this->assertStringContainsString('<meta name="description" content="Ensemble pour la dignité">', $html, 'Le slogan sert de description à l’accueil.');
        $this->assertStringContainsString('<meta property="og:image" content="'.url('/storage/fond.jpg').'">', $html, 'Première image de la page.');
        $this->assertStringContainsString('<meta property="og:locale" content="fr_FR">', $html);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        $this->assertStringNotContainsString('name="robots"', $html);

        preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);
        $types = array_map(fn ($j) => json_decode($j, true)['@type'], $m[1]);
        $this->assertSame(['WebSite', 'Organization'], $types);
        $this->assertSame(url('/').'?s={search_term_string}', json_decode($m[1][0], true)['potentialAction']['target']['urlTemplate']);
    }

    public function test_post_has_article_and_breadcrumb_data(): void
    {
        $category = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Projets']);
        $post = Post::create(['title' => 'Un puits pour le village', 'slug' => 'puits', 'status' => 'published', 'published_at' => now()->subDay(), 'author_id' => $this->admin->id,
            'excerpt' => 'Résumé', 'content' => ['version' => 2, 'elements' => []]]);
        $post->terms()->attach($category);

        $html = $this->get($post->url())->assertOk()->getContent();
        preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);
        $data = array_map(fn ($j) => json_decode($j, true), $m[1]);
        $this->assertSame(['BlogPosting', 'BreadcrumbList'], array_column($data, '@type'));
        $this->assertSame('Un puits pour le village', $data[0]['headline']);
        $this->assertSame('Admin', $data[0]['author']['name']);
        $this->assertSame(['Espoir', 'Blog', 'Projets', 'Un puits pour le village'], array_column($data[1]['itemListElement'], 'name'));
        $this->assertStringContainsString('<meta property="og:type" content="article">', $html);
    }

    public function test_sitemap_lists_language_versions(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('xmlns:xhtml="http://www.w3.org/1999/xhtml"', $xml);
        $this->assertStringContainsString('<xhtml:link rel="alternate" hreflang="en" href="'.url('/en').'"/>', $xml);
    }

    public function test_settings_and_shared_image_fallback(): void
    {
        $base = ['site_name' => 'Espoir', 'primary_color' => '#2563eb', 'secondary_color' => '#0f172a', 'accent_color' => '#f59e0b', 'font_family' => 'Inter', 'locales' => ['fr', 'en'], 'default_locale' => 'fr'];
        $this->actingAs($this->admin)->put(route('admin.settings.update'), $base + ['google_site_verification' => '<script>'])->assertSessionHasErrors('google_site_verification');
        $this->put(route('admin.settings.update'), $base + ['google_site_verification' => 'abc-123_XYZ', 'og_image' => '/storage/partage.jpg'])->assertSessionHasNoErrors();
        $this->assertTrue((bool) Setting::read('search_engines'), 'Sans le champ, l’indexation reste autorisée.');

        $html = $this->get('/en')->getContent();
        $this->assertStringContainsString('<meta name="google-site-verification" content="abc-123_XYZ">', $html);
        $this->assertStringContainsString('<meta property="og:image" content="'.url('/storage/partage.jpg').'">', $html, 'Page sans image : image par défaut.');
    }
}
