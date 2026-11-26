<?php

namespace App\Http\Controllers\Auth;

use App\Auth\GoogleOAuth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Platform\Models\Customer;
use App\Platform\PlatformAccounts;
use App\Platform\PlatformException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * « Continuer avec Google », pour les deux publics :
 * - « customer » (espace client de la plateforme) : connexion, ou création du compte s'il
 *   n'existe pas encore ; un compte existant avec la même adresse est relié à Google ;
 * - « admin » (administration d'un site) : connexion à un compte existant seulement.
 *   Personne ne peut s'inscrire seul dans l'administration.
 */
class GoogleController extends Controller
{
    private const SESSION = 'google_oauth';

    public function __construct(private GoogleOAuth $google) {}

    public function redirect(Request $request)
    {
        abort_unless(GoogleOAuth::configured(), 404);
        $audience = $this->audience($request);
        $state = Str::random(40);
        $request->session()->put(self::SESSION, ['state' => $state, 'audience' => $audience]);

        return redirect()->away($this->google->authorizeUrl($this->callbackUrl($audience), $state));
    }

    public function callback(Request $request)
    {
        abort_unless(GoogleOAuth::configured(), 404);
        $audience = $this->audience($request);
        $saved = $request->session()->pull(self::SESSION);
        $login = $audience === 'customer' ? 'platform.login' : 'login';

        // Retour sans demande de notre part, ou demande d'un autre espace : refusé (attaque par rejeu ou CSRF).
        if (! is_array($saved) || ($saved['audience'] ?? null) !== $audience || ! is_string($request->query('state'))
            || ! hash_equals((string) ($saved['state'] ?? ''), $request->query('state'))) {
            return redirect()->route($login)->withErrors(['email' => __('La connexion avec Google a expiré. Réessayez.')]);
        }
        if ($request->filled('error') || ! is_string($request->query('code'))) {
            return redirect()->route($login)->withErrors(['email' => __('Connexion avec Google annulée.')]);
        }

        try {
            $profile = $this->google->user($request->query('code'), $this->callbackUrl($audience));

            return $audience === 'customer' ? $this->customer($request, $profile) : $this->admin($request, $profile);
        } catch (RuntimeException|PlatformException $e) {
            return redirect()->route($login)->withErrors(['email' => $e->getMessage()]);
        }
    }

    /** Client de la plateforme : connexion, liaison d'un compte existant ou inscription. */
    private function customer(Request $request, array $profile)
    {
        $customer = Customer::query()->where('google_id', $profile['id'])->first()
            ?? Customer::query()->where('email', $profile['email'])->first();
        $created = false;

        if (! $customer) {
            // Mot de passe aléatoire, jamais communiqué : « Mot de passe oublié » permet d'en choisir un.
            $customer = app(PlatformAccounts::class)->register(
                Str::limit($profile['name'], 120, ''), $profile['email'], Str::password(32), null, null,
                in_array($profile['locale'], array_keys((array) config('geniuspress.ui_locales')), true) ? $profile['locale'] : app()->getLocale(),
            );
            $created = true;
            app(\App\Platform\Referrals\Referrals::class)->attach($customer, $request->cookie(\App\Platform\Referrals\Referrals::COOKIE));
        }
        if (! $customer->isActive()) {
            throw new PlatformException(__('Ce compte est suspendu. Contactez-nous.'));
        }
        $customer->forceFill(['google_id' => $profile['id'], 'email_verified_at' => $customer->email_verified_at ?? now()])->save();

        Auth::guard('customer')->login($customer, true);
        $request->session()->regenerate();

        return redirect()->intended(route('platform.customer.dashboard'))
            ->with('status', $created ? __('Bienvenue ! Votre compte GeniusPress est créé.') : null);
    }

    /** Administration d'un site : seulement un compte déjà créé par un administrateur. */
    private function admin(Request $request, array $profile)
    {
        $user = User::query()->where('google_id', $profile['id'])->first()
            ?? User::query()->where('email', $profile['email'])->first();
        if (! $user) {
            throw new RuntimeException(__('Aucun compte de cette administration n’utilise cette adresse Google. Demandez à un administrateur de vous créer un compte.'));
        }
        $user->forceFill(['google_id' => $profile['id'], 'email_verified_at' => $user->email_verified_at ?? now()])->save();

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    private function audience(Request $request): string
    {
        $audience = $request->route('audience');
        abort_unless(in_array($audience, ['admin', 'customer'], true), 404);

        return $audience;
    }

    private function callbackUrl(string $audience): string
    {
        return route($audience === 'customer' ? 'platform.google.callback' : 'google.callback');
    }
}
