@php
    // Une page seule (aperçu du builder, démonstration d'un modèle) n'a pas encore de contexte.
    $site ??= \App\Site\SiteContext::forPage($page, ! empty($preview));
    $page ??= $site->page;
    $font = $settings['font_family'] ?? 'Inter';
    $title = $site->metaTitle($settings['site_name']);
    $description = $site->metaDescription();
    $plugins = app(\App\Plugins\PluginManager::class);
    $nav = \App\Site\Navigation::forContext($site);
    // Gabarits du thème (en-tête, pied de page) : leur CSS et leurs polices s'ajoutent à ceux du contenu.
    $themeHeader ??= null;
    $themeFooter ??= null;
    $fonts = array_values(array_unique(array_merge($rendered['fonts'] ?? [], $themeHeader['fonts'] ?? [], $themeFooter['fonts'] ?? [])));
@endphp
<!DOCTYPE html>
<html lang="{{ $site->locale }}" @if($site->locale === 'ar') dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ $settings['favicon_url'] ?? asset('favicon.svg') }}">
    @if ($description)<meta name="description" content="{{ $description }}">@endif
    @if (! empty($preview) || $site->kind === \App\Site\SiteContext::SEARCH || $site->kind === \App\Site\SiteContext::NOT_FOUND)<meta name="robots" content="noindex">@endif
    @foreach ($nav['languages'] as $lang)@if ($lang['translated'])<link rel="alternate" hreflang="{{ $lang['code'] }}" href="{{ $lang['url'] }}">@endif @endforeach
    <meta property="og:title" content="{{ $title }}">
    @if ($description)<meta property="og:description" content="{{ $description }}">@endif
    @if ($site->post?->featuredMedia)<meta property="og:image" content="{{ url($site->post->featuredMedia->url) }}">@endif
    @if ($site->kind === \App\Site\SiteContext::POST)<meta property="og:type" content="article">@endif
    <link rel="alternate" type="application/rss+xml" title="{{ $settings['site_name'] }}" href="{{ \App\Site\Permalinks::feed($site->locale) }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="{{ \App\Builder\DesignTokens::fontsUrl($settings, $fonts) }}">
    {{-- Animations d'apparition : éléments cachés jusqu'à leur arrivée à l'écran, seulement si le
         script du site tourne (sinon, au bout de 3 s, tout redevient visible). --}}
    <script>document.documentElement.classList.add('gp-js');setTimeout(function(){if(!window.gpAnimReady)document.documentElement.classList.remove('gp-js')},3000);</script>
    @vite(['resources/css/site.css', 'resources/js/site.js'])
    <style>
        {!! \App\Builder\DesignTokens::css($settings) !!}
        {!! $themeHeader['css'] ?? '' !!}
        {!! $rendered['css'] !!}
        {!! $themeFooter['css'] ?? '' !!}
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
    @foreach ($plugins->assets('site_js') as $src)<script src="{{ $src }}" defer></script>@endforeach
    {!! \App\Plugins\Hooks::filter('page.body_end', '', $page, $site) !!}
</body>
</html>
