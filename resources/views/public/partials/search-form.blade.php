{{-- Formulaire de recherche dans le site. --}}
<form class="gp-search-form" role="search" action="{{ \App\Site\Permalinks::search($locale) }}" method="get">
    <label class="gp-sr-only" for="gp-search-{{ $id ?? 'main' }}">{{ __('Rechercher', [], $locale) }}</label>
    <input id="gp-search-{{ $id ?? 'main' }}" type="search" name="s" value="{{ $value ?? '' }}" placeholder="{{ __('Rechercher dans le site…', [], $locale) }}" maxlength="100" required>
    <button type="submit">{{ __('Rechercher', [], $locale) }}</button>
</form>
