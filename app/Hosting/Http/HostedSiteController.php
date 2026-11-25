<?php

namespace App\Hosting\Http;

use App\Hosting\CurrentSite;
use App\Hosting\LoginLink;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/** Connexion en un clic à l'administration d'un site hébergé. */
class HostedSiteController extends Controller
{
    /** Lien signé envoyé par la plateforme : connecte le propriétaire du site. */
    public function login(Request $request, CurrentSite $site)
    {
        $manifest = $site->manifest();
        abort_unless($manifest, 404);

        $nonce = $request->query('nonce');
        $valid = LoginLink::verify($manifest, $request->query('expires'), $nonce, $request->query('signature'))
            // Un lien ne sert qu'une fois.
            && Cache::add('hosting-login:'.$nonce, true, LoginLink::TTL + 60);
        if (! $valid) {
            return redirect()->route('login')->withErrors(['email' => __('Ce lien de connexion a expiré. Retournez sur la plateforme pour en obtenir un nouveau.')]);
        }

        $owner = User::query()->where('role', User::ROLE_ADMIN)->orderBy('id')->first();
        abort_unless($owner, 404);
        Auth::login($owner);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }
}
