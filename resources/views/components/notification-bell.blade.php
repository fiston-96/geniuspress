{{-- Cloche des notifications avec le nombre de non-lues (administration et espace client). --}}
@props(['href', 'count' => 0])
<a href="{{ $href }}" title="{{ __('Notifications') }}" {{ $attributes->merge(['class' => 'relative inline-grid h-9 w-9 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900']) }}>
    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ \App\Notifications\Notice::ICONS['bell'] }}"/></svg>
    <span class="sr-only">{{ __('Notifications') }}@if ($count) ({{ trans_choice('{1} 1 non lue|[2,*] :count non lues', $count) }})@endif</span>
    @if ($count)
        <span class="absolute -right-0.5 -top-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-brand-600 px-1 text-[10px] font-bold leading-none text-white" aria-hidden="true">{{ $count > 9 ? '9+' : $count }}</span>
    @endif
</a>
