<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordLink;
use App\Platform\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Mot de passe oublié : administration d'un site et espace client de la plateforme. */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['geniuspress.platform.enabled' => true]);
        file_put_contents(config('geniuspress.install_lock'), 'test');
        Notification::fake();
    }

    public function test_admin_user_resets_the_password_by_email_link(): void
    {
        $user = User::create(['name' => 'Amani', 'email' => 'amani@test.local', 'password' => 'ancien-mot-2026', 'role' => 'admin', 'locale' => 'en']);

        $this->get(route('login'))->assertSee(route('password.request'));
        $this->get(route('password.request'))->assertOk()->assertSee('Recevoir le lien');

        // Même réponse, que l'adresse existe ou non.
        $this->post(route('password.email'), ['email' => 'inconnu@test.local'])->assertSessionHas('status');
        Notification::assertNothingSent();
        $this->post(route('password.email'), ['email' => 'AMANI@test.local'])->assertSessionHas('status');

        $url = null;
        Notification::assertSentTo($user, ResetPasswordLink::class, function (ResetPasswordLink $n, array $channels, User $notifiable) use (&$url) {
            $url = $n->url;
            $mail = $n->toMail($notifiable);

            return $channels === ['mail'] && str_starts_with($n->url, url('/admin/nouveau-mot-de-passe/'))
                && $notifiable->preferredLocale() === 'en' && $mail->actionUrl === $n->url;
        });
        $token = basename(parse_url($url, PHP_URL_PATH));

        $this->get($url)->assertOk()->assertSee('Nouveau mot de passe')->assertSee('amani@test.local');
        $this->post(route('password.update'), ['token' => $token, 'email' => 'amani@test.local', 'password' => 'court', 'password_confirmation' => 'court'])
            ->assertSessionHasErrors('password');
        $this->post(route('password.update'), ['token' => $token, 'email' => 'amani@test.local', 'password' => 'nouveau-mot-2026', 'password_confirmation' => 'nouveau-mot-2026'])
            ->assertRedirect(route('login'))->assertSessionHas('status');
        $this->assertTrue(Hash::check('nouveau-mot-2026', $user->fresh()->password));

        // Lien à usage unique.
        $this->post(route('password.update'), ['token' => $token, 'email' => 'amani@test.local', 'password' => 'autre-mot-2026', 'password_confirmation' => 'autre-mot-2026'])
            ->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('nouveau-mot-2026', $user->fresh()->password));
    }

    public function test_platform_customer_resets_the_password_separately_from_admins(): void
    {
        // Même adresse pour un administrateur et un client : jetons et comptes séparés.
        $admin = User::create(['name' => 'Exploitant', 'email' => 'meme@test.local', 'password' => 'admin-mot-2026', 'role' => 'admin']);
        $customer = Customer::create(['name' => 'Cliente', 'email' => 'meme@test.local', 'password' => 'client-mot-2026', 'status' => 'active']);

        $this->get(route('platform.login'))->assertSee(route('platform.password.request'));
        $this->post(route('platform.password.email'), ['email' => 'meme@test.local'])->assertSessionHas('status');
        Notification::assertNotSentTo($admin, ResetPasswordLink::class);

        $url = null;
        Notification::assertSentTo($customer, ResetPasswordLink::class, function (ResetPasswordLink $n) use (&$url) {
            $url = $n->url;

            return str_starts_with($n->url, url('/compte/nouveau-mot-de-passe/'));
        });
        $token = basename(parse_url($url, PHP_URL_PATH));

        // Le jeton client ne sert pas dans l'administration.
        $this->post(route('password.update'), ['token' => $token, 'email' => 'meme@test.local', 'password' => 'pirate-mot-2026', 'password_confirmation' => 'pirate-mot-2026'])
            ->assertSessionHasErrors('email');

        $this->post(route('platform.password.update'), ['token' => $token, 'email' => 'meme@test.local', 'password' => 'client-neuf-2026', 'password_confirmation' => 'client-neuf-2026'])
            ->assertRedirect(route('platform.login'));
        $this->assertTrue(Hash::check('client-neuf-2026', $customer->fresh()->password));
        $this->assertTrue(Hash::check('admin-mot-2026', $admin->fresh()->password));

        $this->post(route('platform.login'), ['email' => 'meme@test.local', 'password' => 'client-neuf-2026'])->assertRedirect();
        $this->assertTrue(Auth::guard('customer')->check());
    }
}
