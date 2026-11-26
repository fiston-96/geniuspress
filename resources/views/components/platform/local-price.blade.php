@props(['amount', 'currency'])
{{-- Prix dans la devise du pays du client, à titre indicatif (taux de la console). Rien si même devise ou taux absent. --}}
@php($approx = \App\Platform\Countries::approx((int) $amount, (string) $currency, auth('customer')->user()?->country))
@if ($approx)<span {{ $attributes->merge(['class' => 'whitespace-nowrap text-sm font-normal text-slate-500']) }} title="{{ __('Montant indicatif dans votre devise, au taux du jour') }}">{{ $approx }}</span>@endif
