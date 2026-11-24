{{-- Carte d'un article dans une archive (affichage par défaut, sans gabarit du thème). --}}
<article class="gp-post-card">
    @if ($post->featuredMedia)
        <a href="{{ $post->url() }}" class="gp-post-card__media" tabindex="-1" aria-hidden="true">
            <img src="{{ $post->featuredMedia->url }}" alt="" loading="lazy">
        </a>
    @endif
    <div class="gp-post-card__body">
        @if ($category = $post->categories->first())
            <a href="{{ $category->url() }}" class="gp-post-card__category">{{ $category->name }}</a>
        @endif
        <h2 class="gp-post-card__title"><a href="{{ $post->url() }}">{{ $post->title }}</a></h2>
        <p class="gp-post-card__meta"><time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->locale($locale)->translatedFormat('j F Y') }}</time></p>
        <p class="gp-post-card__excerpt">{{ $post->summary() }}</p>
        <a href="{{ $post->url() }}" class="gp-post-card__more">{{ __('Lire la suite', [], $locale) }} <span aria-hidden="true">→</span></a>
    </div>
</article>
