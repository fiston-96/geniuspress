@php
    // Une page seule (aperçu du builder, démonstration d'un modèle) n'a pas encore de contexte.
    $site ??= \App\Site\SiteContext::forPage($page, ! empty($preview));
    $page ??= $site->page;
    $font = $settings['font_family'] ?? 'Inter';
    $title = $site->metaTitle($settings['site_name']);
    // Sans description propre, l'accueil reprend le slogan du site.
    $description = $site->metaDescription() ?: ($site->isHome() && ! empty($settings['site_tagline']) ? $settings['site_tagline'] : null);
    $plugins = app(\App\Plugins\PluginManager::class);
    $nav = \App\Site\Navigation::forContext($site);
    $seo = \App\Site\Seo::for($site, $settings, $nav['languages'], $title, $description, ! empty($preview));
    // Gabarits du thème (en-tête, pied de page) : leur CSS et leurs polices s'ajoutent à ceux du contenu.
    $themeHeader ??= null;
    $themeFooter ??= null;
    $themePopups ??= [];
    $fonts = array_values(array_unique(array_merge($rendered['fonts'] ?? [], $themeHeader['fonts'] ?? [], $themeFooter['fonts'] ?? [], ...array_column($themePopups, 'fonts'))));
@endphp
<!DOCTYPE html>
<html lang="{{ $site->locale }}" @if($site->locale === 'ar') dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ $settings['favicon_url'] ?? asset('favicon.svg') }}">
    @if ($description)<meta name="description" content="{{ $description }}">@endif
    {{-- Référencement (App\Site\Seo) : indexation, adresse canonique, langues, partage, vérification, données structurées. --}}
    @if ($seo['noindex'])<meta name="robots" content="noindex">@endif
    @if ($seo['canonical'])<link rel="canonical" href="{{ $seo['canonical'] }}">@endif
    @foreach ($nav['languages'] as $lang)@if ($lang['translated'])<link rel="alternate" hreflang="{{ $lang['code'] }}" href="{{ $lang['url'] }}">@endif @endforeach
    @if ($seo['x_default'])<link rel="alternate" hreflang="x-default" href="{{ $seo['x_default'] }}">@endif
    @foreach ($seo['og'] as $property => $content)<meta property="{{ $property }}" content="{{ $content }}">
    @endforeach
    @foreach ($seo['twitter'] as $name => $content)<meta name="{{ $name }}" content="{{ $content }}">
    @endforeach
    @foreach ($seo['verify'] as $name => $content)<meta name="{{ $name }}" content="{{ $content }}">
    @endforeach
    @foreach ($seo['jsonld'] as $data)<script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endforeach
    <link rel="alternate" type="application/rss+xml" title="{{ $settings['site_name'] }}" href="{{ \App\Site\Permalinks::feed($site->locale) }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="{{ \App\Builder\DesignTokens::fontsUrl($settings, $fonts) }}">
    {{-- Animations d'apparition : éléments cachés jusqu'à leur arrivée à l'écran, seulement si le
         script du site tourne (sinon, au bout de 3 s, tout redevient visible). --}}
    {{-- « ?capture=1 » (captures d'écran des modèles, geniuspress:captures) : tout visible d'emblée. --}}
    @unless (request()->boolean('capture'))
    <script>document.documentElement.classList.add('gp-js');setTimeout(function(){if(!window.gpAnimReady)document.documentElement.classList.remove('gp-js')},3000);</script>
    @endunless
    @vite(['resources/css/site.css', 'resources/js/site.js'])
    <style>
        {!! \App\Builder\DesignTokens::css($settings) !!}
        {!! \App\Builder\DesignTokens::pageTransition($settings) !!}
        {!! $themeHeader['css'] ?? '' !!}
        {!! $rendered['css'] !!}
        {!! $themeFooter['css'] ?? '' !!}
        @foreach ($themePopups as $popup){!! $popup['css'] !!}
        @endforeach
    </style>
    @foreach ($plugins->assets('site_css') as $href)<link rel="stylesheet" href="{{ $href }}">@endforeach
    {{-- Les plugins peuvent ajouter du HTML dans <head> (balises meta, statistiques…). --}}
    {!! \App\Plugins\Hooks::filter('page.head', '', $page, $site) !!}
</head>
<body class="gp-view-{{ $site->kind }}">
    @if (! empty($preview) && empty($embedded))
        <div class="gp-preview-bar">{{ __('Aperçu — cette page n’est pas publiée') }}</div>
    @endif
    @if ($themeHeader)
        <header class="gp-theme-header">{!! $themeHeader['html'] !!}</header>
    @elseif ($nav['header']['enabled'])
        @include('public.partials.header')
    @endif
    <main id="contenu">{!! $rendered['html'] !!}</main>
    @if ($themeFooter)
        <footer class="gp-theme-footer">{!! $themeFooter['html'] !!}</footer>
    @elseif ($nav['footer']['enabled'])
        @include('public.partials.footer')
    @endif
    @include('public.partials.popups', ['popups' => $themePopups])
    @foreach ($plugins->assets('site_js') as $src)<script src="{{ $src }}" defer></script>@endforeach
    {!! \App\Plugins\Hooks::filter('page.body_end', '', $page, $site) !!}
</body>
</html>
