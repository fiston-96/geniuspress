@extends($layout)
@section('title', __('Notifications'))
@section('content')
@php use App\Notifications\Notice; @endphp
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('Notifications') }}</h1>
        @if ($unread)
            <form method="POST" action="{{ route($prefix.'.read') }}">@csrf
                <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">{{ __('Tout marquer comme lu') }}</button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <svg class="mx-auto h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ Notice::ICONS['bell'] }}"/></svg>
            <p class="mt-3 text-sm text-slate-500">{{ __('Aucune notification pour le moment. Vous serez prévenu ici des messages, des paiements et de la vie de votre site.') }}</p>
        </div>
    @else
        <ul class="mt-6 divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-200 bg-white">
            @foreach ($notifications as $notification)
                <li>
                    <a href="{{ route($prefix.'.open', $notification->id) }}" @class(['flex gap-4 px-5 py-4 transition hover:bg-slate-50', 'bg-brand-50/50' => ! $notification->read_at])>
                        <span @class(['mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-full', 'bg-brand-100 text-brand-700' => ! $notification->read_at, 'bg-slate-100 text-slate-500' => $notification->read_at])>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ Notice::iconOf($notification) }}"/></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span @class(['block text-sm', 'font-semibold text-slate-900' => ! $notification->read_at, 'font-medium text-slate-700' => $notification->read_at])>
                                {{ Notice::titleOf($notification) }}
                                @unless ($notification->read_at)<span class="sr-only">({{ __('non lue') }})</span>@endunless
                            </span>
                            @if ($body = Notice::bodyOf($notification))<span class="mt-0.5 block truncate text-sm text-slate-500">{{ $body }}</span>@endif
                            <span class="mt-1 block text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                        @unless ($notification->read_at)<span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-brand-600" aria-hidden="true"></span>@endunless
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
@endsection
