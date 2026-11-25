@php
    $tabs = [
        ['admin.platform.dashboard', __('Vue d’ensemble')],
        ['admin.platform.customers.index', __('Clients')],
        ['admin.platform.plans.index', __('Offres')],
        ['admin.platform.pricing.index', __('Tarifs')],
        ['admin.platform.orders.index', __('Commandes')],
        ['admin.platform.domains.index', __('Domaines')],
        ['admin.platform.usage.index', __('Journal IA')],
    ];
@endphp
<div class="mb-6">
    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ __('Plateforme commerciale') }}</p>
    <nav class="mt-2 flex flex-wrap gap-1 border-b border-slate-200 text-sm">
        @foreach ($tabs as [$route, $label])
            @php $active = request()->routeIs(str_replace('.index', '.*', $route)); @endphp
            <a href="{{ route($route) }}" @class(['-mb-px border-b-2 px-3 py-2 font-medium', 'border-indigo-600 text-indigo-700' => $active, 'border-transparent text-slate-600 hover:text-slate-900' => ! $active])>{{ $label }}</a>
        @endforeach
    </nav>
    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
</div>
