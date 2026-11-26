<?php

namespace App\Platform\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Platform\PlatformAccounts;
use App\Platform\PlatformException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/** Inscription et connexion des clients de la plateforme. */
class AuthController extends Controller
{
    public function showRegister()
    {
        return view('platform.customer.register');
    }

    public function register(Request $request, PlatformAccounts $accounts)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:platform_customers,email'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9 .-]{6,20}$/'],
            'country' => ['nullable', 'string', 'size:2', 'alpha'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        try {
            $customer = $accounts->register($data['name'], $data['email'], $data['password'], $data['phone'] ?? null, $data['country'] ?? null, app()->getLocale());
        } catch (PlatformException $e) {
            return back()->withErrors(['email' => $e->getMessage()])->withInput();
        }
        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('platform.customer.dashboard')->with('status', __('Bienvenue :name ! Votre compte est créé.', ['name' => $customer->name]));
    }

    public function showLogin()
    {
        return view('platform.customer.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('customer')->attempt(['email' => Str::lower($data['email']), 'password' => $data['password'], 'status' => 'active'], $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('Adresse e-mail ou mot de passe incorrect.')])->onlyInput('email');
        }
        $request->session()->regenerate();

        // Adresse demandée avant la connexion, seulement si elle est dans l'espace client.
        $intended = (string) $request->session()->pull('url.intended', '');
        $path = (string) parse_url($intended, PHP_URL_PATH);

        return redirect()->to($path === '/compte' || str_starts_with($path, '/compte/') ? $intended : route('platform.customer.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }
}
