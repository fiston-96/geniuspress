@extends('layouts.admin')
@section('title', __('Rédaction par l’assistant IA'))
@section('content')
<h1 class="text-2xl font-semibold tracking-tight">{{ __('L’assistant IA rédige votre site') }}</h1>
<p class="mt-1 max-w-2xl text-sm text-slate-500">{{ __('Chaque page est réécrite pour votre organisation, une après l’autre (environ une demi-minute par page). Gardez cette page ouverte. Ce qui n’est pas connu reste entre crochets, à compléter.') }}</p>

<div class="mt-6 max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
    <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div id="gp-fill-bar" class="h-full w-0 rounded-full bg-brand-600 transition-all"></div></div>
    <p id="gp-fill-summary" class="mt-3 text-sm font-medium" role="status">{{ __('Préparation…') }}</p>
    <ul class="mt-4 divide-y divide-slate-100 text-sm" id="gp-fill-list">
        @foreach ($pages as $page)
            <li class="flex items-center justify-between gap-3 py-2" data-page="{{ $page->id }}" data-url="{{ route('admin.ai.fill', $page) }}">
                <span><span class="mr-2 rounded bg-slate-100 px-1.5 py-0.5 text-xs font-semibold uppercase text-slate-500">{{ $page->locale }}</span><span data-title>{{ $page->title }}</span></span>
                <span data-state class="text-slate-400">{{ __('En attente') }}</span>
            </li>
        @endforeach
    </ul>
    <div id="gp-fill-done" class="mt-6 hidden flex-wrap gap-2">
        <a href="{{ route('admin.pages.index') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Voir mes pages') }}</a>
        <a href="{{ url('/') }}" target="_blank" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">{{ __('Voir le site') }}</a>
    </div>
</div>

<script>
(() => {
    const token = document.querySelector('meta[name=csrf-token]')?.content ?? @js(csrf_token());
    const items = [...document.querySelectorAll('#gp-fill-list [data-page]')];
    const bar = document.getElementById('gp-fill-bar');
    const summary = document.getElementById('gp-fill-summary');
    const text = {
        running: @js(__('Rédaction en cours…')), done: @js(__('Terminé')), failed: @js(__('Échec')), retry: @js(__('Réessayer')),
        progress: @js(__(':done pages sur :total')), finished: @js(__('C’est prêt : relisez vos pages et complétez ce qui est entre crochets.')),
        partial: @js(__('Terminé avec des erreurs : vous pouvez réessayer les pages en échec.')),
    };
    let done = 0;
    const update = () => {
        bar.style.width = `${Math.round((done / items.length) * 100)}%`;
        summary.textContent = text.progress.replace(':done', done).replace(':total', items.length);
    };
    async function fill(item) {
        const state = item.querySelector('[data-state]');
        state.className = 'text-brand-600';
        state.textContent = text.running;
        try {
            const response = await fetch(item.dataset.url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' } });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || response.statusText);
            item.querySelector('[data-title]').textContent = data.page.title;
            state.className = 'text-emerald-600';
            state.innerHTML = '';
            const link = document.createElement('a');
            link.href = data.builder_url;
            link.className = 'font-medium hover:underline';
            link.textContent = `✓ ${text.done}`;
            state.appendChild(link);
            done++;
            return true;
        } catch (error) {
            state.className = 'text-red-600';
            state.textContent = `${text.failed} : ${error.message} `;
            const again = document.createElement('button');
            again.className = 'ml-1 font-medium underline';
            again.textContent = text.retry;
            again.onclick = async () => { if (await fill(item)) update(); };
            state.appendChild(again);
            return false;
        }
    }
    (async () => {
        update();
        let failures = 0;
        for (const item of items) {
            if (!(await fill(item))) failures++;
            update();
        }
        summary.textContent = failures ? text.partial : text.finished;
        document.getElementById('gp-fill-done').classList.replace('hidden', 'flex');
    })();
})();
</script>
@endsection
