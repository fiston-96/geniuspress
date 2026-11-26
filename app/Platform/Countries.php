<?php

namespace App\Platform;

use App\Platform\Payments\MonerailsGateway;
use App\Platform\Suppliers\SupplierPricing;
use Illuminate\Support\Facades\Cache;
use NumberFormatter;

/**
 * Pays du client : nom dans la langue de l'interface, devise, et prix convertis à titre indicatif
 * (« ≈ 28 704 CDF ») au taux réglé dans la console. Le pays choisi adapte la plateforme : prix
 * affichés dans sa devise, pays et devise présélectionnés au paiement Mobile Money.
 */
final class Countries
{
    /** Codes ISO qui ne sont pas des pays (régions, zones réservées). */
    private const NOT_COUNTRIES = ['EU', 'EZ', 'UN', 'QO', 'ZZ', 'XA', 'XB', 'AQ', 'AC', 'CP', 'DG', 'EA', 'IC', 'TA'];

    /** @return array<string, string> code → nom, triés par nom, dans la langue de l'interface */
    public static function all(): array
    {
        $locale = app()->getLocale();

        return Cache::rememberForever('platform-countries:'.$locale, function () use ($locale) {
            $out = [];
            if (! class_exists(\Locale::class)) {
                return $out;
            }
            foreach (range('A', 'Z') as $a) {
                foreach (range('A', 'Z') as $b) {
                    $code = $a.$b;
                    $name = \Locale::getDisplayRegion('-'.$code, $locale);
                    if ($name !== '' && $name !== $code && ! in_array($code, self::NOT_COUNTRIES, true)) {
                        $out[$code] = $name;
                    }
                }
            }
            uasort($out, fn ($x, $y) => strcoll($x, $y));

            return $out;
        });
    }

    public static function name(?string $country): ?string
    {
        return $country ? (self::all()[strtoupper($country)] ?? null) : null;
    }

    public static function valid(?string $country): bool
    {
        return $country !== null && isset(self::all()[strtoupper($country)]);
    }

    /** Devise du pays (celle de Monerails pour les pays ouverts au Mobile Money), ou null. */
    public static function currency(?string $country): ?string
    {
        if (! $country) {
            return null;
        }
        $country = strtoupper($country);
        if (isset(MonerailsGateway::COUNTRIES[$country])) {
            return MonerailsGateway::COUNTRIES[$country];
        }
        if (! class_exists(NumberFormatter::class)) {
            return null;
        }
        $code = (new NumberFormatter('und_'.$country, NumberFormatter::CURRENCY))->getTextAttribute(NumberFormatter::CURRENCY_CODE);

        return is_string($code) && preg_match('/^[A-Z]{3}$/', $code) && $code !== 'XXX' ? $code : null;
    }

    /**
     * Montant converti dans la devise du pays, à titre indicatif (« ≈ 28 704 CDF »), ou null si
     * c'est déjà la devise du pays ou si le taux de change n'est pas réglé.
     */
    public static function approx(int $amount, string $currency, ?string $country): ?string
    {
        $local = self::currency($country);
        if ($local === null || $local === $currency) {
            return null;
        }
        $converted = app(SupplierPricing::class)->convert($amount, $currency, $local);
        if ($converted === null) {
            return null;
        }
        // Arrondi à l'unité : un ordre de grandeur, pas le montant exact prélevé.
        $unit = 10 ** Money::decimals($local);

        return '≈ '.Money::format((int) (round($converted / $unit) * $unit), $local);
    }

    /** Pays suggéré d'après la langue du navigateur (« fr-CD » → CD), ou null. */
    public static function fromBrowser(?string $acceptLanguage): ?string
    {
        foreach (explode(',', (string) $acceptLanguage) as $part) {
            if (preg_match('/^[a-z]{2,3}[-_]([A-Z]{2})\b/i', trim($part), $m) && self::valid($m[1])) {
                return strtoupper($m[1]);
            }
        }

        return null;
    }
}
