@extends('layouts.auth')
@section('title', __('Site introuvable'))
@section('content')
    <div class="text-center">
        <h1 class="text-xl font-semibold">{{ __('Site introuvable') }}</h1>
        <p class="mt-2 text-sm text-slate-600">{{ __('Aucun site GeniusPress n’existe à cette adresse.') }}</p>
    </div>
@endsection
