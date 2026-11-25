<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** « Mon compte » : nom, e-mail et mot de passe, toujours avec le mot de passe actuel. */
class AccountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->user = User::create(['name' => 'Amani', 'email' => 'amani@test.local', 'password' => 'ancien-mot-2026', 'role' => 'editor']);
        User::create(['name' => 'Autre', 'email' => 'autre@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
    }

    public function test_page_is_reachable_from_the_menu(): void
    {
        $this->get(route('admin.account.edit'))->assertRedirect();
        $this->actingAs($this->user)->get(route('admin.dashboard'))->assertOk()->assertSee(route('admin.account.edit'), false);
        $this->get(route('admin.account.edit'))->assertOk()->assertSee('amani@test.local');
    }

    public function test_profile_needs_the_current_password_and_a_free_email(): void
    {
        $this->actingAs($this->user);
        $this->put(route('admin.account.update'), ['name' => 'Amani K.', 'email' => 'nouveau@test.local', 'profile_password' => 'faux'])
            ->assertSessionHasErrors('profile_password');
        $this->put(route('admin.account.update'), ['name' => 'Amani K.', 'email' => 'autre@test.local', 'profile_password' => 'ancien-mot-2026'])
            ->assertSessionHasErrors('email');

        $this->put(route('admin.account.update'), ['name' => 'Amani K.', 'email' => 'Nouveau@Test.local', 'profile_password' => 'ancien-mot-2026'])
            ->assertRedirect(route('admin.account.edit'));
        $this->user->refresh();
        $this->assertSame(['Amani K.', 'nouveau@test.local', 'editor'], [$this->user->name, $this->user->email, $this->user->role]);
    }

    public function test_password_change(): void
    {
        $this->actingAs($this->user);
        $token = $this->user->getRememberToken();

        $this->put(route('admin.account.password'), ['current_password' => 'faux', 'password' => 'nouveau-mot-2027', 'password_confirmation' => 'nouveau-mot-2027'])
            ->assertSessionHasErrors('current_password');
        $this->put(route('admin.account.password'), ['current_password' => 'ancien-mot-2026', 'password' => 'court1', 'password_confirmation' => 'court1'])
            ->assertSessionHasErrors('password');
        $this->put(route('admin.account.password'), ['current_password' => 'ancien-mot-2026', 'password' => 'nouveau-mot-2027', 'password_confirmation' => 'autre-mot-2027'])
            ->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('ancien-mot-2026', $this->user->fresh()->password));

        $this->put(route('admin.account.password'), ['current_password' => 'ancien-mot-2026', 'password' => 'nouveau-mot-2027', 'password_confirmation' => 'nouveau-mot-2027'])
            ->assertRedirect(route('admin.account.edit'))->assertSessionHas('status');
        $fresh = $this->user->fresh();
        $this->assertTrue(Hash::check('nouveau-mot-2027', $fresh->password));
        $this->assertNotSame($token, $fresh->getRememberToken(), 'Les autres connexions « Se souvenir de moi » sont coupées.');
    }

    public function test_attempts_are_limited(): void
    {
        $this->actingAs($this->user);
        foreach (range(1, 6) as $i) {
            $this->put(route('admin.account.password'), ['current_password' => 'faux-'.$i, 'password' => 'nouveau-mot-2027', 'password_confirmation' => 'nouveau-mot-2027']);
        }
        $this->put(route('admin.account.password'), ['current_password' => 'ancien-mot-2026', 'password' => 'nouveau-mot-2027', 'password_confirmation' => 'nouveau-mot-2027'])
            ->assertStatus(429);
    }
}
