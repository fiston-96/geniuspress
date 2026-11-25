@extends('layouts.auth')
@section('title', $suspended ? __('Site suspendu') : __('Essai terminé'))
@section('content')
    <div class="text-center">
        @if ($suspended)
            <h1 class="text-xl font-semibold">{{ __('Ce site est suspendu') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('Ce site n’est pas disponible pour le moment.') }}</p>
        @else
            <h1 class="text-xl font-semibold">{{ __('Ce site d’essai est terminé') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('Ce site a été créé avec GeniusPress pendant un essai gratuit, qui est arrivé à son terme.') }}</p>
            <p class="mt-4 text-sm">
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ __('Vous êtes le propriétaire ? Connectez-vous pour exporter votre site ou choisir une offre.') }}</a>
            </p>
        @endif
    </div>
@endsection
