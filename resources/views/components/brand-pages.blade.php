{{--
    Illustration de la marque : trois pages qui se chevauchent et montent en diagonale
    (or clair, nuit indigo, page de devant blanche à bandeau terracotta), comme le symbole.
    La page de devant montre le site : son nom, son adresse et, si donné, son avancement.
    Aplats uniquement (docs/marque.md : ni dégradé ni ombre).
--}}
@props(['title', 'subtitle' => null, 'progress' => null])
<div {{ $attributes->merge(['class' => 'relative h-48 w-72 shrink-0']) }} aria-hidden="true">
    <div class="absolute bottom-0 left-0 h-36 w-44 rounded-xl bg-gold-light"></div>
    <div class="absolute bottom-5 left-10 h-36 w-44 rounded-xl bg-night ring-1 ring-white/10"></div>
    <div class="absolute bottom-10 left-24 w-48 overflow-hidden rounded-xl bg-white ring-1 ring-line">
        <div class="flex items-center gap-1.5 bg-brand-600 px-3 py-2.5">
            <span class="h-1.5 w-1.5 rounded-full bg-white/70"></span><span class="h-1.5 w-1.5 rounded-full bg-white/50"></span><span class="h-1.5 w-1.5 rounded-full bg-white/30"></span>
            <span class="ml-1 truncate text-[10px] font-semibold text-white">{{ $title }}</span>
        </div>
        <div class="space-y-2 p-3">
            @if ($subtitle)<p class="truncate font-mono text-[9px] text-slate-400">{{ $subtitle }}</p>@endif
            <div class="grid grid-cols-2 gap-1.5">
                <div class="h-7 rounded bg-cream"></div>
                <div class="h-7 rounded bg-paper"></div>
            </div>
            <div class="h-1.5 w-full rounded-full bg-slate-100"></div>
            <div class="h-1.5 w-2/3 rounded-full bg-slate-100"></div>
            @if ($progress !== null)
                <div class="flex items-center gap-2 pt-1">
                    <div class="flex flex-1 gap-0.5">
                        @for ($i = 0; $i < 6; $i++)<span @class(['h-1.5 flex-1 rounded-sm', 'bg-gold' => $i < round($progress / 100 * 6), 'bg-line' => $i >= round($progress / 100 * 6)])></span>@endfor
                    </div>
                    <span class="text-[10px] font-bold tabular-nums text-night">{{ $progress }} %</span>
                </div>
            @endif
        </div>
    </div>
</div>
