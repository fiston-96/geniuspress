{{-- Builder (pages, articles, gabarits du thème) : $boot vient de App\Builder\BuilderBoot. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ __('Builder') }} · {{ $boot['page']['title'] }}</title>
    @include('partials.brand-head')
    @vite(['resources/css/app.css', 'resources/js/builder/main.jsx'])
    {{-- Scripts des plugins actifs : ils déclarent leurs widgets via window.GeniusPress. --}}
    @foreach (app(\App\Plugins\PluginManager::class)->assets('builder') as $src)
        <script type="module" src="{{ $src }}"></script>
    @endforeach
</head>
<body class="overflow-hidden antialiased">
    <div id="builder"></div>
    <script type="application/json" id="gp-boot">{!! json_encode($boot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
</body>
</html>
