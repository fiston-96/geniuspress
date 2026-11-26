@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null])
@php($password = $type === 'password')
<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>
    @if ($password)<div class="relative">@endif
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        value="{{ $password ? '' : old($name, $value) }}"
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20'.($password ? ' pr-11' : '')]) }}>
    @if ($password)<x-password-toggle /></div>@endif
    @if ($hint)<p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
