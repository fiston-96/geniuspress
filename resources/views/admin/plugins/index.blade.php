@extends('layouts.admin')
@section('title', __('Plugins'))
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('Plugins') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Ajoutez des widgets et des fonctionnalités à GeniusPress :version.', ['version' => $version]) }}</p>
        </div>
    </div>

    @if (session('error'))
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="mt-6 space-y-3">
        @forelse ($plugins as $slug => $plugin)
            @php $isActive = in_array($slug, $active, true); @endphp
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-semibold">{{ $plugin->name }}</h2>
                            <span class="text-xs text-slate-500">v{{ $plugin->version }}</span>
                            @if ($isActive)
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('Actif') }}</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ __('Inactif') }}</span>
                            @endif
                        </div>
                        @if ($plugin->description)<p class="mt-1 text-sm text-slate-600">{{ $plugin->description }}</p>@endif
                        <p class="mt-1 text-xs text-slate-500">
                            {{ __('Dossier') }} <code>plugins/{{ $slug }}</code>
                            @if ($plugin->author) · {{ __('par :author', ['author' => $plugin->author]) }}@endif
                            @if ($plugin->license) · {{ __('licence :license', ['license' => $plugin->license]) }}@endif
                            @if ($plugin->requires !== '*') · {{ __('nécessite GeniusPress :version', ['version' => $plugin->requires]) }}@endif
                        </p>
                    </div>
                    <form method="POST" action="{{ route($isActive ? 'admin.plugins.deactivate' : 'admin.plugins.activate', $slug) }}">
                        @csrf
                        @if ($isActive)
                            <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold hover:bg-slate-50">{{ __('Désactiver') }}</button>
                        @else
                            <button class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-500 disabled:opacity-50" @disabled(! $plugin->isValid())>{{ __('Activer') }}</button>
                        @endif
                    </form>
                </div>
                @foreach ($plugin->errors as $message)
                    <p class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">{{ $message }}</p>
                @endforeach
                @if (! empty($errors[$slug]) && ! $isActive)
                    <p class="mt-3 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ __('Désactivé automatiquement après une erreur : :error', ['error' => $errors[$slug]]) }}</p>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
                {{ __('Aucun plugin installé.') }}
            </div>
        @endforelse
    </div>

    <div class="mt-8 rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-600">
        <h2 class="font-semibold text-slate-900">{{ __('Installer un plugin') }}</h2>
        <p class="mt-1">{!! __('Copiez le dossier du plugin dans :dir, puis activez-le ici. Pour en créer un : :command.', ['dir' => '<code>'.e($directory).'</code>', 'command' => '<code>php artisan geniuspress:make-plugin mon-plugin</code>']) !!}</p>
        <p class="mt-2 text-amber-700">{{ __('Un plugin exécute du code sur votre serveur : n’installez que des plugins de sources de confiance.') }}</p>
    </div>
@endsection
