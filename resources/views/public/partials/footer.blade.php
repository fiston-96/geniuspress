{{-- Pied de page commun du site. --}}
<footer class="gp-site-footer">
    <div class="gp-site-footer__inner">
        <div>
            <p class="gp-site-footer__name">{{ $settings['site_name'] }}</p>
            @if ($nav['footer_text'])<p>{{ $nav['footer_text'] }}</p>@endif
        </div>
        @if ($nav['footer_links'])
            <ul class="gp-site-footer__links">
                @foreach ($nav['footer_links'] as $link)<li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>@endforeach
            </ul>
        @endif
    </div>
    @if ($nav['footer']['credit'])
        <p class="gp-site-footer__credit">{!! __('Propulsé par :name', ['name' => '<a href="https://geniuspress.site">GeniusPress</a>'], $site->locale) !!}</p>
    @endif
</footer>
