<?php

namespace App\Platform\Http\Controllers\Customer;

use App\Platform\LicenseKeys;
use App\Platform\Models\License;
use Illuminate\Http\Request;

/** Clés de licence des sites du client (IA par la plateforme, modèles achetés). */
class LicenseController extends CustomerController
{
    public const MAX_ACTIVE = 20;

    public function store(Request $request, LicenseKeys $keys)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'site_url' => ['nullable', 'url:http,https', 'max:255'],
        ]);
        $customer = $this->customer($request);
        if ($customer->licenses()->where('status', 'active')->count() >= self::MAX_ACTIVE) {
            return back()->withErrors(['name' => __('Vous avez atteint le nombre maximal de clés actives. Révoquez une clé inutilisée.')]);
        }
        ['key' => $key] = $keys->issue($customer, $data['name'], $data['site_url'] ?? null);

        // Affichée une seule fois, sur la page suivante.
        return redirect()->route('platform.customer.dashboard')->with('license_key', $key)
            ->with('status', __('Clé de licence créée. Copiez-la maintenant : elle ne sera plus affichée.'));
    }

    public function destroy(Request $request, License $license, LicenseKeys $keys)
    {
        $this->own($request, $license);
        $keys->revoke($license);

        return back()->with('status', __('Clé de licence révoquée.'));
    }
}
