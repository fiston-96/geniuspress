{{--
    Œil d'un champ de mot de passe : affiche ou masque le texte saisi. À placer juste après
    l'<input type="password">, dans un conteneur « relative » (le champ garde « pr-11 » pour le bouton).
--}}
<button type="button" tabindex="0" aria-pressed="false"
    aria-label="{{ __('Afficher le mot de passe') }}" title="{{ __('Afficher le mot de passe') }}"
    data-show="{{ __('Afficher le mot de passe') }}" data-hide="{{ __('Masquer le mot de passe') }}"
    onclick="const i = this.previousElementSibling, show = i.type === 'password'; i.type = show ? 'text' : 'password';
        this.setAttribute('aria-pressed', show); this.ariaLabel = this.title = show ? this.dataset.hide : this.dataset.show;
        this.children[0].classList.toggle('hidden', show); this.children[1].classList.toggle('hidden', ! show); i.focus();"
    class="absolute inset-y-0 right-0 grid w-11 place-items-center rounded-r-lg text-slate-400 transition hover:text-slate-700 focus:outline-none focus-visible:text-brand-600 focus-visible:ring-2 focus-visible:ring-brand-500/30">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
    <svg class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.1A10.4 10.4 0 0112 5c6.4 0 10 7 10 7a17.6 17.6 0 01-3.2 4.1M6.6 6.6C3.9 8.4 2 12 2 12s3.6 7 10 7a9.8 9.8 0 005.4-1.6"/></svg>
</button>
