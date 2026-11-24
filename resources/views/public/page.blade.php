@php
    $font = $settings['font_family'] ?? 'Inter';
    $title = $page->meta_title ?: $page->title.' · '.$settings['site_name'];
    $plugins = app(\App\Plugins\PluginManager::class);
    $nav = \App\Site\Navigation::forPage($page);
@endphp
<!DOCTYPE html>
<html lang="{{ $page->locale }}" @if($page->locale === 'ar') dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ $settings['favicon_url'] ?? asset('favicon.svg') }}">
    @if ($page->meta_description)<meta name="description" content="{{ $page->meta_description }}">@endif
    @if (! empty($preview))<meta name="robots" content="noindex">@endif
    <meta property="og:title" content="{{ $title }}">
    @if ($page->meta_description)<meta property="og:description" content="{{ $page->meta_description }}">@endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family={{ urlencode(strtolower($font)) }}:400,500,600,700,800&display=swap">
    @vite(['resources/css/site.css'])
    <style>
        :root {
            --gp-primary: {{ $settings['primary_color'] }};
            --gp-secondary: {{ $settings['secondary_color'] }};
            --gp-accent: {{ $settings['accent_color'] }};
            --gp-font: '{{ $font }}', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }
        {!! $rendered['css'] !!}
    </style>
    @foreach ($plugins->assets('site_css') as $href)<link rel="stylesheet" href="{{ $href }}">@endforeach
    {{-- Les plugins peuvent ajouter du HTML dans <head> (balises meta, statistiques…). --}}
    {!! \App\Plugins\Hooks::filter('page.head', '', $page) !!}
</head>
<body>
    @if (! empty($preview) && empty($embedded))
        <div class="gp-preview-bar">{{ __('Aperçu — cette page n’est pas publiée') }}</div>
    @endif
    @if ($nav['header']['enabled'])
        @include('public.partials.header')
    @endif
    <main id="contenu">{!! $rendered['html'] !!}</main>
    @if ($nav['footer']['enabled'])
        @include('public.partials.footer')
    @endif
    @foreach ($plugins->assets('site_js') as $src)<script src="{{ $src }}" defer></script>@endforeach
    {!! \App\Plugins\Hooks::filter('page.body_end', '', $page) !!}
</body>
</html>
