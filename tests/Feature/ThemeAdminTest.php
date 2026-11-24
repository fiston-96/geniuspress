<?php

namespace Tests\Feature;

use App\Models\ContentRevision;
use App\Models\Layout;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Administration des articles, des catégories et étiquettes, et du constructeur de thème. */
class ThemeAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        Setting::write(['locales' => ['fr', 'en']]);
        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => 'published']);
    }

    private function content(string $text): array
    {
        return ['version' => 2, 'elements' => [['id' => 'w123456789', 'type' => 'heading', 'settings' => ['text' => $text, 'tag' => 'h2']]]];
    }

    public function test_writing_and_publishing_a_post(): void
    {
        $news = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Actualités']);
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.posts.store'), ['title' => 'Journée de l’arbre', 'locale' => 'fr', 'categories' => [$news->id], 'tags' => 'Nature, nature , Écologie']);
        $post = Post::sole();
        $response->assertRedirect(route('admin.posts.builder', $post));
        $this->assertSame('journee-de-larbre', $post->slug);
        $this->assertSame(Post::STATUS_DRAFT, $post->status);
        $this->assertEqualsCanonicalizing(['Actualités', 'Nature', 'Écologie'], $post->terms->pluck('name')->all(), 'Étiquettes créées, sans doublon.');

        $this->get(route('admin.posts.builder', $post))->assertOk()->assertSee('"document":"post"', false);

        $this->putJson(route('admin.posts.content', $post), ['content' => $this->content('Bonjour'), 'publish' => true])->assertOk()->assertJsonPath('status', 'published');
        $post->refresh();
        $this->assertTrue($post->isPublished());
        $this->assertNotNull($post->published_at);
        $this->get('/blog/journee-de-larbre')->assertOk()->assertSee('Bonjour');

        // Une modification d'un article en ligne reste en brouillon.
        $this->putJson(route('admin.posts.content', $post), ['content' => $this->content('Brouillon')])->assertOk()->assertJsonPath('has_draft', true);
        $this->get('/blog/journee-de-larbre')->assertSee('Bonjour')->assertDontSee('Brouillon');
        $this->assertSame(2, $post->revisions()->count());

        // Programmer : date future.
        $this->put(route('admin.posts.update', $post), ['title' => $post->title, 'slug' => $post->slug, 'locale' => 'fr', 'status' => 'published', 'published_at' => now()->addDays(3)->format('Y-m-d\TH:i')])
            ->assertSessionHas('status', 'Article programmé.');
        auth()->logout();
        $this->get('/blog/journee-de-larbre')->assertNotFound();
    }

    public function test_post_validation_preview_and_revisions(): void
    {
        $this->actingAs($this->admin);
        $other = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Sport', 'locale' => 'en']);
        $this->post(route('admin.posts.store'), ['title' => 'RSS', 'locale' => 'fr'])->assertSessionHasErrors('slug');
        $this->post(route('admin.posts.store'), ['title' => 'Mauvaise langue', 'locale' => 'fr', 'categories' => [$other->id]])->assertSessionHasErrors('categories.0');

        $post = Post::create(['title' => 'Aperçu', 'slug' => 'apercu', 'author_id' => $this->admin->id]);
        $this->post(route('admin.posts.preview', $post), ['content' => json_encode($this->content('Texte en cours'))])
            ->assertOk()->assertSee('Texte en cours')->assertSee('<meta name="robots" content="noindex">', false);

        $layout = Layout::create(['name' => 'X', 'type' => 'header']);
        $foreign = $layout->revisions()->create(['content' => $this->content('Autre'), 'note' => 'x']);
        $this->postJson(route('admin.posts.revisions.restore', [$post, $foreign]))->assertNotFound();
        $mine = $post->revisions()->create(['content' => $this->content('Ancienne'), 'note' => 'x']);
        $this->postJson(route('admin.posts.revisions.restore', [$post, $mine]))->assertOk()->assertJsonPath('content.elements.0.settings.text', 'Ancienne');
    }

    public function test_categories_and_tags(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('admin.terms.store'), ['taxonomy' => 'category', 'name' => 'Vie scolaire', 'locale' => 'fr', 'description' => 'Tout sur la vie à l’école.'])->assertRedirect();
        $term = Term::sole();
        $this->assertSame('vie-scolaire', $term->slug);
        $this->post(route('admin.terms.store'), ['taxonomy' => 'category', 'name' => 'Vie scolaire', 'locale' => 'fr'])->assertSessionHasErrors('slug');
        $this->post(route('admin.terms.store'), ['taxonomy' => 'category', 'name' => 'Vie scolaire', 'locale' => 'en'])->assertSessionHasNoErrors();

        $this->get(route('admin.terms.index'))->assertOk()->assertSee('Vie scolaire');
        $this->put(route('admin.terms.update', $term), ['name' => 'Vie de l’école', 'slug' => 'vie-ecole', 'locale' => 'fr'])->assertRedirect();
        $this->assertSame('vie-ecole', $term->fresh()->slug);

        $post = Post::create(['title' => 'A', 'slug' => 'a']);
        $post->terms()->attach($term);
        $this->delete(route('admin.terms.destroy', $term))->assertRedirect();
        $this->assertTrue($post->fresh()->exists && $post->terms()->count() === 0, 'L’article est conservé.');
    }

    public function test_theme_layouts_conditions_builder_and_preview(): void
    {
        $this->actingAs($this->admin);
        $sport = Term::create(['taxonomy' => Term::CATEGORY, 'name' => 'Sport']);

        $this->get(route('admin.layouts.index'))->assertOk()->assertSee('Pied de page');
        $this->post(route('admin.layouts.store'), ['type' => 'header', 'name' => 'En-tête principal'])->assertRedirect();
        $layout = Layout::sole();
        $this->assertSame([['mode' => 'include', 'target' => 'all']], $layout->conditions);
        $this->get(route('admin.layouts.builder', $layout))->assertOk()->assertSee('"document":"layout"', false);
        $this->get(route('admin.layouts.edit', $layout))->assertOk()->assertSee('value="in_category:'.$sport->id.'"', false);

        $this->put(route('admin.layouts.update', $layout), ['name' => 'En-tête', 'status' => 'published', 'priority' => 3, 'locale' => '', 'rules' => [
            ['mode' => 'include', 'what' => 'all'],
            ['mode' => 'exclude', 'what' => 'category:'.$sport->id],
            ['mode' => 'include', 'what' => ''],
            ['mode' => 'include', 'what' => 'nimporte'],
        ]])->assertSessionHasNoErrors();
        $layout->refresh();
        $this->assertSame([['mode' => 'include', 'target' => 'all'], ['mode' => 'exclude', 'target' => 'category', 'id' => $sport->id]], $layout->conditions);
        $this->assertNull($layout->locale);

        $this->putJson(route('admin.layouts.content', $layout), ['content' => $this->content('MON EN-TÊTE'), 'publish' => true])->assertOk();
        $this->get('/')->assertSee('MON EN-TÊTE');
        $this->get('/blog/categorie/sport')->assertOk()->assertDontSee('MON EN-TÊTE');

        // Aperçu d'un gabarit d'article inactif, sur un site sans article (article d'exemple).
        $single = Layout::create(['name' => 'Article', 'type' => 'single']);
        $this->post(route('admin.layouts.preview', $single), ['content' => json_encode($this->content('APERÇU ARTICLE'))])
            ->assertOk()->assertSee('APERÇU ARTICLE');
        foreach (['archive', 'search', 'not_found', 'footer'] as $type) {
            $l = Layout::create(['name' => $type, 'type' => $type]);
            $this->post(route('admin.layouts.preview', $l), ['content' => json_encode($this->content('APERÇU '.$type))])->assertOk()->assertSee('APERÇU '.$type);
        }
        $this->assertSame(1, ContentRevision::query()->count());
    }

    public function test_editors_write_posts_but_do_not_change_the_theme(): void
    {
        $editor = User::create(['name' => 'Éditeur', 'email' => 'e@test.local', 'password' => 'secret-password-1', 'role' => 'editor']);
        $this->actingAs($editor);
        $this->get(route('admin.posts.index'))->assertOk();
        $this->get(route('admin.terms.index'))->assertOk();
        $this->get(route('admin.layouts.index'))->assertForbidden();
        $this->put(route('admin.posts.settings'), ['per_page' => 3])->assertForbidden();
    }

    public function test_blog_settings(): void
    {
        $this->actingAs($this->admin);
        $this->put(route('admin.posts.settings'), ['per_page' => 6, 'blog' => ['fr' => ['title' => 'Actualités', 'base' => 'Nos Actualités'], 'en' => ['title' => 'News', 'base' => 'news']]])
            ->assertSessionHas('status');
        $blog = Setting::read('blog');
        $this->assertSame(6, $blog['per_page']);
        $this->assertSame(['title' => 'Actualités', 'base' => 'nos-actualites'], $blog['locales']['fr']);
        $this->get('/nos-actualites')->assertOk()->assertSee('Actualités');
        $this->get('/en/news')->assertOk()->assertSee('News');

        $this->put(route('admin.posts.settings'), ['per_page' => 6, 'blog' => ['fr' => ['base' => 'admin']]])->assertSessionHasErrors('blog');
    }
}
