<?php

namespace Tests\Feature;

use App\Auth\GoogleOAuth;
use App\Models\User;
use App\Platform\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** « Continuer avec Google » : espace client (connexion ou inscription) et administration (comptes existants). */
class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['geniuspress.platform.enabled' => true, 'services.google.client_id' => 'client-test.apps.googleusercontent.com', 'services.google.client_secret' => 'secret-test']);
        file_put_contents(config('geniuspress.install_lock'), 'test');
    }

    private function fakeGoogle(array $profile): void
    {
        Http::fake([
            GoogleOAuth::TOKEN => Http::response(['access_token' => 'jeton-test', 'token_type' => 'Bearer', 'expires_in' => 3599]),
            GoogleOAuth::USERINFO => Http::response($profile + ['sub' => '1234567890', 'email' => 'amani@gmail.com', 'email_verified' => true, 'name' => 'Amani Kahindo', 'locale' => 'fr']),
        ]);
    }

    /** Va jusqu'à Google puis revient avec un code ; renvoie la réponse du retour. */
    private function roundTrip(string $start, string $callback, array $query = [])
    {
        $redirect = $this->get(route($start))->assertRedirect()->headers->get('Location');
        $this->assertStringStartsWith(GoogleOAuth::AUTHORIZE, $redirect);
        parse_str((string) parse_url($redirect, PHP_URL_QUERY), $params);
        $this->assertSame('client-test.apps.googleusercontent.com', $params['client_id']);
        $this->assertSame(route($callback), $params['redirect_uri']);
        $this->assertSame('openid email profile', $params['scope']);

        return $this->get(route($callback, $query + ['state' => $params['state'], 'code' => 'code-test']));
    }

    public function test_new_customer_signs_up_with_google(): void
    {
        $this->fakeGoogle([]);
        $this->get(route('platform.register'))->assertSee('Continuer avec Google');

        $this->roundTrip('platform.google.redirect', 'platform.google.callback')
            ->assertRedirect(route('platform.customer.dashboard'))->assertSessionHas('status', 'Bienvenue ! Votre compte GeniusPress est créé.');

        $customer = Customer::query()->sole();
        $this->assertSame(['Amani Kahindo', 'amani@gmail.com', '1234567890'], [$customer->name, $customer->email, $customer->google_id]);
        $this->assertNotNull($customer->email_verified_at);
        $this->assertTrue(Auth::guard('customer')->check());
        $this->assertFalse(Auth::guard('web')->check(), 'Jamais connecté à l’administration.');

        // Le code est échangé côté serveur, avec le secret.
        Http::assertSent(fn ($r) => $r->url() === GoogleOAuth::TOKEN && $r['client_secret'] === 'secret-test' && $r['code'] === 'code-test');
    }

    public function test_existing_customer_is_linked_and_state_is_checked(): void
    {
        $this->fakeGoogle([]);
        $customer = Customer::create(['name' => 'Amani', 'email' => 'amani@gmail.com', 'password' => 'secret-password-1', 'status' => 'active']);

        // Retour sans demande préalable (ou avec un autre « state ») : refusé, rien n'est appelé.
        $this->get(route('platform.google.callback', ['state' => 'falsifie', 'code' => 'x']))->assertRedirect(route('platform.login'))->assertSessionHasErrors('email');
        Http::assertNothingSent();

        $this->roundTrip('platform.google.redirect', 'platform.google.callback')->assertRedirect(route('platform.customer.dashboard'));
        $this->assertSame(1, Customer::query()->count());
        $this->assertSame('1234567890', $customer->fresh()->google_id);
        $this->assertSame($customer->id, Auth::guard('customer')->id());
    }

    public function test_unverified_google_address_is_refused(): void
    {
        $this->fakeGoogle(['email_verified' => false]);
        $this->roundTrip('platform.google.redirect', 'platform.google.callback')->assertRedirect(route('platform.login'))
            ->assertSessionHasErrors(['email' => 'Votre adresse Google n’est pas vérifiée. Vérifiez-la chez Google ou créez un compte avec un mot de passe.']);
        $this->assertSame(0, Customer::query()->count());
    }

    public function test_suspended_customer_cannot_sign_in_with_google(): void
    {
        $this->fakeGoogle([]);
        Customer::create(['name' => 'Amani', 'email' => 'amani@gmail.com', 'password' => 'secret-password-1', 'status' => 'suspended']);
        $this->roundTrip('platform.google.redirect', 'platform.google.callback')->assertRedirect(route('platform.login'))
            ->assertSessionHasErrors(['email' => 'Ce compte est suspendu. Contactez-nous.']);
        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_admin_google_login_only_for_existing_accounts(): void
    {
        $this->fakeGoogle([]);
        $this->get(route('login'))->assertSee('Continuer avec Google');

        $this->roundTrip('google.redirect', 'google.callback')->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Aucun compte de cette administration n’utilise cette adresse Google. Demandez à un administrateur de vous créer un compte.']);
        $this->assertSame(0, User::query()->count());

        $user = User::create(['name' => 'Amani', 'email' => 'amani@gmail.com', 'password' => 'secret-password-1', 'role' => 'editor']);
        $this->roundTrip('google.redirect', 'google.callback')->assertRedirect(route('admin.dashboard'));
        $this->assertSame($user->id, Auth::id());
        $this->assertSame('1234567890', $user->fresh()->google_id);
    }

    public function test_google_is_hidden_without_credentials(): void
    {
        config(['services.google.client_id' => null]);
        $this->get(route('login'))->assertOk()->assertDontSee('Continuer avec Google');
        $this->get(route('google.redirect'))->assertNotFound();
    }
}
