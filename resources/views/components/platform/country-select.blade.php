@props(['name' => 'country', 'value' => null, 'label' => null])
{{-- Liste des pays (noms dans la langue de l'interface). --}}
<div>
    @if ($label)<label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>@endif
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm']) }}>
        <option value="">{{ __('Choisissez votre pays') }}</option>
        @foreach (\App\Platform\Countries::all() as $code => $country)
            <option value="{{ $code }}" @selected(old($name, $value) === $code)>{{ $country }}</option>
        @endforeach
    </select>
    @error($name)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
