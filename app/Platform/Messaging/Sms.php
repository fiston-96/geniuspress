<?php

namespace App\Platform\Messaging;

use App\Models\Setting;
use App\Platform\Models\Customer;
use App\Platform\Models\SmsMessage;
use App\Platform\PlatformException;
use Throwable;

/**
 * SMS de la plateforme : le premier prestataire configuré qui couvre le numéro l'envoie ; chaque
 * tentative est journalisée (sans le texte). Un échec n'interrompt jamais l'action en cours.
 * Désactivé tant que l'exploitant ne l'a pas activé (console → Messagerie).
 */
class Sms
{
    /** Prestataires, dans l'ordre d'essai. Un prestataire pour l'Afrique centrale s'ajoutera ici. */
    public const GATEWAYS = [AfricasTalkingGateway::class];

    /** Longueur maximale d'un SMS de la plateforme (au-delà, il est coupé proprement). */
    public const MAX_LENGTH = 320;

    public static function enabled(): bool
    {
        return (bool) Setting::read('platform_sms_enabled', false);
    }

    /** « +243 81 234 5678 », « 00243812345678 » → « +243812345678 » ; null sans indicatif du pays. */
    public static function normalize(?string $phone): ?string
    {
        $phone = trim((string) $phone);
        if (str_starts_with($phone, '00')) {
            $phone = '+'.substr($phone, 2);
        }
        if (! str_starts_with($phone, '+')) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone);

        return strlen((string) $digits) >= 8 && strlen((string) $digits) <= 15 ? '+'.$digits : null;
    }

    /** Prestataire configuré qui couvre ce numéro, ou null. */
    public function gatewayFor(string $phone): ?SmsGateway
    {
        foreach (self::GATEWAYS as $class) {
            $gateway = app($class);
            if ($gateway->configured() && $gateway->covers($phone)) {
                return $gateway;
            }
        }

        return null;
    }

    /** SMS d'une notification à un client (s'il les accepte et a un numéro couvert). */
    public function toCustomer(Customer $customer, string $text, string $kind): ?SmsMessage
    {
        if (! self::enabled() || ! $customer->sms_opt_in) {
            return null;
        }
        $phone = self::normalize($customer->phone);
        if ($phone === null) {
            return null;
        }

        return $this->send($phone, $text, $kind, $customer);
    }

    /** Envoie et journalise ; ne lève jamais d'exception (l'erreur est dans le journal). */
    public function send(string $phone, string $text, string $kind, ?Customer $customer = null): SmsMessage
    {
        $log = ['customer_id' => $customer?->id, 'to' => $phone, 'kind' => mb_substr($kind, 0, 60)];
        $gateway = $this->gatewayFor($phone);
        if (! $gateway) {
            return SmsMessage::create($log + ['status' => 'skipped', 'error' => __('Aucun prestataire SMS ne couvre ce pays.')]);
        }
        $text = mb_strlen($text) > self::MAX_LENGTH ? mb_substr($text, 0, self::MAX_LENGTH - 1).'…' : $text;
        try {
            $result = $gateway->send($phone, $text);
        } catch (PlatformException $e) {
            return SmsMessage::create($log + ['provider' => $gateway->name(), 'status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 255)]);
        } catch (Throwable $e) {
            report($e);

            return SmsMessage::create($log + ['provider' => $gateway->name(), 'status' => 'failed', 'error' => __('Erreur inattendue.')]);
        }

        return SmsMessage::create($log + ['provider' => $gateway->name(), 'status' => 'sent', 'provider_id' => $result->id, 'cost' => $result->cost]);
    }
}
