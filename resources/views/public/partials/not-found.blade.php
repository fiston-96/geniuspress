{{-- Page introuvable par défaut, quand aucun gabarit « 404 » du thème ne s'applique. --}}
@php($locale = $site->locale)
<section class="gp-not-found">
    <p class="gp-not-found__code">404</p>
    <h1 class="gp-not-found__title">{{ __('Page introuvable', [], $locale) }}</h1>
    <p>{{ __('Cette page n’existe pas ou a été déplacée. Essayez une recherche, ou revenez à l’accueil.', [], $locale) }}</p>
    @include('public.partials.search-form')
    <p><a class="gp-not-found__home" href="{{ \App\Site\Permalinks::home($locale) }}">{{ __('Retour à l’accueil', [], $locale) }}</a></p>
</section>
