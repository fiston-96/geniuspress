{{-- Bandeau d'un site hébergé dans l'administration : jours restants, quotas, offre. --}}
@php
    $hostedSite = app(\App\Hosting\CurrentSite::class);
    $hostedManifest = $hostedSite->manifest();
@endphp
@if ($hostedManifest && $hostedManifest->isTrial())
    @php
        $usage = app(\App\Hosting\Quotas::class)->summary();
        $expired = $hostedManifest->status() === \App\Hosting\Manifest::STATUS_EXPIRED;
        $labels = ['pages' => __('Pages'), 'posts' => __('Articles'), 'media_mb' => __('Médias (Mo)'), 'ai_requests' => __('Demandes IA')];
    @endphp
    <div @class(['mb-6 flex flex-wrap items-center justify-between gap-3 rounded-lg border px-4 py-3 text-sm', 'border-amber-200 bg-amber-50 text-amber-900' => $expired, 'border-indigo-200 bg-indigo-50 text-indigo-900' => ! $expired])>
        <p class="font-medium">
            @if ($expired)
                {{ __('Essai terminé : administration en lecture seule. Exportez votre site ou choisissez une offre.') }}
            @else
                {{ trans_choice('Essai gratuit : :count jour restant.|Essai gratuit : :count jours restants.', $hostedManifest->daysLeft() ?? 0) }}
            @endif
        </p>
        <p class="flex flex-wrap gap-x-4 gap-y-1 text-xs opacity-80">
            @foreach ($usage as $key => [$used, $limit])
                @if ($limit !== null)<span>{{ $labels[$key] }} : {{ $used }} / {{ $limit }}</span>@endif
            @endforeach
        </p>
        @if ($hostedManifest->upgradeUrl())
            <a href="{{ $hostedManifest->upgradeUrl() }}" target="_blank" rel="noopener" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('Choisir une offre') }}</a>
        @endif
    </div>
@endif
