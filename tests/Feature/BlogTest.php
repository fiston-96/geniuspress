<?php

namespace Tests\Feature;

use App\Models\Layout;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use App\Models\User;
use App\Site\SiteContext;
use App\Site\ThemeLayouts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Articles, catégories et étiquettes, archives, recherche, flux RSS, plan du site, 404 et gabarits du thème. */
class BlogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::create(['name' => 'Amina K.', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        Setting::write(['site_name' => 'École Avenir', 'locales' => ['fr', 'en']]);
        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => 'published', 'content' => $this->text('Bienvenue')]);
    }

    private function text(string $text, string $type = 'text'): array
    {
        $settings = $type === 'heading' ? ['text' => $text, 'tag' => 'h2'] : ['html' => '<p>'.$text.'</p>'];

        return ['version' => 2, 'elements' => [['id' => 'w'.substr(md5($text.$type), 0, 9), 'type' => $type, 'settings' => $settings]]];
    }

    private function article(string $title, array $extra = []): Post
    {
        return Post::create($extra + [
            'title' => $title, 'slug' => Post::normalizeSlug($title), 'status' => 'published', 'published_at' => now()->subDay(),
            'content' => $this->text('Contenu de '.$title), 'author_id' => $this->admin->id,
        ]);
    }

    public function test_blog_lists_only_published_posts_and_shows_a_post(): void
    {
        $news = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Actualités']);
        $tag = Term::create(['taxonomy' => Term::TAG, 'name' => 'Rentrée']);
        $a = $this->article('Rentrée scolaire 2027', ['excerpt' => 'Les inscriptions sont ouvertes.']);
        $a->terms()->attach([$news->id, $tag->id]);
        $this->article('Brouillon secret', ['status' => 'draft']);
        $this->article('Article programmé', ['published_at' => now()->addWeek()]);

        $this->get('/blog')->assertOk()
            ->assertSee('Rentrée scolaire 2027')->assertSee('Les inscriptions sont ouvertes.')
            ->assertDontSee('Brouillon secret')->assertDontSee('Article programmé')
            ->assertSee('<link rel="alternate" type="application/rss+xml"', false);

        $this->get('/blog/rentree-scolaire-2027')->assertOk()
            ->assertSee('<h1 class="gp-single__title">Rentrée scolaire 2027</h1>', false)
            ->assertSee('Contenu de Rentrée scolaire 2027')
            ->assertSee('href="'.url('/blog/categorie/actualites').'"', false)
            ->assertSee('#Rentrée')
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<meta name="description" content="Les inscriptions sont ouvertes.">', false);

        $this->get('/blog/brouillon-secret')->assertNotFound();
        $this->actingAs($this->admin)->get('/blog/brouillon-secret')->assertOk()->assertSee('<meta name="robots" content="noindex">', false);
    }

    public function test_category_tag_and_search_archives(): void
    {
        $sport = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Sport', 'description' => 'La vie sportive de l’école.']);
        $this->article('Tournoi de football')->terms()->attach($sport);
        $this->article('Concert de fin d’année');

        $this->get('/blog/categorie/sport')->assertOk()->assertSee('La vie sportive de l’école.')
            ->assertSee('Tournoi de football')->assertDontSee('Concert de fin');
        $this->get('/blog/categorie/inconnue')->assertNotFound();

        $this->get('/?s=concert')->assertOk()->assertSee('Concert de fin d’année')->assertDontSee('Tournoi de football')
            ->assertSee('<meta name="robots" content="noindex">', false);
        $this->get('/?s=introuvable')->assertOk()->assertSee('Aucun article ne correspond à votre recherche.');
    }

    public function test_pagination_and_english_blog(): void
    {
        Setting::write(['blog' => ['locales' => ['fr' => ['base' => 'actualites', 'title' => 'Actualités']], 'per_page' => 2]]);
        foreach (['Un', 'Deux', 'Trois'] as $i => $title) {
            $this->article($title, ['published_at' => now()->subDays(10 - $i)]);
        }
        $this->article('Hello world', ['locale' => 'en']);

        $this->get('/actualites')->assertOk()->assertSee('<h1 class="gp-archive__title">Actualités</h1>', false)
            ->assertSee('Trois')->assertSee('Deux')->assertDontSee('>Un<', false)->assertSee('rel="next"', false);
        $this->get('/actualites?page=2')->assertOk()->assertSee('Un');
        $this->get('/actualites/trois')->assertOk();
        $this->get('/blog')->assertNotFound();

        $this->get('/en/blog')->assertOk()->assertSee('Hello world')->assertDontSee('Trois');
        $this->get('/en/blog/hello-world')->assertOk()->assertSee('<html lang="en"', false);
    }

    public function test_a_page_with_the_blog_address_replaces_the_default_archive(): void
    {
        Page::create(['title' => 'Notre blog', 'slug' => 'blog', 'status' => 'published', 'content' => $this->text('Page blog personnalisée')]);
        $this->article('Premier article');

        $this->get('/blog')->assertOk()->assertSee('Page blog personnalisée');
        $this->get('/blog/premier-article')->assertOk();
    }

    public function test_feed_sitemap_and_not_found(): void
    {
        $this->article('Journée portes ouvertes');
        $this->article('Brouillon', ['status' => 'draft']);
        Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Vide']);

        $rss = $this->get('/blog/rss')->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')->getContent();
        $this->assertNotFalse(simplexml_load_string($rss));
        $this->assertStringContainsString('<title>Journée portes ouvertes</title>', $rss);
        $this->assertStringNotContainsString('Brouillon', $rss);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertNotFalse(simplexml_load_string($xml));
        $this->assertStringContainsString('<loc>'.url('/blog/journee-portes-ouvertes').'</loc>', $xml);
        $this->assertStringContainsString('<loc>'.url('/').'</loc>', $xml);
        $this->assertStringNotContainsString('categorie/vide', $xml, 'Pas d’archive vide dans le plan du site.');

        $this->get('/nexiste-pas')->assertNotFound()->assertSee('Page introuvable')->assertSee('name="s"', false)
            ->assertSee('<meta name="robots" content="noindex">', false);
    }

    public function test_theme_layouts_replace_header_single_archive_and_404(): void
    {
        $sport = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Sport']);
        $post = $this->article('Match amical');
        $post->terms()->attach($sport);

        Layout::create(['name' => 'En-tête', 'type' => 'header', 'status' => 'published', 'content' => $this->text('EN-TÊTE DU THÈME', 'heading')]);
        Layout::create(['name' => 'Brouillon', 'type' => 'footer', 'status' => 'draft', 'content' => $this->text('PIED INACTIF')]);
        Layout::create(['name' => 'Article', 'type' => 'single', 'status' => 'published', 'content' => $this->text('GABARIT ARTICLE')]);
        Layout::create(['name' => 'Article sport', 'type' => 'single', 'status' => 'published', 'content' => $this->text('GABARIT SPORT'),
            'conditions' => [['mode' => 'include', 'target' => 'in_category', 'id' => $sport->id]]]);
        Layout::create(['name' => '404', 'type' => 'not_found', 'status' => 'published', 'content' => $this->text('PERDU ?')]);

        $home = $this->get('/')->assertOk()->assertSee('EN-TÊTE DU THÈME')->assertDontSee('PIED INACTIF')->getContent();
        $this->assertStringNotContainsString('gp-site-header', $home, 'L’en-tête par défaut est remplacé.');
        $this->assertStringContainsString('gp-site-footer', $home, 'Pas de pied de page actif : celui par défaut reste.');

        // La règle la plus précise l'emporte (catégorie Sport) ; sinon le gabarit général.
        $this->get('/blog/match-amical')->assertOk()->assertSee('GABARIT SPORT')->assertDontSee('GABARIT ARTICLE');
        $this->article('Autre article');
        $this->get('/blog/autre-article')->assertOk()->assertSee('GABARIT ARTICLE');

        // Pas de gabarit d'archive : affichage par défaut.
        $this->get('/blog')->assertOk()->assertSee('gp-post-grid', false);
        $this->get('/inconnue')->assertNotFound()->assertSee('PERDU ?');
    }

    public function test_conditions_include_exclude_priority_and_language(): void
    {
        $resolver = new ThemeLayouts;
        $page = Page::where('is_home', true)->first();
        $home = SiteContext::forPage($page);
        $other = SiteContext::forPage(Page::create(['title' => 'Contact', 'slug' => 'contact', 'status' => 'published']));

        $all = Layout::create(['name' => 'Tout', 'type' => 'header', 'status' => 'published']);
        $notHome = Layout::create(['name' => 'Sauf accueil', 'type' => 'header', 'status' => 'published', 'priority' => 5,
            'conditions' => [['mode' => 'include', 'target' => 'all'], ['mode' => 'exclude', 'target' => 'front']]]);
        $en = Layout::create(['name' => 'Anglais', 'type' => 'header', 'status' => 'published', 'priority' => 9, 'locale' => 'en']);

        $this->assertSame($all->id, $resolver->find('header', $home)->id, 'Exclusion de la page d’accueil.');
        $this->assertSame($notHome->id, (new ThemeLayouts)->find('header', $other)->id, 'Priorité la plus haute.');
        $this->assertNotSame($en->id, (new ThemeLayouts)->find('header', $other)->id, 'Gabarit d’une autre langue ignoré.');

        $this->assertSame([['mode' => 'include', 'target' => 'page', 'id' => 3], ['mode' => 'exclude', 'target' => 'all']],
            ThemeLayouts::sanitizeConditions([['mode' => 'include', 'target' => 'page', 'id' => '3'], ['mode' => 'exclude', 'target' => 'all', 'id' => 9], ['target' => 'hack'], ['target' => 'in_category']]));
    }

    public function test_post_summary_and_reading_time_come_from_the_content(): void
    {
        $post = $this->article('Long article', ['content' => $this->text(str_repeat('mot ', 450))]);

        $this->assertSame(3, $post->readingMinutes());
        $this->assertStringStartsWith('mot mot', $post->summary());
        $this->assertSame(['previous' => null, 'next' => null], \App\Site\PostNavigation::for($post));
    }
}
