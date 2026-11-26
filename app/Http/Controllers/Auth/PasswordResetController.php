<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\PasswordChanged;
use App\Platform\Models\Customer;
use App\Platform\PlatformNotifier;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * Mot de passe oublié, pour les deux publics :
 * - « admin » : utilisateurs de l'administration d'un site (/admin/…, courtier users) ;
 * - « customer » : clients de la plateforme (/compte/…, courtier customers).
 *
 * La réponse à une demande de lien est toujours la même, que l'adresse existe ou non,
 * pour ne pas révéler qui a un compte.
 */
class PasswordResetController extends Controller
{
    private const AUDIENCES = [
        'admin' => [
            'broker' => 'users',
            'login' => 'login',
            'email' => 'password.email',
            'update' => 'password.update',
        ],
        'customer' => [
            'broker' => 'customers',
            'login' => 'platform.login',
            'email' => 'platform.password.email',
            'update' => 'platform.password.update',
        ],
    ];

    public function request(Request $request)
    {
        return view('auth.forgot-password', ['routes' => $this->audience($request)]);
    }

    public function email(Request $request)
    {
        $audience = $this->audience($request);
        $data = $request->validate(['email' => ['required', 'email', 'max:190']]);

        $status = Password::broker($audience['broker'])->sendResetLink(['email' => Str::lower($data['email'])]);
        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages(['email' => __('Un lien vient déjà d’être envoyé. Patientez une minute avant d’en demander un autre.')]);
        }

        return back()->with('status', __('Si un compte existe pour cette adresse, un lien pour choisir un nouveau mot de passe vient d’y être envoyé. Pensez à regarder dans les courriers indésirables.'));
    }

    public function edit(Request $request, string $token)
    {
        return view('auth.reset-password', ['routes' => $this->audience($request), 'token' => $token, 'email' => (string) $request->query('email')]);
    }

    public function update(Request $request)
    {
        $audience = $this->audience($request);
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()],
        ], [], ['password' => __('nouveau mot de passe')]);

        $status = Password::broker($audience['broker'])->reset(
            ['email' => Str::lower($data['email']), 'token' => $data['token'], 'password' => $data['password'], 'password_confirmation' => $request->input('password_confirmation')],
            function ($account, string $password) {
                // Le modèle hache le mot de passe (cast « hashed ») ; les sessions « rester connecté » sont coupées.
                $account->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($account));
                PlatformNotifier::send($account, new PasswordChanged(route($account instanceof Customer ? 'platform.password.request' : 'password.request')));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __('Ce lien n’est plus valable. Demandez-en un nouveau.')]);
        }

        return redirect()->route($audience['login'])->with('status', __('Votre mot de passe a été changé. Vous pouvez vous connecter.'));
    }

    /** Public de la route (défini par ->defaults('audience', …) dans les routes). */
    private function audience(Request $request): array
    {
        return self::AUDIENCES[$request->route('audience')] ?? abort(404);
    }
}
