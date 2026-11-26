<?php

namespace Tests\Feature;

use App\Mail\NewsletterMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\User;
use App\Newsletter\Newsletter;
use App\Notifications\NewsletterConfirmation;
use App\Notifications\NewSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Newsletter intégrée : widget, double confirmation, désinscription, campagnes envoyées par lots. */
class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Mail::fake();
        $this->admin = User::create(['name' => 'Amani', 'email' => 'admin@test.local', 'password' => 'secret-password-1', 'role' => 'admin', 'locale' => 'fr']);
    }

    private function newsPage(): Page
    {
        return Page::create(['title' => 'Actualités', 'slug' => 'actualites', 'status' => 'published', 'content' => ['elements' => [
            ['id' => 'box0000001', 'type' => 'container', 'settings' => [], 'children' => [
                ['id' => 'news000001', 'type' => 'newsletter', 'settings' => ['show_name' => true, 'button_text' => 'Je m’abonne']],
            ]],
        ]]]);
    }

    private function signUp(Page $page, array $data, int $age = 10)
    {
        return $this->from('/actualites')->post(route('newsletter.subscribe'), $data + [
            'page' => $page->id, 'widget' => 'news000001', '_ts' => Crypt::encryptString((string) (time() - $age)),
        ]);
    }

    public function test_visitor_subscribes_with_double_opt_in(): void
    {
        Notification::fake();
        $page = $this->newsPage();
        $html = $this->get('/actualites')->assertOk()->getContent();
        $this->assertStringContainsString('action="'.route('newsletter.subscribe').'"', $html);
        $this->assertStringContainsString('Je m’abonne', $html);
        $this->assertStringContainsString('name="website"', $html, 'Champ piège pour les robots.');

        // Robots et adresses invalides.
        $this->signUp($page, ['email' => 'bot@spam.test', 'website' => 'x'])->assertSessionHas('gp_news_sent');
        $this->signUp($page, ['email' => 'bot@spam.test'], age: 1)->assertSessionHas('gp_news_sent');
        $this->signUp($page, ['email' => 'pas-une-adresse'])->assertSessionHasErrors('email', null, 'gp_news_news000001');
        $this->assertSame(0, NewsletterSubscriber::count());
        $this->post(route('newsletter.subscribe'), ['page' => $page->id, 'widget' => 'autre00001', 'email' => 'a@b.c'])->assertNotFound();

        $this->signUp($page, ['email' => 'Neema@Test.local', 'name' => 'Neema'])->assertRedirect('/actualites#gp-news-news000001')->assertSessionHas('gp_news_sent', 'news000001');
        $subscriber = NewsletterSubscriber::sole();
        $this->assertSame(['neema@test.local', 'Neema', 'pending', 'Actualités'], [$subscriber->email, $subscriber->name, $subscriber->status, $subscriber->source]);
        $this->withSession(['gp_news_sent' => 'news000001'])->get('/actualites')->assertSee('Ouvrez l’e-mail que nous venons de vous envoyer');

        $url = null;
        Notification::assertSentOnDemand(NewsletterConfirmation::class, function ($n, $channels, $notifiable) use (&$url) {
            $url = $n->url;

            return $notifiable->routes['mail'] === 'neema@test.local';
        });
        Notification::assertNothingSentTo($this->admin);

        $this->get($url)->assertOk()->assertSee('Inscription confirmée');
        $this->assertSame('subscribed', $subscriber->fresh()->status);
        Notification::assertSentTo($this->admin, NewSubscriber::class);
        $this->get($url)->assertOk();
        Notification::assertSentToTimes($this->admin, NewSubscriber::class, 1);
    }

    public function test_unsubscribe_page_and_one_click(): void
    {
        $subscriber = app(Newsletter::class)->subscribe('kahindo@test.local', verified: true);
        $url = route('newsletter.unsubscribe', $subscriber->token);

        // Ouvrir le lien ne désinscrit pas (les antivirus ouvrent les liens) : il faut le bouton.
        $this->get($url)->assertOk()->assertSee('Me désinscrire');
        $this->assertSame('subscribed', $subscriber->fresh()->status);

        // Bouton de la messagerie : POST sans jeton CSRF.
        $this->post($url)->assertOk()->assertSee('Vous êtes désinscrit');
        $this->assertSame('unsubscribed', $subscriber->fresh()->status);
        $this->get(route('newsletter.unsubscribe', str_repeat('a', 48)))->assertOk();
    }

    public function test_campaign_is_sent_once_to_subscribed_people_in_the_chosen_language(): void
    {
        $newsletter = app(Newsletter::class);
        $fr = $newsletter->subscribe('fr@test.local', 'Furaha', 'fr', verified: true);
        $newsletter->subscribe('en@test.local', 'Grace', 'en', verified: true);
        $newsletter->subscribe('attente@test.local', null, 'fr'); // non confirmé
        $gone = $newsletter->subscribe('parti@test.local', null, 'fr', verified: true);
        $newsletter->unsubscribe($gone->token);

        $this->actingAs($this->admin)->get(route('admin.newsletter.index'))->assertOk()->assertSee('Abonnés inscrits');
        $this->post(route('admin.newsletter.campaigns.store'), ['subject' => 'Nouvelles de novembre', 'content' => "# Bonjour\n\nNos **nouveaux** projets. <script>alert(1)</script>", 'locale' => 'fr'])
            ->assertRedirect();
        $campaign = NewsletterCampaign::sole();
        $this->assertStringNotContainsString('<script>', $campaign->content);
        $this->get(route('admin.newsletter.campaigns.edit', $campaign))->assertOk()->assertSee('<strong>nouveaux</strong>', false)->assertSee('1 abonné inscrit.');

        $this->post(route('admin.newsletter.campaigns.test', $campaign))->assertSessionHas('status');
        Mail::assertSent(NewsletterMail::class, fn ($m) => $m->hasTo('admin@test.local'));

        $this->post(route('admin.newsletter.campaigns.send', $campaign))->assertRedirect(route('admin.newsletter.index'));
        $campaign->refresh();
        $this->assertSame(['sent', 1, 1], [$campaign->status, $campaign->recipients, $campaign->sent_count]);
        Mail::assertSent(NewsletterMail::class, function (NewsletterMail $mail) use ($fr) {
            $headers = $mail->headers()->text;

            return $mail->hasTo('fr@test.local') && $headers['List-Unsubscribe'] === '<'.route('newsletter.unsubscribe', $fr->token).'>'
                && $headers['List-Unsubscribe-Post'] === 'List-Unsubscribe=One-Click';
        });
        Mail::assertNotSent(NewsletterMail::class, fn ($m) => $m->hasTo('en@test.local') || $m->hasTo('attente@test.local') || $m->hasTo('parti@test.local'));

        // Jamais deux fois : un second passage n'envoie rien, une campagne envoyée ne se renvoie pas.
        $this->assertSame(0, $newsletter->sendBatch());
        $this->post(route('admin.newsletter.campaigns.send', $campaign))->assertForbidden();
        $this->assertSame(1, NewsletterDelivery::count());
        $this->put(route('admin.newsletter.campaigns.update', $campaign), ['subject' => 'Autre', 'content' => 'x'])->assertForbidden();
    }

    public function test_batches_follow_the_rate_and_resume(): void
    {
        $newsletter = app(Newsletter::class);
        foreach (range(1, 5) as $i) {
            $newsletter->subscribe("abonne{$i}@test.local", verified: true);
        }
        $campaign = NewsletterCampaign::create(['subject' => 'Lots', 'content' => 'Bonjour', 'status' => 'draft']);
        $this->assertSame(5, $newsletter->queue($campaign));

        $this->assertSame(2, $newsletter->sendBatch(2));
        $this->assertSame(3, $newsletter->pending());
        $this->assertSame('sending', $campaign->fresh()->status);
        $this->artisan('geniuspress:newsletter-send')->assertSuccessful();
        $this->assertSame(0, $newsletter->pending());
        $this->assertSame(['sent', 5], [$campaign->fresh()->status, $campaign->fresh()->sent_count]);
        Mail::assertSent(NewsletterMail::class, 5);
    }

    public function test_export_and_admin_only_access(): void
    {
        app(Newsletter::class)->subscribe('inscrit@test.local', 'Inscrit', verified: true);
        app(Newsletter::class)->subscribe('attente@test.local');

        $csv = $this->actingAs($this->admin)->get(route('admin.newsletter.export'))->assertOk()->streamedContent();
        $this->assertStringContainsString('inscrit@test.local;Inscrit;fr', $csv);
        $this->assertStringNotContainsString('attente@test.local', $csv);

        $editor = User::create(['name' => 'Éditrice', 'email' => 'editrice@test.local', 'password' => 'secret-password-1', 'role' => 'editor']);
        $this->actingAs($editor)->get(route('admin.newsletter.index'))->assertForbidden();
        $this->get(route('admin.newsletter.export'))->assertForbidden();
    }
}
