<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Administration : menu latéral, recherche globale, tableau de bord « Bien démarrer », liste des pages. */
class AdminWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::create(['name' => 'Awa Diallo', 'email' => 'admin@ong.test', 'password' => 'secret-password-1', 'role' => 'admin']);
        Setting::write(['locales' => ['fr', 'en'], 'site_name' => 'Solidarité Nord']);
    }

    public function test_dashboard_shows_the_getting_started_checklist_and_menu(): void
    {
        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => 'published', 'locale' => 'fr']);
        Page::create(['title' => 'Projet en cours', 'slug' => 'projet', 'status' => 'draft', 'locale' => 'fr']);

        $html = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('Bien démarrer', $html);
        $this->assertStringContainsString('1 étapes sur 6', $html, 'Seule la page d’accueil est publiée.');
        $this->assertStringContainsString('Projet en cours', $html);
        foreach (['Contenu', 'Design', 'Marketing', 'Système', 'Solidarité Nord', 'AD'] as $text) {
            $this->assertStringContainsString($text, $html);
        }

        // Un éditeur ne voit ni les réglages ni la liste « Bien démarrer ».
        $editor = User::create(['name' => 'Éditeur', 'email' => 'e@ong.test', 'password' => 'secret-password-1', 'role' => 'editor']);
        $this->actingAs($editor)->get(route('admin.dashboard'))->assertOk()->assertDontSee('Bien démarrer')->assertDontSee(route('admin.settings.edit'));
    }

    public function test_global_search_finds_pages_and_posts(): void
    {
        Page::create(['title' => 'Nos programmes santé', 'slug' => 'sante', 'status' => 'published', 'locale' => 'fr']);
        Post::create(['title' => 'Campagne santé 2026', 'slug' => 'campagne', 'status' => 'draft', 'locale' => 'fr', 'author_id' => $this->admin->id]);
        Page::create(['title' => 'Contact', 'slug' => 'contact', 'status' => 'published', 'locale' => 'fr']);

        $this->actingAs($this->admin)->get(route('admin.search', ['q' => 'santé']))->assertOk()
            ->assertSee('Nos programmes santé')->assertSee('Campagne santé 2026')->assertDontSee('>Contact<', false)->assertSee('2 résultats');
        $this->get(route('admin.search', ['q' => 'x']))->assertOk()->assertSee('Tapez au moins deux caractères.');
        $this->get(route('admin.search', ['q' => '%']))->assertOk();
    }

    public function test_pages_list_has_status_tabs_and_language_filter(): void
    {
        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => 'published', 'locale' => 'fr']);
        Page::create(['title' => 'Home', 'slug' => 'home', 'is_home' => true, 'status' => 'published', 'locale' => 'en']);
        Page::create(['title' => 'Brouillon FR', 'slug' => 'brouillon', 'status' => 'draft', 'locale' => 'fr']);

        $this->actingAs($this->admin)->get(route('admin.pages.index'))->assertOk()->assertSee('Publiées')->assertSee('Brouillons');
        $this->get(route('admin.pages.index', ['locale' => 'en']))->assertOk()->assertSee('Home')->assertDontSee('Brouillon FR');
        $this->get(route('admin.pages.index', ['status' => 'draft']))->assertOk()->assertSee('Brouillon FR')->assertDontSee('>Accueil<', false);
    }
}
