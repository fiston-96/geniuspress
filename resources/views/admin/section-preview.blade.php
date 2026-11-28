{{-- Aperçu d'une section de la bibliothèque (vignette du builder) : styles du site, sans script. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="{{ $fontsUrl }}">
    @vite(['resources/css/site.css'])
    <style>
        {!! $designCss !!}
        {!! $css !!}
        body { margin: 0; overflow: hidden; pointer-events: none; }
    </style>
</head>
<body><main id="contenu">{!! $html !!}</main></body>
</html>
