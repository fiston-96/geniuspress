<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * « Mon compte » : chaque utilisateur modifie son nom, son e-mail et son mot de passe.
 * Toute modification demande le mot de passe actuel.
 */
class AccountController extends Controller
{
    public function edit(Request $request)
    {
        return view('admin.account', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_password' => ['required', 'current_password'],
        ], [], ['profile_password' => __('mot de passe actuel')]);

        $user->update(['name' => $data['name'], 'email' => Str::lower($data['email'])]);

        return redirect()->route('admin.account.edit')->with('status', __('Votre profil est enregistré.'));
    }

    public function password(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(10)->letters()->numbers()],
        ], [], ['current_password' => __('mot de passe actuel'), 'password' => __('nouveau mot de passe')]);

        $user->password = $data['password'];
        // Les connexions « Se souvenir de moi » ouvertes ailleurs ne sont plus valables.
        $user->setRememberToken(Str::random(60));
        $user->save();
        $request->session()->regenerate();

        return redirect()->route('admin.account.edit')->with('status', __('Votre mot de passe est modifié.'));
    }
}
