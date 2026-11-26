<?php

namespace App\Platform\Suppliers;

use App\Models\Setting;
use App\Platform\Money;

/**
 * Marge sur les achats chez les fournisseurs : coût d'achat (euros HT chez LWS),
 * taux de change réglés par l'exploitant (1 EUR = x devise) et marge en %.
 * Les prix de vente restent saisis par l'exploitant ; ce calcul lui donne le prix
 * conseillé et la marge réelle de chaque offre.
 */
class SupplierPricing
{
    public const DEFAULT_MARKUP = 30;

    public function markup(): float
    {
        return (float) Setting::read('platform_supplier_markup', self::DEFAULT_MARKUP);
    }

    /** @return array<string, string> devise → valeur de 1 EUR (« USD » → « 1.08 ») */
    public function rates(): array
    {
        return array_filter((array) Setting::read('platform_supplier_rates', []), fn ($rate) => is_numeric($rate) && (float) $rate > 0);
    }

    /** Valeur de 1 EUR dans cette devise, ou null si le taux n'est pas réglé. */
    public function rate(string $currency): ?float
    {
        $currency = strtoupper($currency);

        return $currency === 'EUR' ? 1.0 : (isset($this->rates()[$currency]) ? (float) $this->rates()[$currency] : null);
    }

    /** Montant (plus petite unité) converti d'une devise à l'autre, ou null sans taux. */
    public function convert(int $amount, string $from, string $to): ?int
    {
        if (strtoupper($from) === strtoupper($to)) {
            return $amount;
        }
        $fromRate = $this->rate($from);
        $toRate = $this->rate($to);
        if (! $fromRate || ! $toRate) {
            return null;
        }
        $value = $amount / (10 ** Money::decimals($from)) / $fromRate * $toRate;

        return (int) round($value * (10 ** Money::decimals($to)));
    }

    /** Prix de vente conseillé : coût converti, plus la marge, arrondi à l'unité supérieure. */
    public function suggested(?int $cost, ?string $costCurrency, string $currency): ?int
    {
        if ($cost === null || $costCurrency === null) {
            return null;
        }
        $converted = $this->convert($cost, $costCurrency, $currency);
        if ($converted === null) {
            return null;
        }
        $unit = 10 ** Money::decimals($currency);
        // Devises sans décimales et à petite valeur (XOF, CDF…) : arrondi à la centaine.
        if ($unit === 1 || in_array(strtoupper($currency), ['CDF', 'TZS', 'NGN', 'UGX', 'GNF', 'MGA'], true)) {
            $unit = $unit * 100;
        }

        return (int) (ceil($converted * (1 + $this->markup() / 100) / $unit) * $unit);
    }

    /** Marge réelle (prix de vente moins coût converti), dans la devise de vente, ou null. */
    public function margin(int $price, string $currency, ?int $cost, ?string $costCurrency): ?int
    {
        if ($cost === null || $costCurrency === null) {
            return null;
        }
        $converted = $this->convert($cost, $costCurrency, $currency);

        return $converted === null ? null : $price - $converted;
    }
}
