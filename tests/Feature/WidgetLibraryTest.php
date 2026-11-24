<?php

namespace Tests\Feature;

use App\Ai\LlmGateway;
use App\Builder\ContentSanitizer;
use App\Builder\Icons;
use App\Builder\Renderer;
use App\Models\FormSubmission;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\Fakes\FakeLlm;
use Tests\TestCase;

/** Widgets des modèles : icônes, boîtes, listes, FAQ, galerie, tarifs, formulaire de contact… */
class WidgetLibraryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // Un compte existe : le site est installé (sinon tout renvoie vers /install).
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@ong.test', 'password' => 'secret-password-1', 'role' => 'admin']);
    }

    private function render(array $widgets, ?int $pageId = null): array
    {
        return app(Renderer::class)->render(['elements' => [['id' => 'box0000001', 'type' => 'container', 'settings' => [], 'children' => $widgets]]], $pageId);
    }

    private function contactPage(array $settings = ['show_phone' => true]): Page
    {
        return Page::create(['title' => 'Contact', 'slug' => 'contact', 'status' => 'published', 'content' => ['elements' => [
            ['id' => 'box0000001', 'type' => 'container', 'settings' => [], 'children' => [
                ['id' => 'form000001', 'type' => 'contact-form', 'settings' => $settings],
            ]],
        ]]]);
    }

    private function send(Page $page, array $data, int $age = 10)
    {
        return $this->from('/contact')->post('/formulaire', $data + [
            'page' => $page->id, 'widget' => 'form000001', '_ts' => Crypt::encryptString((string) (time() - $age)),
        ]);
    }

    public function test_icons_are_validated_and_rendered_inline(): void
    {
        $this->assertTrue(Icons::exists('lucide:heart'));
        $this->assertTrue(Icons::exists('brand:whatsapp'));
        $this->assertFalse(Icons::exists('lucide:../../etc'));
        $this->assertFalse(Icons::exists('brand:inconnu'));

        $clean = app(ContentSanitizer::class)->sanitize(['elements' => [['type' => 'icon', 'settings' => ['icon' => 'lucide:<script>', 'shape' => 'circle']]]]);
        $this->assertArrayNotHasKey('icon', $clean['elements'][0]['settings']);

        $out = $this->render([['id' => 'icon000001', 'type' => 'icon', 'settings' => ['icon' => 'lucide:heart', 'link' => '/dons', 'label' => 'Faire un don', 'shape' => 'circle']]]);
        $this->assertStringContainsString('<a href="/dons" aria-label="Faire un don"><span class="gp-icon-wrap gp-icon--circle"><svg class="gp-ico" viewBox="0 0 24 24"', $out['html']);
        $this->assertStringContainsString('aria-hidden="true"', $out['html']);
        $this->assertStringContainsString('#gp-icon000001 .gp-icon-wrap{color:#ffffff;background:var(--gp-primary);padding:16px}', $out['css']);
    }

    public function test_list_settings_are_validated_item_by_item(): void
    {
        $clean = app(ContentSanitizer::class)->sanitize(['elements' => [['type' => 'accordion', 'settings' => ['items' => [
            ['title' => 'Question <b>1</b> ?', 'html' => '<p>Réponse<script>alert(1)</script></p>', 'evil' => 'x'],
            ['title' => '', 'html' => ''],
            'pas un tableau',
            ['title' => 'Deux ?'],
        ]]], ['type' => 'gallery', 'settings' => ['images' => array_fill(0, 80, ['url' => 'javascript:alert(1)', 'alt' => 'x'])]]]]);

        $items = $clean['elements'][0]['settings']['items'];
        $this->assertSame([['title' => 'Question <b>1</b> ?', 'html' => '<p>Réponse</p>'], ['title' => 'Deux ?']], $items);
        $images = $clean['elements'][1]['settings']['images'];
        $this->assertCount(60, $images, 'Nombre d’éléments limité.');
        $this->assertSame(['alt' => 'x'], $images[0], 'Adresse dangereuse retirée.');

        // Le texte reste échappé au rendu.
        $out = $this->render($clean['elements']);
        $this->assertStringContainsString('<summary>Question &lt;b&gt;1&lt;/b&gt; ?</summary>', $out['html']);
    }

    public function test_every_widget_renders_its_content(): void
    {
        $out = $this->render([
            ['id' => 'ibox000001', 'type' => 'icon-box', 'settings' => ['icon' => 'lucide:church', 'title' => 'Cultes', 'text' => '<p>Dimanche 9 h</p>', 'link' => '/cultes', 'link_text' => 'Horaires']],
            ['id' => 'imgbox0001', 'type' => 'image-box', 'settings' => ['url' => '/img.jpg', 'alt' => 'Élèves', 'title' => 'Primaire', 'ratio' => '4:3']],
            ['id' => 'ilist00001', 'type' => 'icon-list', 'settings' => ['items' => [['icon' => 'lucide:phone', 'text' => '+243 81', 'url' => 'tel:+24381']]]],
            ['id' => 'testi00001', 'type' => 'testimonial', 'settings' => ['text' => "Merci\npour tout", 'name' => 'Amani', 'rating' => 4]],
            ['id' => 'count00001', 'type' => 'counter', 'settings' => ['number' => 12500, 'suffix' => '+', 'label' => 'élèves']],
            ['id' => 'progr00001', 'type' => 'progress', 'settings' => ['label' => 'Collecte', 'percent' => 70]],
            ['id' => 'accor00001', 'type' => 'accordion', 'settings' => ['items' => [['title' => 'Horaires ?', 'html' => '<p>8 h – 16 h</p>']], 'open_first' => true, 'faq_schema' => true]],
            ['id' => 'tabs000001', 'type' => 'tabs', 'settings' => ['items' => [['title' => 'Primaire', 'html' => '<p>A</p>'], ['title' => 'Secondaire', 'html' => '<p>B</p>']]]],
            ['id' => 'gall000001', 'type' => 'gallery', 'settings' => ['images' => [['url' => '/a.jpg', 'alt' => 'A'], ['url' => '/b.jpg', 'alt' => 'B']], 'columns' => 2, 'columns_mobile' => 1]],
            ['id' => 'price00001', 'type' => 'pricing', 'settings' => ['title' => 'Mensuel', 'currency' => '$', 'price' => '20', 'features' => [['text' => 'Repas', 'included' => true], ['text' => 'Transport', 'included' => false]], 'button_text' => 'S’inscrire', 'featured' => true, 'badge' => 'Populaire']],
            ['id' => 'team000001', 'type' => 'team-member', 'settings' => ['name' => 'Dr Kabila', 'role' => 'Pédiatre', 'socials' => [['icon' => 'brand:x', 'url' => 'https://x.com/a']]]],
            ['id' => 'social0001', 'type' => 'social-icons', 'settings' => ['items' => [['icon' => 'brand:facebook', 'url' => 'https://facebook.com/ong']]]],
            ['id' => 'map0000001', 'type' => 'map', 'settings' => ['address' => 'Goma, RDC', 'zoom' => 13]],
            ['id' => 'count00002', 'type' => 'countdown', 'settings' => ['date' => now()->addDays(3)->format('Y-m-d\TH:i')]],
        ]);
        $html = $out['html'];

        $this->assertStringContainsString('<h3 class="gp-ibox__title">Cultes</h3><div class="gp-ibox__text"><p>Dimanche 9 h</p></div><a class="gp-ibox__link" href="/cultes">Horaires', $html);
        $this->assertStringContainsString('alt="Élèves"', $html);
        $this->assertStringContainsString('<a class="gp-ilist__item" href="tel:+24381">', $html);
        $this->assertStringContainsString('Merci<br />', $html);
        $this->assertStringContainsString('aria-label="4 sur 5"', $html);
        $this->assertStringContainsString('data-gp-counter="12500"', $html);
        $this->assertStringContainsString("12\u{202F}500", $html, 'Le chiffre final est lisible sans JavaScript.');
        $this->assertStringContainsString('aria-valuenow="70"', $html);
        $this->assertStringContainsString('<details open><summary>Horaires ?</summary>', $html);
        $this->assertStringContainsString('"@type":"FAQPage"', $html);
        $this->assertStringContainsString('data-gp-tabs', $html);
        $this->assertStringContainsString('<a href="/a.jpg"><img src="/a.jpg" alt="A"', $html);
        $this->assertStringContainsString('gp-price is-featured', $html);
        $this->assertStringContainsString('(non inclus)', $html);
        $this->assertStringContainsString('aria-label="X"', $html);
        $this->assertStringContainsString('aria-label="Facebook" style="--gp-brand:#', $html);
        $this->assertStringContainsString('https://maps.google.com/maps?q=Goma%2C%20RDC&amp;z=13&amp;output=embed', $html);
        $this->assertMatchesRegularExpression('/data-gp-countdown="[^"]+"/', $html);

        $this->assertStringContainsString('#gp-gall000001 .gp-gallery__grid{grid-template-columns:repeat(2, minmax(0, 1fr));gap:12px}', $out['css']);
        $this->assertMatchesRegularExpression('/@media \(max-width: 767px\) \{.*#gp-gall000001 \.gp-gallery__grid\{grid-template-columns:repeat\(1, minmax\(0, 1fr\)\)\}/s', $out['css']);
    }

    public function test_contact_form_stores_the_message_and_sends_an_email(): void
    {
        Mail::fake();
        Setting::write(['forms_email' => 'contact@ong.test']);
        $page = $this->contactPage();

        $html = $this->get('/contact')->assertOk()->getContent();
        $this->assertStringContainsString('action="'.route('forms.submit').'"', $html);
        $this->assertStringContainsString('name="website"', $html, 'Champ piège pour les robots.');
        $this->assertStringContainsString('name="phone"', $html);
        $this->assertStringNotContainsString('name="subject"', $html);

        $this->send($page, ['name' => 'Amani', 'email' => 'amani@test.local', 'phone' => '+243 81', 'message' => 'Bonjour, je voudrais aider.'])
            ->assertRedirect('/contact#gp-form-form000001')->assertSessionHas('gp_form_sent', 'form000001');

        $submission = FormSubmission::sole();
        $this->assertSame(['name' => 'Amani', 'email' => 'amani@test.local', 'phone' => '+243 81', 'message' => 'Bonjour, je voudrais aider.'], $submission->data);
        $this->assertSame($page->id, $submission->page_id);
        $this->assertSame(64, strlen($submission->ip_hash), 'Seule une empreinte de l’adresse IP est conservée.');

        // Après l'envoi, le formulaire laisse place au message de remerciement.
        $this->withSession(['gp_form_sent' => 'form000001'])->get('/contact')->assertSee('Votre message a bien été envoyé');

        // Le réglage privé n'est jamais envoyé au navigateur ni exporté.
        $this->assertArrayNotHasKey('forms_email', Setting::publicValues());
    }

    public function test_contact_form_rejects_bots_and_invalid_input(): void
    {
        $page = $this->contactPage(['show_phone' => false]);

        // Champ piège rempli, ou envoi trop rapide : rien n'est enregistré, mais le robot croit avoir réussi.
        $this->send($page, ['name' => 'Bot', 'email' => 'b@b.b', 'message' => 'spam', 'website' => 'http://spam'])->assertSessionHas('gp_form_sent');
        $this->send($page, ['name' => 'Bot', 'email' => 'b@b.b', 'message' => 'spam'], age: 1)->assertSessionHas('gp_form_sent');
        $this->assertSame(0, FormSubmission::count());

        // Erreurs affichées sous les champs, dans le bon formulaire.
        $this->send($page, ['name' => '', 'email' => 'pas-un-email', 'message' => 'x', 'phone' => 'champ absent'])
            ->assertSessionHasErrors(['name', 'email', 'phone'], null, 'gp_form_form000001');
        $this->assertSame(0, FormSubmission::count());

        // Formulaire inexistant ou page non publiée.
        $this->send($page, ['name' => 'A', 'email' => 'a@b.c', 'message' => 'x', 'widget' => 'autre00001'])->assertNotFound();
        $page->update(['status' => 'draft']);
        $this->send($page, ['name' => 'A', 'email' => 'a@b.c', 'message' => 'x'])->assertNotFound();
    }

    public function test_contact_form_is_rate_limited(): void
    {
        $page = $this->contactPage();
        for ($i = 0; $i < 5; $i++) {
            $this->send($page, ['name' => 'A', 'email' => 'a@test.local', 'message' => 'Message '.$i])->assertRedirect();
        }
        $this->send($page, ['name' => 'A', 'email' => 'a@test.local', 'message' => 'De trop'])->assertStatus(429);
        $this->assertSame(5, FormSubmission::count());
    }

    public function test_messages_are_listed_for_administrators_only(): void
    {
        $admin = $this->admin;
        $editor = User::create(['name' => 'Ed', 'email' => 'ed@test.local', 'password' => 'secret-password-1', 'role' => 'editor']);
        $page = $this->contactPage();
        $message = FormSubmission::create(['page_id' => $page->id, 'widget_id' => 'form000001', 'data' => ['name' => 'Amani', 'email' => 'amani@test.local', 'message' => '<script>x</script>Bonjour']]);

        $this->actingAs($admin)->get('/admin/messages')->assertOk()->assertSee('Amani');
        $this->actingAs($admin)->get("/admin/messages/{$message->id}")->assertOk()
            ->assertSee('&lt;script&gt;x&lt;/script&gt;Bonjour', false)->assertDontSee('<script>x</script>', false);
        $this->assertNotNull($message->fresh()->read_at);

        $this->actingAs($editor)->get('/admin/messages')->assertForbidden();
        $this->actingAs($admin)->delete("/admin/messages/{$message->id}")->assertRedirect('/admin/messages');
        $this->assertSame(0, FormSubmission::count());
    }

    public function test_ai_translation_covers_texts_inside_lists(): void
    {
        $fake = new FakeLlm([['items' => [
            ['key' => 'faq0000001.items.0.title', 'text' => 'Opening hours?'],
            ['key' => 'faq0000001.items.0.html', 'text' => '<p>8 am – 4 pm</p>'],
            ['key' => 'faq0000001.items.9.title', 'text' => 'Inventé'],
            ['key' => '__title', 'text' => 'Questions'],
        ]]]);
        $this->app->instance(LlmGateway::class, $fake);
        Setting::write(['locales' => ['fr', 'en']]);
        $admin = $this->admin;
        $page = Page::create(['title' => 'Questions', 'slug' => 'questions', 'content' => ['elements' => [['id' => 'box0000001', 'type' => 'container', 'settings' => [], 'children' => [
            ['id' => 'faq0000001', 'type' => 'accordion', 'settings' => ['items' => [['title' => 'Horaires ?', 'html' => '<p>8 h – 16 h</p>']]]],
        ]]]]]);

        $this->actingAs($admin)->postJson("/admin/ai/pages/{$page->id}/translate", ['locale' => 'en'])->assertOk();

        $this->assertStringContainsString('faq0000001.items.0.title', $fake->calls[0]['content'][0]['text']);
        $translation = Page::where('locale', 'en')->sole();
        $items = $this->widgetsOf($translation->content)[0]['settings']['items'];
        $this->assertSame([['title' => 'Opening hours?', 'html' => '<p>8 am – 4 pm</p>']], $items, 'Les éléments inventés par l’IA sont ignorés.');
    }
}
