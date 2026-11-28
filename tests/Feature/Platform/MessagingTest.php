<?php

namespace Tests\Feature\Platform;

use App\Models\Setting;
use App\Platform\Checkout;
use App\Platform\Messaging\AfricasTalkingGateway;
use App\Platform\Messaging\Sms;
use App\Platform\Models\Operator;
use App\Platform\Models\SmsMessage;
use App\Platform\Notifications\OrderPaid;
use App\Platform\PlatformException;
use App\Platform\PlatformNotifier;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/** SMS de la plateforme par Africa's Talking : API simulée (Http::fake), clés factices, jamais de vrai SMS. */
class MessagingTest extends PlatformTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Setting::write([
            'platform_sms_enabled' => true,
            'platform_at_username' => 'geniuspress',
            'platform_at_key' => Crypt::encryptString('cle-at-factice-0000'),
            'platform_at_sender' => 'GENIUSPRESS',
        ]);
    }

    private function accepted(string $number = '+221770000001'): array
    {
        return ['SMSMessageData' => ['Message' => 'Sent to 1/1 Total Cost: XOF 25', 'Recipients' => [
            ['statusCode' => 101, 'number' => $number, 'status' => 'Success', 'cost' => 'XOF 25.0000', 'messageId' => 'ATXid_123'],
        ]]];
    }

    public function test_production_and_sandbox_requests_and_refusals(): void
    {
        Http::fake(['api.africastalking.com/*' => Http::response($this->accepted())]);
        $result = app(AfricasTalkingGateway::class)->send('+221770000001', 'Bonjour');
        $this->assertSame(['ATXid_123', 'XOF 25.0000'], [$result->id, $result->cost]);
        Http::assertSent(fn (Request $r) => $r->url() === 'https://api.africastalking.com/version1/messaging/bulk'
            && $r->hasHeader('apiKey', 'cle-at-factice-0000') && $r['username'] === 'geniuspress'
            && $r['phoneNumbers'] === ['+221770000001'] && $r['senderId'] === 'GENIUSPRESS');

        // Bac à sable : autre adresse, formulaire, aucun vrai SMS.
        // Puis refus du prestataire (solde) : message en français.
        Setting::write(['platform_at_username' => 'sandbox']);
        Http::fake(['api.sandbox.africastalking.com/*' => Http::sequence()
            ->push($this->accepted())
            ->push(['SMSMessageData' => ['Recipients' => [['statusCode' => 405, 'number' => '+221770000001']]]])]);
        app(AfricasTalkingGateway::class)->send('+221770000001', 'Bonjour');
        Http::assertSent(fn (Request $r) => $r->url() === 'https://api.sandbox.africastalking.com/version1/messaging' && $r['to'] === '+221770000001');

        $this->expectExceptionMessage('Solde Africa’s Talking insuffisant.');
        app(AfricasTalkingGateway::class)->send('+221770000001', 'Bonjour');
    }

    public function test_important_notifications_are_also_sent_by_sms_when_possible(): void
    {
        Http::fake(['api.africastalking.com/*' => Http::response($this->accepted())]);
        $plan = $this->plan(['slug' => 'credits', 'kind' => 'ai_credits', 'period' => 'once', 'trial_days' => 0, 'price' => 1000, 'currency' => 'USD']);

        $senegal = $this->customer(['phone' => '+221 77 000 00 01', 'country' => 'SN']);
        PlatformNotifier::send($senegal, new OrderPaid(app(Checkout::class)->forPlan($senegal, $plan)));
        $sms = SmsMessage::query()->sole();
        $this->assertSame(['sent', '+221770000001', 'OrderPaid', 'africastalking', 'XOF 25.0000'], [$sms->status, $sms->to, $sms->kind, $sms->provider, $sms->cost]);
        Http::assertSent(fn (Request $r) => str_starts_with($r['message'], 'GeniusPress : Paiement reçu'));
        $this->assertSame(1, $senegal->notifications()->count(), 'La cloche reste.');

        // RD Congo : pas encore couverte, journalisé « non envoyé », sans appel.
        $congo = $this->customer(['phone' => '+243810000000', 'country' => 'CD']);
        PlatformNotifier::send($congo, new OrderPaid(app(Checkout::class)->forPlan($congo, $plan)));
        $this->assertSame('skipped', SmsMessage::query()->latest('id')->first()->status);
        Http::assertSentCount(1);

        // Client qui refuse les SMS, numéro sans indicatif, notification pour l'exploitant : rien.
        $refus = $this->customer(['phone' => '+221770000002', 'sms_opt_in' => false]);
        $local = $this->customer(['phone' => '770000003']);
        foreach ([$refus, $local] as $customer) {
            PlatformNotifier::send($customer, new OrderPaid(app(Checkout::class)->forPlan($customer, $plan)));
        }
        $this->assertNull((new OrderPaid(app(Checkout::class)->forPlan($senegal, $plan), forAdmin: true))->sms($senegal));
        $this->assertSame(2, SmsMessage::count());

        // SMS désactivés dans la console : plus aucun envoi.
        Setting::write(['platform_sms_enabled' => false]);
        PlatformNotifier::send($senegal, new OrderPaid(app(Checkout::class)->forPlan($senegal, $plan)));
        $this->assertSame(2, SmsMessage::count());
        Http::assertSentCount(1);
    }

    public function test_console_keeps_the_key_encrypted_and_sends_a_test_sms(): void
    {
        $operator = Operator::create(['name' => 'Exploitant', 'email' => 'ops@test.local', 'password' => 'secret-password-1']);
        $this->actingAs($operator, 'operator')->get(route('console.messaging.index'))->assertOk()
            ->assertSee('Sénégal')->assertSee('RD Congo', false)->assertDontSee('cle-at-factice-0000');

        $this->put(route('console.messaging.settings'), ['enabled' => '1', 'username' => 'geniuspress', 'api_key' => 'nouvelle-cle-at-9876', 'sender' => 'GENIUS'])->assertSessionHas('status');
        $this->assertNotSame('nouvelle-cle-at-9876', Setting::read('platform_at_key'));
        $this->assertSame('nouvelle-cle-at-9876', Crypt::decryptString(Setting::read('platform_at_key')));
        $this->assertArrayNotHasKey('platform_at_key', Setting::publicValues());
        $this->get(route('console.messaging.index'))->assertDontSee('nouvelle-cle-at-9876')->assertSee('…9876');

        $this->post(route('console.messaging.test'), ['phone' => '770000001'])->assertSessionHasErrors('phone');
        Http::fake(['api.africastalking.com/*' => Http::response($this->accepted())]);
        $this->post(route('console.messaging.test'), ['phone' => '+221 77 000 00 01'])->assertSessionHas('status');
        $this->assertSame(['test', 'sent'], [SmsMessage::sole()->kind, SmsMessage::sole()->status]);
        Http::assertSent(fn (Request $r) => $r->hasHeader('apiKey', 'nouvelle-cle-at-9876'));
    }

    public function test_customer_sets_phone_and_can_refuse_sms(): void
    {
        $customer = $this->customer();
        // Réglages du compte (pays, téléphone, SMS) : page « Mon compte ».
        $this->actingAs($customer, 'customer')->get(route('platform.customer.account'))->assertOk()
            ->assertSee('Recevoir par SMS les messages importants', false)->assertSee('Ajoutez votre numéro', false);

        $this->put(route('platform.customer.country'), ['country' => 'SN', 'phone' => '+221 77 000 00 01'])->assertSessionHas('status');
        $this->assertSame('+221 77 000 00 01', $customer->fresh()->phone);
        $this->assertSame('+221770000001', Sms::normalize($customer->fresh()->phone));

        $this->put(route('platform.customer.sms'), ['sms_opt_in' => '0'])->assertSessionHas('status');
        $this->assertFalse($customer->fresh()->sms_opt_in);
        $this->assertNull(app(Sms::class)->toCustomer($customer->fresh(), 'Bonjour', 'test'));
    }

    public function test_unconfigured_gateway_refuses_cleanly(): void
    {
        Setting::write(['platform_at_key' => null]);
        $this->assertFalse(app(AfricasTalkingGateway::class)->configured());
        $this->assertSame('skipped', app(Sms::class)->send('+221770000001', 'Bonjour', 'test')->status);
        $this->expectException(PlatformException::class);
        app(AfricasTalkingGateway::class)->send('+221770000001', 'Bonjour');
    }
}
