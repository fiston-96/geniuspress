<?php

namespace Tests\Feature;

use App\Models\Layout;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use App\Models\User;
use App\Site\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Widgets dynamiques du thème : titre, contenu, informations, grille d'articles, menu, fil d'Ariane… */
class ThemeWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private int $n = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $admin = User::create(['name' => 'Kofi M.', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        Setting::write(['site_name' => 'Église Lumière', 'locales' => ['fr', 'en']]);
        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => 'published',
            'content' => $this->tree([['posts', ['source' => 'latest', 'count' => 2, 'columns' => 2, 'show_more' => true]]])]);
        $this->admin = $admin;
    }

    private User $admin;

    /** @param array<array{0: string, 1: array}> $widgets */
    private function tree(array $widgets): array
    {
        return ['version' => 2, 'elements' => array_map(fn ($w) => ['id' => 'w'.str_pad((string) ++$this->n, 9, '0', STR_PAD_LEFT), 'type' => $w[0], 'settings' => $w[1]], $widgets)];
    }

    private function article(string $title, array $extra = []): Post
    {
        return Post::create($extra + ['title' => $title, 'slug' => Post::normalizeSlug($title), 'status' => 'published', 'published_at' => now()->subHours(++$this->n),
            'author_id' => $this->admin->id, 'content' => $this->tree([['text', ['html' => '<p>Corps de « '.$title.' »</p>', 'color' => '#123456']]])]);
    }

    public function test_single_layout_with_post_widgets(): void
    {
        $culte = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Cultes']);
        $tag = Term::create(['taxonomy' => Term::TAG, 'name' => 'Pâques']);
        $this->article('Ancien message', ['published_at' => now()->subDays(3)]);
        $post = $this->article('Culte de Pâques', ['excerpt' => 'Un culte en famille.', 'published_at' => now()->subDays(2)]);
        $post->terms()->attach([$culte->id, $tag->id]);
        $this->article('Message suivant', ['published_at' => now()->subDay()]);

        Layout::create(['name' => 'Article', 'type' => 'single', 'status' => 'published', 'content' => $this->tree([
            ['breadcrumbs', []],
            ['post-title', ['tag' => 'h1', 'color' => '#ff0000']],
            ['post-meta', ['show_tags' => true]],
            ['post-excerpt', ['length' => 80]],
            ['post-content', ['font_size' => 19]],
            ['post-nav', []],
        ])]);

        $html = $this->get('/blog/culte-de-paques')->assertOk()->getContent();
        $this->assertStringContainsString('<h1>Culte de Pâques</h1>', $html);
        $this->assertStringContainsString('Kofi M.', $html);
        $this->assertStringContainsString('href="'.url('/blog/categorie/cultes').'">Cultes</a>', $html);
        $this->assertStringContainsString('#Pâques', $html);
        $this->assertStringContainsString('Un culte en famille.', $html);
        $this->assertStringContainsString('Corps de « Culte de Pâques »', $html, 'Contenu de l’article inclus dans le gabarit.');
        $this->assertMatchesRegularExpression('/#gp-w\d+\{color:#123456\}/', $html, 'Le CSS du contenu de l’article est inclus.');
        $this->assertStringContainsString('rel="prev"', $html);
        $this->assertStringContainsString('Ancien message', $html);
        $this->assertStringContainsString('Message suivant', $html);
        $this->assertStringContainsString('aria-current="page">Culte de Pâques</span>', $html, 'Fil d’Ariane.');
    }

    public function test_post_widgets_render_nothing_outside_a_post_and_content_never_includes_itself(): void
    {
        $page = Page::create(['title' => 'À propos', 'slug' => 'a-propos', 'status' => 'published', 'content' => $this->tree([['post-title', []], ['post-content', []], ['post-meta', []]])]);
        $html = $this->get('/a-propos')->assertOk()->getContent();
        $this->assertStringNotContainsString('<h1>', $html);

        // Un article dont le contenu contient le widget « contenu » ne boucle pas.
        $this->article('Boucle', ['content' => $this->tree([['post-content', []], ['heading', ['text' => 'Titre interne']]])]);
        $this->get('/blog/boucle')->assertOk()->assertSee('Titre interne');
    }

    public function test_posts_grid_sources_and_archive_layout(): void
    {
        $sport = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Jeunesse', 'description' => 'Pour les jeunes.']);
        foreach (['Un', 'Deux', 'Trois'] as $title) {
            $this->article('Article '.$title);
        }
        $this->article('Camp des jeunes')->terms()->attach($sport);

        // Accueil : 2 derniers articles.
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertSame(2, substr_count($home, 'class="gp-posts__item"'));
        $this->assertMatchesRegularExpression('/\{grid-template-columns:repeat\(2, minmax\(0, 1fr\)\)\}/', $home);

        // Archive par gabarit : titre de l'archive et articles de la catégorie seulement.
        Layout::create(['name' => 'Archive', 'type' => 'archive', 'status' => 'published', 'content' => $this->tree([['archive-title', []], ['posts', ['source' => 'current']]])]);
        $archive = $this->get('/blog/categorie/jeunesse')->assertOk()->getContent();
        $this->assertStringContainsString('<p class="gp-atitle__prefix">Catégorie</p>', $archive);
        $this->assertStringContainsString('Pour les jeunes.', $archive);
        $this->assertStringContainsString('Camp des jeunes', $archive);
        $this->assertStringNotContainsString('Article Un', $archive);

        // Source « category » sur une page.
        Page::create(['title' => 'Jeunes', 'slug' => 'jeunes', 'status' => 'published', 'content' => $this->tree([['posts', ['source' => 'category', 'category' => $sport->id]]])]);
        $this->get('/jeunes')->assertOk()->assertSee('Camp des jeunes')->assertDontSee('Article Deux');
    }

    public function test_header_layout_with_logo_menu_and_search(): void
    {
        Navigation::save([
            'header' => ['enabled' => '1'], 'footer' => ['enabled' => '1'],
            'locales' => ['fr' => ['menu' => [['label' => 'Cultes', 'url' => '/cultes']], 'cta_label' => 'Faire un don', 'cta_url' => '/dons']],
        ], ['fr', 'en']);
        Layout::create(['name' => 'En-tête', 'type' => 'header', 'status' => 'published', 'content' => $this->tree([
            ['site-logo', ['height' => 48]],
            ['nav-menu', ['collapse' => 'tablet', 'color' => '#222222']],
            ['search-form', ['button_text' => 'Chercher']],
        ])]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('<span class="gp-logo__name">Église Lumière</span>', $html);
        $this->assertStringContainsString('<a href="/cultes">Cultes</a>', $html);
        $this->assertStringContainsString('gp-menu--collapse-tablet', $html);
        $this->assertStringContainsString('class="gp-btn gp-btn-sm gp-menu__cta" href="/dons">Faire un don</a>', $html);
        $this->assertStringContainsString('hreflang="en"', $html, 'Choix de la langue.');
        $this->assertStringContainsString('>Chercher</button>', $html);
        $this->assertStringNotContainsString('gp-site-header', $html);
    }
}
