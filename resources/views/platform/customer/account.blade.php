@extends('platform.layouts.customer')
@section('title', __('Mon compte'))
@section('content')
@php use App\Platform\Labels; @endphp
@php($countryName = \App\Platform\Countries::name($customer->country))
    <div class="flex flex-wrap items-center gap-4">
        <span class="grid h-14 w-14 place-items-center rounded-2xl bg-night text-lg font-bold text-gold-light">{{ collect(preg_split('/\s+/', trim($customer->name)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') }}</span>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-night">{{ __('Mon compte') }}</h1>
            <p class="text-sm text-slate-500">{{ $customer->name }} · {{ $customer->email }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Pays du client : prix affichés dans sa devise, pays et devise présélectionnés au paiement. --}}
            <section class="rounded-2xl border border-line bg-white p-6">
                <h2 class="flex items-center gap-2 font-semibold"><x-icon name="globe" class="h-5 w-5 text-brand-600" />{{ __('Pays, devise et téléphone') }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $countryName && ($currency = \App\Platform\Countries::currency($customer->country)) ? __('Les prix sont aussi affichés en :currency, et le paiement s’adapte à votre pays.', ['currency' => $currency]) : __('GeniusPress affiche alors les prix dans votre devise et vous propose les moyens de paiement de votre pays.') }}</p>
                @if ($countryName)<p class="mt-2 text-sm font-medium">{{ __('Votre pays : :country', ['country' => $countryName]) }}</p>@else<p class="mt-2 text-sm font-medium text-amber-700">{{ __('Indiquez votre pays') }}</p>@endif
                <form method="POST" action="{{ route('platform.customer.country') }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">@csrf @method('PUT')
                    <label class="block text-sm"><span class="mb-1 block font-medium text-slate-700">{{ __('Pays') }}</span><x-platform.country-select :value="$customer->country" class="w-full" /></label>
                    <label class="block text-sm"><span class="mb-1 block font-medium text-slate-700">{{ __('Téléphone') }}</span>
                        <input name="phone" type="tel" value="{{ old('phone', $customer->phone) }}" placeholder="{{ __('+ indicatif du pays') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></label>
                    <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Enregistrer') }}</button>
                </form>
            </section>

            {{-- SMS importants (seulement si la plateforme envoie des SMS). --}}
            @if (\App\Platform\Messaging\Sms::enabled())
                <section class="rounded-2xl border border-line bg-white p-6">
                    <h2 class="flex items-center gap-2 font-semibold"><x-icon name="smartphone" class="h-5 w-5 text-brand-600" />{{ __('SMS') }}</h2>
                    <form method="POST" action="{{ route('platform.customer.sms') }}" class="mt-3 flex flex-wrap items-center gap-3 text-sm">@csrf @method('PUT')
                        <label class="flex flex-1 items-start gap-2">
                            <input type="hidden" name="sms_opt_in" value="0"><input type="checkbox" name="sms_opt_in" value="1" @checked($customer->sms_opt_in) class="mt-0.5" onchange="this.form.submit()">
                            <span>{{ __('Recevoir par SMS les messages importants (paiement reçu, service prêt, fin d’essai).') }}
                                @unless (\App\Platform\Messaging\Sms::normalize($customer->phone))<span class="block text-xs text-amber-700">{{ __('Ajoutez votre numéro avec l’indicatif du pays pour les recevoir.') }}</span>@endunless</span>
                        </label>
                        <noscript><button class="rounded-lg border border-slate-300 px-3 py-1.5">{{ __('Enregistrer') }}</button></noscript>
                    </form>
                </section>
            @endif

            {{-- Clés de licence des sites GeniusPress. --}}
            <section class="rounded-2xl border border-line bg-white p-6">
                <h2 class="flex items-center gap-2 font-semibold"><x-icon name="key-round" class="h-5 w-5 text-brand-600" />{{ __('Clés de licence') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Une clé par site GeniusPress (installé chez vous, sur votre serveur ou hébergé chez nous) : l’assistant IA utilise vos crédits et vos modèles achetés s’installent directement.') }}</p>
                <div class="mt-4 divide-y divide-line">
                    @forelse ($licenses as $license)
                        <div class="flex flex-wrap items-center justify-between gap-2 py-2.5 text-sm">
                            <div>
                                <span class="font-medium">{{ $license->name }}</span>
                                <code class="ml-2 text-xs text-slate-500">{{ $license->displayKey() }}</code>
                                @if (! $license->isActive())<span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ Labels::status($license->status) }}</span>@endif
                                @if ($license->last_used_at)<span class="ml-2 text-xs text-slate-500">{{ __('utilisée :when', ['when' => $license->last_used_at->diffForHumans()]) }}</span>@endif
                            </div>
                            @if ($license->isActive())
                                <form method="POST" action="{{ route('platform.customer.licenses.destroy', $license) }}" onsubmit="return confirm(@js(__('Révoquer cette clé ? Le site qui l’utilise perdra l’accès à l’IA.')))">@csrf @method('DELETE')
                                    <button class="text-xs text-slate-500 hover:text-red-600">{{ __('Révoquer') }}</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="py-2 text-sm text-slate-500">{{ __('Aucune clé pour l’instant.') }}</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('platform.customer.licenses.store') }}" class="mt-4 flex flex-wrap gap-2">
                    @csrf
                    <input name="name" required maxlength="120" placeholder="{{ __('Nom du site') }}" aria-label="{{ __('Nom du site') }}" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input name="site_url" type="url" maxlength="255" placeholder="https://…" aria-label="{{ __('Adresse du site') }}" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Créer une clé') }}</button>
                </form>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="rounded-2xl border border-line bg-white p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Sécurité') }}</h2>
                <p class="mt-3 text-sm text-slate-600">{{ __('Pour changer votre mot de passe, utilisez « Mot de passe oublié » sur la page de connexion : un lien vous est envoyé par e-mail.') }}</p>
            </section>
            <section class="rounded-2xl bg-night p-5 text-white">
                <x-icon name="gift" class="h-6 w-6 text-gold-light" />
                <h2 class="mt-3 font-semibold">{{ __('Recommander GeniusPress') }}</h2>
                <p class="mt-1 text-sm text-slate-300">{{ __('Partagez votre lien : vous recevez une commission sur la première commande des personnes que vous amenez.') }}</p>
                <a href="{{ route('platform.customer.referrals') }}" class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-2 text-sm font-semibold ring-1 ring-white/20 hover:bg-white/15">{{ __('Mon lien d’affiliation') }}<x-icon name="arrow-right" class="h-4 w-4" /></a>
            </section>
        </aside>
    </div>
@endsection
