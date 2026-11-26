{{--
    Logo GeniusPress : symbole « pages en construction » et nom.
    Paramètres : size (px), wordmark (afficher le nom), dark (sur fond sombre).
    En dessous de 25 px, le symbole simplifié (un seul bloc blanc) est utilisé.
--}}
@props(['size' => 36, 'wordmark' => true, 'dark' => false])
@php $small = $size <= 24; @endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    <svg viewBox="0 0 64 64" width="{{ $size }}" height="{{ $size }}" class="shrink-0" @if ($wordmark) aria-hidden="true" @else role="img" aria-label="GeniusPress" @endif>
        {{-- Or clair, nuit indigo (crème sur fond sombre, pour rester visible), terracotta. --}}
        <rect x="5" y="25" width="25" height="33" rx="5" fill="#e3c48f"/>
        <rect x="17" y="16" width="25" height="34" rx="5" fill="{{ $dark ? '#fbf7f0' : '#1e1b4b' }}"/>
        <rect x="30" y="6" width="28" height="37" rx="5" fill="#aa4d26"/>
        @if ($small)
            <rect x="35" y="12" width="18" height="8" rx="3" fill="#ffffff"/>
        @else
            <rect x="35" y="11" width="18" height="6" rx="2" fill="#ffffff"/>
            <rect x="35" y="20" width="8" height="10" rx="2" fill="#ffffff"/>
            <rect x="45" y="20" width="8" height="10" rx="2" fill="#ffffff" fill-opacity="0.6"/>
        @endif
    </svg>
    @if ($wordmark)
        <span class="font-bold whitespace-nowrap" style="font-family: Inter, ui-sans-serif, system-ui, sans-serif; font-size: {{ round($size * 0.58) }}px; letter-spacing: -0.02em; line-height: 1"><span style="color: {{ $dark ? '#ffffff' : '#1e1b4b' }}">Genius</span><span style="color: {{ $dark ? '#e3c48f' : '#aa4d26' }}">Press</span></span>
    @endif
</span>
