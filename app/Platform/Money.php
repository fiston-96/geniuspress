<?php

namespace App\Platform;

/**
 * Montants en plus petite unité de la devise (centimes, ou francs pour les
 * devises sans décimales), et crédits IA en micro-dollars (µ$).
 */
final class Money
{
    /** Devises sans subdivision utilisée (ISO 4217). */
    public const ZERO_DECIMAL = ['XOF', 'XAF', 'RWF', 'UGX', 'BIF', 'GNF', 'KMF', 'DJF', 'JPY', 'KRW', 'VND', 'CLP', 'PYG', 'MGA'];

    public const CURRENCIES = ['USD', 'EUR', 'XOF', 'XAF', 'CDF', 'RWF', 'KES', 'UGX', 'TZS', 'NGN', 'GHS', 'ZAR', 'MAD', 'BIF', 'GNF', 'MGA'];

    public static function decimals(string $currency): int
    {
        return in_array(strtoupper($currency), self::ZERO_DECIMAL, true) ? 0 : 2;
    }

    /** Saisie de l'exploitant (« 12,50 » ou « 12.50 ») → plus petite unité. */
    public static function parse(string|int|float $amount, string $currency): int
    {
        return self::scale((string) $amount, self::decimals($currency));
    }

    public static function format(int $amount, string $currency): string
    {
        $decimals = self::decimals($currency);
        $value = $amount / (10 ** $decimals);
        $fr = app()->getLocale() !== 'en';
        $number = number_format($value, $decimals, $fr ? ',' : '.', $fr ? "\u{202F}" : ',');

        return $fr ? $number."\u{00A0}".$currency : $currency.' '.$number;
    }

    /** Saisie d'un montant en dollars → µ$. */
    public static function usdToMicros(string|int|float $usd): int
    {
        return self::scale((string) $usd, 6);
    }

    /** µ$ → « 4,25 $ » (4 décimales sous 1 $ pour que les petits débits restent lisibles). */
    public static function micros(int $micros): string
    {
        $decimals = abs($micros) < 1_000_000 && $micros !== 0 ? 4 : 2;
        $fr = app()->getLocale() !== 'en';
        $number = number_format($micros / 1_000_000, $decimals, $fr ? ',' : '.', $fr ? "\u{202F}" : ',');

        return $fr ? $number."\u{00A0}$" : '$'.$number;
    }

    /** « 12,5 » avec $decimals décimales → entier, sans passer par les flottants. */
    public static function scale(string $value, int $decimals): int
    {
        $value = str_replace([' ', "\u{202F}", "\u{00A0}"], '', trim($value));
        $value = str_replace(',', '.', $value);
        if (! preg_match('/^(-?)(\d*)(?:\.(\d*))?$/', $value, $m) || ($m[2] === '' && ($m[3] ?? '') === '')) {
            throw new \InvalidArgumentException('Montant invalide');
        }
        $fraction = substr(str_pad($m[3] ?? '', $decimals, '0'), 0, $decimals);
        $int = (int) (($m[2] === '' ? '0' : $m[2]).$fraction);

        return $m[1] === '-' ? -$int : $int;
    }

    /** Entier → saisie (« 12.50 ») pour pré-remplir un formulaire. */
    public static function input(int $amount, int $decimals): string
    {
        $unit = 10 ** $decimals;
        $fraction = $decimals > 0 ? rtrim(str_pad((string) (abs($amount) % $unit), $decimals, '0', STR_PAD_LEFT), '0') : '';

        return ($amount < 0 ? '-' : '').intdiv(abs($amount), $unit).($fraction !== '' ? '.'.$fraction : '');
    }
}
