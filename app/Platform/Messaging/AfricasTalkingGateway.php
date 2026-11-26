<?php

namespace App\Platform\Messaging;

use App\Models\Setting;
use App\Platform\PlatformException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Africa's Talking (https://developers.africastalking.com/docs/sms) : SMS dans une vingtaine de
 * pays (pas la RD Congo ni l'Afrique centrale). En-tête « apiKey » ; nom d'utilisateur de
 * l'application et identifiant d'expéditeur (sender ID) enregistré chez Africa's Talking.
 *
 * Production : POST /version1/messaging/bulk (JSON). Bac à sable (username « sandbox ») :
 * POST https://api.sandbox.africastalking.com/version1/messaging (formulaire), sans vrai SMS.
 * Réglages : console → Messagerie (clé chiffrée, jamais réaffichée).
 */
class AfricasTalkingGateway implements SmsGateway
{
    /** Indicatifs des pays couverts par les SMS d'Africa's Talking (page Tarifs, novembre 2026). */
    public const COUNTRY_CODES = [
        '229' => 'BJ', '226' => 'BF', '225' => 'CI', '20' => 'EG', '268' => 'SZ', '233' => 'GH', '254' => 'KE',
        '266' => 'LS', '265' => 'MW', '223' => 'ML', '264' => 'NA', '227' => 'NE', '234' => 'NG', '250' => 'RW',
        '221' => 'SN', '27' => 'ZA', '255' => 'TZ', '228' => 'TG', '256' => 'UG', '260' => 'ZM',
    ];

    /** Codes d'état d'un destinataire accepté : traité, envoyé, en file. */
    private const ACCEPTED = [100, 101, 102];

    public function name(): string
    {
        return 'africastalking';
    }

    public function configured(): bool
    {
        return $this->username() !== '' && $this->key() !== '';
    }

    public function sandbox(): bool
    {
        return $this->username() === 'sandbox';
    }

    public function username(): string
    {
        return trim((string) Setting::read('platform_at_username'));
    }

    public function senderId(): ?string
    {
        $sender = trim((string) Setting::read('platform_at_sender'));

        return $sender !== '' ? $sender : null;
    }

    private function key(): string
    {
        $stored = Setting::read('platform_at_key');
        try {
            return $stored ? Crypt::decryptString((string) $stored) : '';
        } catch (Throwable) {
            return '';
        }
    }

    public function covers(string $phone): bool
    {
        $digits = ltrim($phone, '+');
        foreach (array_keys(self::COUNTRY_CODES) as $code) {
            if (str_starts_with($digits, (string) $code)) {
                return true;
            }
        }

        return false;
    }

    public function send(string $phone, string $text): SmsResult
    {
        if (! $this->configured()) {
            throw new PlatformException(__('Africa’s Talking n’est pas configuré (console → Messagerie).'));
        }
        $http = Http::withHeaders(['apiKey' => $this->key()])->acceptJson()->timeout(15);
        try {
            if ($this->sandbox()) {
                $response = $http->asForm()->post('https://api.sandbox.africastalking.com/version1/messaging', array_filter([
                    'username' => 'sandbox', 'to' => $phone, 'message' => $text, 'from' => $this->senderId(),
                ]));
            } else {
                $response = $http->asJson()->post('https://api.africastalking.com/version1/messaging/bulk', array_filter([
                    'username' => $this->username(), 'phoneNumbers' => [$phone], 'message' => $text,
                    'senderId' => $this->senderId(), 'enqueue' => 1,
                ]));
            }
        } catch (ConnectionException) {
            throw new PlatformException(__('Africa’s Talking ne répond pas pour le moment.'));
        }

        if ($response->status() === 401) {
            throw new PlatformException(__('Africa’s Talking a refusé la clé API : vérifiez le nom d’utilisateur et la clé.'));
        }
        $recipient = $response->json('SMSMessageData.Recipients.0');
        if (! $response->successful() || ! is_array($recipient)) {
            throw new PlatformException(__('Africa’s Talking n’a pas accepté le SMS (:status).', ['status' => $response->status()]));
        }
        $status = (int) ($recipient['statusCode'] ?? 0);
        if (! in_array($status, self::ACCEPTED, true)) {
            throw new PlatformException(match ($status) {
                402 => __('Identifiant d’expéditeur refusé : demandez-le dans votre compte Africa’s Talking.'),
                403 => __('Numéro de téléphone invalide.'),
                405 => __('Solde Africa’s Talking insuffisant.'),
                406 => __('Ce numéro a refusé les SMS (liste noire).'),
                407, 404 => __('Africa’s Talking ne peut pas joindre ce numéro.'),
                409 => __('Ce numéro refuse les SMS commerciaux (Ne pas déranger).'),
                default => __('Africa’s Talking a refusé le SMS (code :code).', ['code' => $status]),
            });
        }

        return new SmsResult(
            mb_substr((string) ($recipient['messageId'] ?? ''), 0, 100) ?: null,
            mb_substr((string) ($recipient['cost'] ?? ''), 0, 30) ?: null,
        );
    }
}
