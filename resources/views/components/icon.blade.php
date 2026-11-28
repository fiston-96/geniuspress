{{--
    Icône d'interface (Lucide) : même jeu que le builder (App\View\UiIcons). Décorative :
    le texte ou l'infobulle du bouton qui la contient porte le sens.
--}}
@props(['name', 'stroke' => 1.8])
@if ($body = \App\View\UiIcons::body($name))
    <svg {{ $attributes->merge(['class' => 'h-5 w-5 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $body !!}</svg>
@endif
