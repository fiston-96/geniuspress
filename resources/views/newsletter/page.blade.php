{{-- Page simple de la newsletter (confirmation, désinscription), aux couleurs du site. --}}
@php($settings = \App\Models\Setting::publicValues())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} · {{ $settings['site_name'] ?? 'GeniusPress' }}</title>
    <style>
        {!! \App\Builder\DesignTokens::css($settings) !!}
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; box-sizing: border-box;
            font: 16px/1.6 system-ui, -apple-system, 'Segoe UI', sans-serif; color: var(--gp-color-texte, #1c1917); background: var(--gp-color-clair, #f8fafc); }
        main { max-width: 460px; width: 100%; background: #fff; border: 1px solid var(--gp-line, #e2e8f0); border-radius: 16px; padding: 32px; text-align: center; }
        h1 { margin: 0 0 8px; font-size: 1.4rem; }
        p { margin: 0 0 20px; color: var(--gp-color-doux, #475569); }
        .btn { display: inline-block; border: 0; border-radius: 10px; padding: 11px 20px; font: inherit; font-weight: 600; cursor: pointer; text-decoration: none; background: var(--gp-primary); color: #fff; }
        .link { display: block; margin-top: 16px; color: var(--gp-primary); font-size: .95rem; }
    </style>
</head>
<body>
<main>
    <p style="margin-bottom: 18px; font-weight: 700; color: var(--gp-primary)">{{ $settings['site_name'] ?? 'GeniusPress' }}</p>
    <h1>{{ $title }}</h1>
    <p>{{ $message }}</p>
    @if ($unsubscribeToken)
        <form method="POST" action="{{ route('newsletter.unsubscribe', $unsubscribeToken) }}">@csrf
            <button class="btn">{{ __('Me désinscrire') }}</button>
        </form>
    @endif
    <a class="link" href="{{ url('/') }}">{{ __('Retour au site') }}</a>
</main>
</body>
</html>
