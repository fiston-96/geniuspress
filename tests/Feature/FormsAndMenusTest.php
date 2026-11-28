<?php

namespace Tests\Feature;

use App\Builder\DynamicTags;
use App\Builder\Renderer;
use App\Models\FormSubmission;
use App\Models\Layout;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Lot 3 du builder : formulaire avancé, balises dynamiques, méga-menu, formulaires dans les popups. */
class FormsAndMenusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@ong.test', 'password' => 'secret-password-1', 'role' => 'admin']);
        Setting::write(['locales' => ['fr'], 'site_name' => 'Solidarité <Nord>', 'site_tagline' => 'Agir ensemble']);
        Mail::fake();
    }

    private function fields(): array
    {
        return [
            ['type' => 'text', 'label' => 'Nom complet', 'name' => 'nom-complet', 'required' => true, 'width' => '50'],
            ['type' => 'email', 'label' => 'Adresse e-mail', 'required' => true, 'width' => '50'],
            ['type' => 'select', 'label' => 'Service', 'options' => "Site vitrine\nBoutique", 'required' => true],
            ['type' => 'checkboxes', 'label' => 'Langues', 'options' => "Français\nAnglais\nSwahili"],
            ['type' => 'textarea', 'label' => 'Projet'],
            ['type' => 'acceptance', 'label' => 'J’accepte', 'required' => true],
            ['type' => 'hidden', 'label' => 'Origine', 'value' => '{{page.title}}'],
        ];
    }

    private function page(array $widgets, string $slug = 'devis'): Page
    {
        return Page::create(['title' => 'Devis', 'slug' => $slug, 'status' => 'published', 'content' => ['version' => 2, 'elements' => [
            ['id' => 'box0000001', 'type' => 'container', 'settings' => [], 'children' => $widgets],
        ]]]);
    }

    private function send(string $owner, array $data, string $widget = 'form000001')
    {
        return $this->from('/devis')->post('/formulaire', $data + ['owner' => $owner, 'widget' => $widget, '_ts' => Crypt::encryptString((string) (time() - 10))]);
    }

    public function test_dynamic_tags_are_replaced_and_escaped(): void
    {
        $page = $this->page([['id' => 'head000001', 'type' => 'heading', 'settings' => ['text' => '{{site.name}} · {{ page.title }} · {{date.year}} · {{inconnu.tag}} · {{post.title}}']]], 'accueil');

        $html = $this->get('/accueil')->assertOk()->getContent();
        $this->assertStringContainsString('Solidarité &lt;Nord&gt; · Devis · '.now()->format('Y').' · {{inconnu.tag}} · </h2>', $html);
        $this->assertSame('Agir ensemble', DynamicTags::values(null)['site.tagline']);
        $this->assertSame('sans balise', DynamicTags::replace('sans balise', null));
    }

    public function test_custom_form_renders_validates_and_stores_answers(): void
    {
        $this->page([['id' => 'form000001', 'type' => 'form', 'settings' => ['form_name' => 'Devis', 'fields' => $this->fields(), 'email_to' => 'devis@ong.test']]]);

        $html = $this->get('/devis')->assertOk()->getContent();
        $this->assertStringContainsString('<div class="gp-form__grid"><p class="gp-form__field gp-form__field--50"><label for="gp-form000001-nom-complet">Nom complet <span aria-hidden="true">*</span></label>', $html);
        $this->assertStringContainsString('name="adresse-e-mail"', $html);
        $this->assertStringContainsString('<fieldset class="gp-form__field gp-form__field--100"><legend>Langues</legend>', $html);
        $this->assertStringContainsString('name="langues[]" value="Swahili"', $html);
        $this->assertStringContainsString('<input type="hidden" name="origine" value="Devis">', $html, 'Balise dynamique dans un champ caché.');
        $this->assertMatchesRegularExpression('/name="owner" value="page:\d+"/', $html);

        // Choix inconnu, consentement manquant, e-mail invalide : refusés.
        $page = Page::where('slug', 'devis')->first();
        $this->send('page:'.$page->id, ['nom-complet' => 'Awa', 'adresse-e-mail' => 'pas-un-email', 'service' => 'Piratage', 'langues' => ['Klingon']])
            ->assertSessionHasErrors(['adresse-e-mail', 'service', 'langues.0', 'jaccepte'], null, 'gp_form_form000001');
        $this->assertSame(0, FormSubmission::count());

        $this->send('page:'.$page->id, ['nom-complet' => 'Awa Diallo', 'adresse-e-mail' => 'awa@exemple.org', 'service' => 'Boutique', 'langues' => ['Français', 'Swahili'], 'projet' => "Une boutique\nen deux langues", 'jaccepte' => '1', 'origine' => 'Devis'])
            ->assertRedirect()->assertSessionHas('gp_form_sent', 'form000001');
        $m = FormSubmission::sole();
        $this->assertSame('Devis', $m->form_name);
        $this->assertSame($page->id, $m->page_id);
        $this->assertSame('Awa Diallo', $m->senderName());
        $this->assertSame('awa@exemple.org', $m->senderEmail());
        $this->assertSame(['Langues', 'Français, Swahili'], $m->fields()[3]);
        $this->assertSame(['J’accepte', 'Oui'], $m->fields()[5]);

        $this->actingAs($this->admin)->get(route('admin.messages.show', $m))->assertOk()->assertSee('Français, Swahili')->assertSee('Une boutique');
        $this->get(route('admin.messages.index'))->assertOk()->assertSee('Awa Diallo')->assertSee('Devis');
    }

    public function test_custom_form_can_redirect_after_sending(): void
    {
        $page = $this->page([['id' => 'form000001', 'type' => 'form', 'settings' => ['fields' => [['type' => 'email', 'label' => 'E-mail', 'required' => true]], 'redirect' => '/merci']]]);

        $this->send('page:'.$page->id, ['e-mail' => 'a@b.org'])->assertRedirect('/merci');
    }

    public function test_forms_and_newsletter_work_inside_a_popup(): void
    {
        $this->page([], 'accueil');
        $popup = Layout::create(['name' => 'Offre', 'type' => 'popup', 'status' => Layout::STATUS_PUBLISHED, 'content' => ['version' => 2, 'elements' => [
            ['id' => 'box0000002', 'type' => 'container', 'settings' => [], 'children' => [
                ['id' => 'form000002', 'type' => 'form', 'settings' => ['fields' => [['type' => 'email', 'label' => 'E-mail', 'required' => true]]]],
                ['id' => 'news000002', 'type' => 'newsletter', 'settings' => []],
            ]],
        ]]]);

        $html = $this->get('/accueil')->assertOk()->getContent();
        $this->assertSame(2, substr_count($html, 'name="owner" value="layout:'.$popup->id.'"'));

        $this->send('layout:'.$popup->id, ['e-mail' => 'visiteur@exemple.org'], 'form000002')->assertSessionHas('gp_form_sent', 'form000002');
        $m = FormSubmission::sole();
        $this->assertNull($m->page_id);
        $this->assertSame($popup->id, $m->layout_id);
        $this->assertSame('Offre', $m->source());

        // Un gabarit inactif ne reçoit rien.
        $popup->update(['status' => Layout::STATUS_DRAFT]);
        $this->send('layout:'.$popup->id, ['e-mail' => 'x@exemple.org'], 'form000002')->assertNotFound();
    }

    public function test_mega_menu_renders_designed_panels_and_link_lists(): void
    {
        $panel = Layout::create(['name' => 'Services', 'type' => 'mega', 'content' => ['version' => 2, 'elements' => [
            ['id' => 'mbox000001', 'type' => 'container', 'settings' => ['background_color' => '#123456'], 'children' => [
                ['id' => 'mhead00001', 'type' => 'heading', 'settings' => ['text' => 'NOS SERVICES']],
                // Un panneau qui contient lui-même un méga-menu ne boucle pas.
                ['id' => 'mmenu00002', 'type' => 'mega-menu', 'settings' => ['items' => [['label' => 'Boucle', 'mega' => 0]]]],
            ]],
        ]]]);
        $out = app(Renderer::class)->render(['version' => 2, 'elements' => [['id' => 'hbox000001', 'type' => 'container', 'settings' => [], 'children' => [
            ['id' => 'mmenu00001', 'type' => 'mega-menu', 'settings' => ['open_on' => 'click', 'panel_width' => 'full', 'panel_bg' => '#fafafa', 'items' => [
                ['label' => 'Accueil', 'url' => '/'],
                ['label' => 'Services', 'mega' => $panel->id],
                ['label' => 'Ressources', 'url' => '/ressources', 'links' => "Guides | /guides\nmauvais lien | javascript:alert(1)\nFAQ | /faq"],
            ]]],
        ]]]]);

        $html = $out['html'];
        $this->assertStringContainsString('<nav class="gp-mega gp-mega--collapse-mobile gp-mega--click gp-mega--w-full"', $html);
        $this->assertStringContainsString('<li class="gp-mega__item"><a class="gp-mega__top" href="/" aria-current="page">Accueil</a></li>', $html);
        $this->assertStringContainsString('<button type="button" class="gp-mega__top" data-gp-mega-open aria-expanded="false" aria-controls="gp-mmenu00001-p1">Services</button>', $html);
        $this->assertStringContainsString('NOS SERVICES', $html);
        $this->assertStringContainsString('<li class="gp-mega__item"><span class="gp-mega__top">Boucle</span></li>', $html, 'Entrée sans lien ni panneau.');
        $this->assertStringContainsString('aria-label="Afficher : Ressources"', $html);
        $this->assertStringContainsString('<ul class="gp-mega__links"><li><a href="/guides">Guides</a></li><li><a href="/faq">FAQ</a></li></ul>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        // CSS du panneau intégré à celui de la page.
        $this->assertStringContainsString('#gp-mbox000001{background-color:#123456}', $out['css']);
        $this->assertStringContainsString('#gp-mmenu00001 .gp-mega__panel{background-color:#fafafa}', $out['css']);
    }

    public function test_mega_layouts_are_offered_to_the_builder_and_previewed_alone(): void
    {
        $panel = Layout::create(['name' => 'Panneau services', 'type' => 'mega']);
        $page = $this->page([], 'accueil');
        $page->update(['is_home' => true]);

        $this->actingAs($this->admin)->get(route('admin.pages.builder', $page))->assertOk()->assertSee('"megaLayouts":[{"id":'.$panel->id.',"name":"Panneau services"}]', false);
        $this->get(route('admin.layouts.edit', $panel))->assertOk()->assertSee('il n’a pas de conditions d’affichage')->assertDontSee('Afficher sur');
        $this->post(route('admin.layouts.preview', $panel), ['content' => json_encode(['version' => 2, 'elements' => [['id' => 'wapercu002', 'type' => 'heading', 'settings' => ['text' => 'PANNEAU SEUL']]]])])
            ->assertOk()->assertSee('PANNEAU SEUL');
    }
}
