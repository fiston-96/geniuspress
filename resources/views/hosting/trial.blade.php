@extends('hosting.layout')
@section('title', __('Essayer GeniusPress'))
@if (in_array($status, ['provisioning', 'securing'], true))
    @push('head')
        {{-- Préparation (ou certificat HTTPS) en cours : la page se recharge quand le site est prêt. --}}
        <script>
            (function poll() {
                setTimeout(function () {
                    fetch(@json(route('hosting.trial.status')), { headers: { Accept: 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (d) { d.status === @json($status) ? poll() : location.reload(); })
                        .catch(poll);
                }, 2000);
            })();
        </script>
    @endpush
@endif
@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Essayer GeniusPress') }}</h1>

    @if (! $site)
        <p class="mt-2 text-sm text-slate-600">{{ __('Obtenez en un clic un site GeniusPress complet, hébergé chez nous : builder, modèles, blog, thème et assistant IA. Rien à installer.') }}</p>

        <ul class="mt-5 grid gap-2 text-sm text-slate-700 sm:grid-cols-2">
            <li class="rounded-lg bg-slate-50 px-3 py-2">{{ __(':n jours d’essai gratuit', ['n' => $plan->days]) }}</li>
            @if ($plan->limits['pages'] ?? null)<li class="rounded-lg bg-slate-50 px-3 py-2">{{ __('Jusqu’à :n pages', ['n' => $plan->limits['pages']]) }}</li>@endif
            @if ($plan->limits['media_mb'] ?? null)<li class="rounded-lg bg-slate-50 px-3 py-2">{{ __(':n Mo de médias', ['n' => $plan->limits['media_mb']]) }}</li>@endif
            @if ($plan->limits['ai_requests'] ?? null)<li class="rounded-lg bg-slate-50 px-3 py-2">{{ __(':n demandes à l’assistant IA', ['n' => $plan->limits['ai_requests']]) }}</li>@endif
        </ul>

        @if ($failed)
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ __('La création de votre site a échoué. Réessayez dans quelques minutes ; si le problème persiste, contactez-nous.') }}</div>
        @endif

        @if ($refusal)
            <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $refusal }}</div>
        @else
            <form method="POST" action="{{ route('hosting.trial.store') }}" class="mt-6 space-y-4">
                @csrf
                <x-field name="name" :label="__('Nom de votre site')" :value="auth()->user()->name" required maxlength="120" />
                <div>
                    <label for="slug" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Adresse') }}</label>
                    <div class="flex items-stretch overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20">
                        <input id="slug" name="slug" value="{{ old('slug', $suggested) }}" required maxlength="40" pattern="[a-z0-9][a-z0-9-]{1,38}[a-z0-9]" autocomplete="off"
                            class="min-w-0 flex-1 px-3 py-2 text-sm outline-none">
                        <span class="flex items-center bg-slate-50 px-3 text-sm text-slate-500">.{{ $domain }}</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Lettres minuscules sans accents, chiffres et tirets.') }}</p>
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Créer mon site d’essai') }}</button>
            </form>
        @endif
    @else
        <p class="mt-2 text-sm text-slate-600">{{ $site->name }} · <span class="font-mono text-slate-500">{{ $site->host ?? '—' }}</span></p>

        @if (in_array($status, ['provisioning', 'securing'], true))
            <div class="mt-6 flex items-center gap-3 rounded-lg bg-brand-50 px-4 py-3 text-sm text-brand-800">
                <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                {{ $status === 'securing' ? __('Votre site est prêt ; sécurisation de son adresse (certificat HTTPS), environ une minute…') : __('Votre site est en préparation…') }}
            </div>
        @elseif ($status === 'active')
            <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                @if ($site->isTrial() && $site->expires_at)
                    {{ trans_choice('Votre essai est en cours : :count jour restant (jusqu’au :date).|Votre essai est en cours : :count jours restants (jusqu’au :date).', $site->daysLeft(), ['date' => $site->expires_at->translatedFormat('j F Y')]) }}
                @else
                    {{ __('Votre site est en ligne.') }}
                @endif
            </div>
        @elseif ($status === 'expired')
            <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                {{ __('Votre essai est terminé. Votre site n’est plus visible, mais son administration reste ouverte en lecture seule : vous pouvez exporter votre site ou choisir une offre pour le remettre en ligne.') }}
            </div>
        @elseif ($status === 'suspended')
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ __('Ce site a été suspendu. Contactez-nous pour en savoir plus.') }}
                @if ($site->suspend_reason)<br>{{ $site->suspend_reason }}@endif
            </div>
        @elseif ($status === 'deleted')
            <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                {{ __('Votre essai est terminé et ses données ont été supprimées.') }}
            </div>
        @endif

        <div class="mt-6 flex flex-wrap gap-3">
            @if (in_array($status, ['active', 'expired'], true))
                <a href="{{ route('hosting.trial.open') }}" target="_blank" rel="noopener" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Ouvrir mon site ↗') }}</a>
            @endif
            @if ($status === 'active')
                <a href="{{ $site->url() }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">{{ __('Voir le site public') }}</a>
            @endif
            @if ($upgradeUrl && $site->isTrial() && in_array($status, ['active', 'expired'], true))
                <a href="{{ $upgradeUrl }}" class="rounded-lg border border-brand-200 px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ __('Choisir une offre') }}</a>
            @endif
        </div>
    @endif
@endsection
