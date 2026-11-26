@extends('layouts.admin')
@section('title', __('Commandes'))
@section('content')
    @include('platform.admin._nav')
    <form class="flex gap-2">
        <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()" aria-label="{{ __('État') }}">
            <option value="">{{ __('Tous les états') }}</option>
            @foreach (['pending', 'paid', 'failed', 'canceled'] as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ \App\Platform\Labels::status($s) }}</option>
            @endforeach
        </select>
    </form>
    @include('platform.admin.orders._table', ['orders' => $orders])
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
