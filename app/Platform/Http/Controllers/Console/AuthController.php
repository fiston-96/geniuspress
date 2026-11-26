<?php

namespace App\Platform\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Connexion à la console d'exploitation (garde « operator »). Pas d'inscription : les comptes se créent en ligne de commande. */
class AuthController extends Controller
{
    public function show()
    {
        return view('platform.console.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        if (! Auth::guard('operator')->attempt(['email' => Str::lower($data['email']), 'password' => $data['password'], 'status' => 'active'], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => __('Adresse e-mail ou mot de passe incorrect.')]);
        }
        $request->session()->regenerate();
        Auth::guard('operator')->user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('console.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('operator')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('console.login');
    }
}
