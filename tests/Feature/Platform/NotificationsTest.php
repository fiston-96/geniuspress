<?php

namespace Tests\Feature\Platform;

use App\Hosting\Site;
use App\Models\FormSubmission;
use App\Models\Page;
use App\Models\User;
use App\Notifications\ContactMessageReceived;
use App\Platform\Ai\CreditAccount;
use App\Platform\Models\Order;
use App\Platform\Models\Payment;
use App\Platform\Notifications\AiCreditsLow;
use App\Platform\Notifications\CustomerWelcome;
use App\Platform\Notifications\NewCustomer;
use App\Platform\Notifications\OrderPaid;
use App\Platform\Notifications\TrialEndingSoon;
use App\Platform\PlatformAccounts;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/** Notifications : cloche de l'administration et de l'espace client, e-mails importants. */
class NotificationsTest extends PlatformTestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->admin = User::create(['name' => 'Exploitant', 'email' => 'admin@test.local', 'password' => 'secret-password-1', 'role' => 'admin', 'locale' => 'fr']);
    }

    public function test_contact_message_rings_the_admin_bell(): void
    {
        $page = Page::create(['title' => 'Contact', 'slug' => 'contact', 'status' => 'published', 'content' => ['elements' => [
            ['id' => 'box0000001', 'type' => 'container', 'settings' => [], 'children' => [
                ['id' => 'form000001', 'type' => 'contact-form', 'settings' => []],
            ]],
        ]]]);
        $this->from('/contact')->post(route('forms.submit'), ['page' => $page->id, 'widget' => 'form000001', '_ts' => Crypt::encryptString((string) (time() - 10)),
            'name' => 'Bahati', 'email' => 'bahati@test.local', 'message' => 'Bonjour, nous voulons un devis.'])->assertSessionHas('gp_form_sent');

        $notification = $this->admin->notifications()->sole();
        $this->assertSame(ContactMessageReceived::class, $notification->type);
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Notifications (1 non lue)', false);
        $this->get(route('admin.notifications.index'))->assertOk()->assertSee('Nouveau message de Bahati')->assertSee('Bonjour, nous voulons un devis.');

        $this->get(route('admin.notifications.open', $notification->id))->assertRedirect(route('admin.messages.show', FormSubmission::sole()));
        $this->assertNotNull($notification->fresh()->read_at);
        $this->get(route('admin.dashboard'))->assertDontSee('non lue');
    }

    public function test_customer_lifecycle_notifications(): void
    {
        $customer = app(PlatformAccounts::class)->register('Amani', 'amani@test.local', 'mot-de-passe-2026');

        $this->assertSame([CustomerWelcome::class], $customer->notifications()->pluck('type')->all());
        $this->assertSame([NewCustomer::class], $this->admin->notifications()->pluck('type')->all());

        // Paiement confirmé : e-mail + cloche pour le client, cloche pour l'exploitant.
        $plan = $this->plan();
        $this->actingAs($customer, 'customer')->post(route('platform.customer.plans.buy', $plan));
        $order = Order::sole();
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'operator' => 'orange', 'phone' => '+243810000000']);
        $this->post(route('platform.sandbox.complete', Payment::sole()), ['result' => 'success']);

        $paid = $customer->notifications()->where('type', OrderPaid::class)->sole();
        $this->assertSame('Paiement reçu : commande :number', $paid->data['title']);
        $this->assertSame(2, $this->admin->notifications()->count());

        $this->get(route('platform.customer.notifications.index'))->assertOk()
            ->assertSee('Paiement reçu : commande '.$order->number)->assertSee('Bienvenue sur GeniusPress');
        $this->post(route('platform.customer.notifications.read'))->assertRedirect();
        $this->assertSame(0, $customer->unreadNotifications()->count());

        // Un client ne lit pas les notifications d'un autre compte.
        $other = $this->admin->notifications()->first();
        $this->get(route('platform.customer.notifications.open', $other->id))->assertNotFound();
    }

    public function test_important_notices_are_also_emailed(): void
    {
        Notification::fake();
        $customer = $this->customer();
        app(PlatformAccounts::class)->register('Neema', 'neema@test.local', 'mot-de-passe-2026');

        Notification::assertSentTo(\App\Platform\Models\Customer::where('email', 'neema@test.local')->sole(), CustomerWelcome::class,
            fn ($n, array $channels) => $channels === ['database', 'mail']);
        Notification::assertSentTo($this->admin, NewCustomer::class, fn ($n, array $channels) => $channels === ['database']);
    }

    public function test_low_ai_credits_alert_is_sent_once_when_crossing_the_threshold(): void
    {
        $customer = $this->customer();
        $credits = app(CreditAccount::class);
        $credits->credit($customer, 1_000_000, 'purchase', 'Pack');

        $credits->debit($customer, 400_000, 'usage', 'IA'); // 0,60 $ : au-dessus du seuil (0,50 $)
        $this->assertSame(0, $customer->notifications()->count());
        $credits->debit($customer, 200_000, 'usage', 'IA'); // 0,40 $ : passe sous le seuil
        $credits->debit($customer, 100_000, 'usage', 'IA'); // reste sous le seuil : pas de nouvelle alerte

        $this->assertSame([AiCreditsLow::class], $customer->notifications()->pluck('type')->all());
        $this->assertSame("0,4000\u{00A0}$", $customer->notifications()->sole()->data['body_params']['balance'] ?? null);
    }

    public function test_trial_reminders_are_sent_once_per_step(): void
    {
        $customer = $this->customer();
        Site::create(['slug' => 'cooperative', 'name' => 'Coopérative', 'account_id' => 'customer:'.$customer->id, 'owner_email' => $customer->email,
            'kind' => 'trial', 'status' => Site::STATUS_ACTIVE, 'expires_at' => now()->addDays(3)->subHour()]);

        $this->artisan('geniuspress:trial-reminders')->assertSuccessful();
        $this->artisan('geniuspress:trial-reminders')->assertSuccessful();
        $notice = $customer->notifications()->sole();
        $this->assertSame(TrialEndingSoon::class, $notice->type);
        $this->assertSame(['days' => 3], $notice->data['title_params']);

        $this->travel(2)->days();
        $this->artisan('geniuspress:trial-reminders')->assertSuccessful();
        $this->assertSame('Votre essai se termine demain', $customer->notifications()->latest()->first()->data['title']);
        $this->assertSame(2, DatabaseNotification::query()->where('notifiable_id', $customer->id)->count());
    }
}
