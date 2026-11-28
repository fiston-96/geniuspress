{{-- Popups du thème (App\Site\Popups) : cachées au chargement, ouvertes par resources/js/site.js. --}}
@foreach ($popups as $popup)
    @php
        $o = $popup['options'];
        $modal = $o['overlay'] && ! in_array($o['position'], ['bottom-left', 'bottom-right'], true);
        $style = '--gp-popup-w:'.$o['width'].'px;--gp-popup-radius:'.$o['radius'].'px;--gp-popup-bg:'.\App\Builder\RenderContext::value($o['background'])
            .';--gp-popup-overlay:'.\App\Builder\RenderContext::value($o['overlay_color']);
    @endphp
    <div id="popup-{{ $popup['id'] }}" class="gp-popup gp-popup--{{ $o['position'] }} gp-popup--{{ $o['animation'] }}" hidden
        role="dialog" aria-modal="{{ $modal ? 'true' : 'false' }}" aria-label="{{ $popup['name'] }}" style="{{ $style }}"
        data-popup="{{ $popup['id'] }}" data-trigger="{{ $o['trigger'] }}" data-delay="{{ $o['delay'] }}" data-scroll="{{ $o['scroll'] }}"
        data-frequency="{{ $o['frequency'] }}" data-days="{{ $o['days'] }}" data-devices="{{ $o['devices'] }}"
        @if ($modal) data-modal @endif @if ($o['close_outside']) data-close-outside @endif @if ($popup['preview']) data-preview @endif>
        @if ($o['overlay'])<div class="gp-popup-overlay"></div>@endif
        <div class="gp-popup-box" tabindex="-1">
            @if ($o['close_button'])
                <button type="button" class="gp-popup-close" data-popup-close aria-label="{{ __('Fermer') }}" title="{{ __('Fermer') }}">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            @endif
            <div class="gp-popup-content">{!! $popup['html'] !!}</div>
        </div>
    </div>
@endforeach
