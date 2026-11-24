{{-- En-tête commun du site : logo, menu de la langue de la page, langues, bouton d'action. --}}
@php
    // « page » pour la page affichée, « true » pour la rubrique qui la contient (/documentation sur /documentation/installation).
    $current = rtrim('/'.trim(request()->path(), '/'), '/');
    $ariaCurrent = function ($url) use ($current) {
        if (str_starts_with($url, '#') || str_contains($url, '://')) {
            return null;
        }
        $path = rtrim(parse_url($url, PHP_URL_PATH) ?: '/', '/');

        return match (true) {
            $path === $current => 'page',
            $path !== '' && str_starts_with($current, $path.'/') => 'true',
            default => null,
        };
    };
@endphp
<header @class(['gp-site-header', 'gp-site-header--dark' => $nav['header']['style'] === 'dark', 'is-sticky' => $nav['header']['sticky']])>
    <div class="gp-site-header__inner">
        <a href="{{ $nav['home_url'] }}" class="gp-site-brand">
            @if (! empty($settings['logo_url']))<img src="{{ $settings['logo_url'] }}" alt="" height="36">@endif
            <span>{{ $settings['site_name'] }}</span>
        </a>

        @if ($nav['menu'] || $nav['languages'] || ($nav['cta_label'] && $nav['cta_url']))
            <input type="checkbox" id="gp-nav-toggle" class="gp-nav-toggle">
            <label for="gp-nav-toggle" class="gp-nav-burger" aria-label="{{ __('Ouvrir le menu', [], $page->locale) }}"><span></span></label>

            <nav class="gp-site-nav" aria-label="{{ __('Menu principal', [], $page->locale) }}">
                @if ($nav['menu'])
                    <ul class="gp-site-menu">
                        @foreach ($nav['menu'] as $link)
                            <li><a href="{{ $link['url'] }}"@if ($state = $ariaCurrent($link['url'])) aria-current="{{ $state }}"@endif>{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                @endif
                @if ($nav['languages'])
                    <ul class="gp-site-langs" aria-label="{{ __('Langue', [], $page->locale) }}">
                        @foreach ($nav['languages'] as $lang)
                            <li><a href="{{ $lang['url'] }}" hreflang="{{ $lang['code'] }}" lang="{{ $lang['code'] }}" title="{{ $lang['name'] }}"@if ($lang['current']) aria-current="true"@endif>{{ strtoupper($lang['code']) }}</a></li>
                        @endforeach
                    </ul>
                @endif
                @if ($nav['cta_label'] && $nav['cta_url'])
                    <a class="gp-btn gp-btn-sm gp-site-cta" href="{{ $nav['cta_url'] }}">{{ $nav['cta_label'] }}</a>
                @endif
            </nav>
        @endif
    </div>
</header>
