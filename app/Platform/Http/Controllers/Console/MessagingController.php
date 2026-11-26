<?php

namespace App\Platform\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Platform\Messaging\AfricasTalkingGateway;
use App\Platform\Messaging\Sms;
use App\Platform\Models\SmsMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Messagerie : SMS de la plateforme (Africa's Talking). Activation, nom d'utilisateur, clé API
 * chiffrée (jamais réaffichée), identifiant d'expéditeur, SMS d'essai et journal des envois.
 */
class MessagingController extends Controller
{
    public function index(AfricasTalkingGateway $at)
    {
        return view('platform.admin.messaging', [
            'enabled' => Sms::enabled(),
            'at' => $at,
            'keyHint' => Setting::read('platform_at_key_hint'),
            'countries' => collect(AfricasTalkingGateway::COUNTRY_CODES)->map(fn ($country) => \App\Platform\Countries::name($country) ?? $country)->sort()->values()->all(),
            'messages' => SmsMessage::query()->with('customer')->latest('id')->limit(30)->get(),
            'month' => SmsMessage::query()->where('created_at', '>=', now()->startOfMonth())->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'enabled' => ['sometimes', 'boolean'],
            'username' => ['nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9_.-]+$/'],
            'api_key' => ['nullable', 'string', 'max:200'],
            'api_key_remove' => ['sometimes', 'boolean'],
            'sender' => ['nullable', 'string', 'max:11', 'regex:/^[A-Za-z0-9 ]+$/'],
        ]);
        $values = [
            'platform_sms_enabled' => $request->boolean('enabled'),
            'platform_at_username' => $data['username'] ?? null,
            'platform_at_sender' => $data['sender'] ?? null,
        ];
        $key = trim((string) ($data['api_key'] ?? ''));
        if ($key !== '') {
            $values['platform_at_key'] = Crypt::encryptString($key);
            $values['platform_at_key_hint'] = '…'.substr($key, -4);
        } elseif ($request->boolean('api_key_remove')) {
            $values['platform_at_key'] = null;
            $values['platform_at_key_hint'] = null;
        }
        Setting::write($values);

        return back()->with('status', __('Réglages de la messagerie enregistrés.'));
    }

    /** SMS d'essai vers un numéro saisi par l'exploitant (journalisé « test »). */
    public function test(Request $request, Sms $sms)
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:30']]);
        $phone = Sms::normalize($data['phone']);
        if ($phone === null) {
            return back()->withErrors(['phone' => __('Indiquez le numéro avec l’indicatif du pays (+221…).')]);
        }
        $message = $sms->send($phone, __('GeniusPress : SMS d’essai de la messagerie de la plateforme.'), 'test');

        return $message->status === 'sent'
            ? back()->with('status', __('SMS d’essai accepté par :provider (coût : :cost).', ['provider' => "Africa’s Talking", 'cost' => $message->cost ?? '—']))
            : back()->withErrors(['phone' => $message->error]);
    }
}
