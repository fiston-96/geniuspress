<x-mail::message>
{!! $body !!}

<x-slot:subcopy>
{{ __('Vous recevez cet e-mail parce que vous êtes inscrit à notre newsletter.') }} [{{ __('Se désinscrire') }}]({{ $unsubscribeUrl }})
</x-slot:subcopy>
</x-mail::message>
