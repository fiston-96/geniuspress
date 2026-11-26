<?php

namespace App\Platform\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Platform\Ai\Pricing;
use App\Platform\Models\AiRate;
use App\Platform\Models\Customer;
use App\Platform\Models\TemplatePrice;
use App\Platform\PlatformAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** API de la plateforme pour les sites : compte de la licence, modèles payants. */
class AccountController extends Controller
{
    public function show(Request $request, PlatformAccounts $accounts, Pricing $pricing)
    {
        /** @var Customer $customer */
        $customer = $request->attributes->get('platform_customer');
        $subscription = $accounts->activeSubscription($customer);

        return response()->json([
            'customer' => ['name' => $customer->name],
            'license' => ['name' => $request->attributes->get('platform_license')->name],
            'plan' => $subscription ? [
                'slug' => $subscription->plan->slug,
                'name' => $subscription->plan->label(),
                'status' => $subscription->status,
                'ends_at' => $subscription->endsAt()?->toIso8601String(),
            ] : null,
            'ai' => [
                'balance' => $customer->ai_balance,
                // Prix facturés (coût réel × multiplicateur), en µ$ par million de jetons.
                'models' => AiRate::query()->where('active', true)->orderBy('label')->get()->map(fn (AiRate $r) => [
                    'model' => $r->model,
                    'label' => $r->label,
                    'input_per_million' => $pricing->billedPerMillion((string) $r->input_price),
                    'output_per_million' => $pricing->billedPerMillion((string) $r->output_price),
                ])->all(),
            ],
            'templates' => $customer->templateIds(),
            'account_url' => route('platform.customer.dashboard'),
        ]);
    }

    /** Prix publics des modèles payants (sans clé). */
    public function prices()
    {
        return response()->json([
            'templates' => TemplatePrice::query()->where('active', true)->get()
                ->mapWithKeys(fn (TemplatePrice $p) => [$p->template_id => ['price' => $p->price, 'currency' => $p->currency]]),
            'buy_url' => route('platform.customer.templates'),
        ]);
    }

    /** Fichier d'un modèle payant, pour le client qui l'a acheté. */
    public function template(Request $request, string $template)
    {
        /** @var Customer $customer */
        $customer = $request->attributes->get('platform_customer');
        abort_unless(preg_match('/^[a-z0-9-]{2,60}$/', $template), 404);
        $paid = TemplatePrice::query()->where('template_id', $template)->exists();
        if ($paid && ! in_array($template, $customer->templateIds(), true)) {
            return response()->json(['error' => 'not_purchased', 'message' => __('Ce modèle est payant : achetez-le dans votre espace client GeniusPress.')], 403);
        }
        $file = rtrim((string) config('geniuspress.catalog.path'), '/').'/'.$template.'.zip';
        abort_unless(is_file($file), 404);

        return response()->download($file, $template.'.zip', ['Content-Type' => 'application/zip']);
    }
}
