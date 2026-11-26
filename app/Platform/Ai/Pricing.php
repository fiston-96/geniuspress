<?php

namespace App\Platform\Ai;

use App\Models\Setting;
use App\Platform\Money;
use App\Platform\Models\AiRate;

/**
 * Prix de l'IA : coût réel selon le tarif du modèle (USD par million de jetons),
 * puis débit = coût réel × multiplicateur (2 par défaut), arrondi au µ$ supérieur.
 *
 * jetons × prix par million (USD) = coût en µ$ : aucun arrondi avant la fin.
 */
class Pricing
{
    public function multiplier(): string
    {
        $value = (string) Setting::read('platform_ai_multiplier', config('geniuspress.platform.ai_multiplier', 2));

        return is_numeric($value) && (float) $value >= 1 ? $value : '2';
    }

    public function rate(string $model): ?AiRate
    {
        return AiRate::query()->where('model', $model)->where('active', true)->first();
    }

    /** Coût réel en µ$ (arrondi supérieur). */
    public function cost(AiRate $rate, int $inputTokens, int $outputTokens): int
    {
        return self::ceilDiv($this->costE6($rate, $inputTokens, $outputTokens), 1_000_000);
    }

    /** Montant débité au client en µ$ (arrondi supérieur). */
    public function billed(AiRate $rate, int $inputTokens, int $outputTokens): int
    {
        $multiplier = Money::scale($this->multiplier(), 3); // millièmes

        return self::ceilDiv($this->costE6($rate, $inputTokens, $outputTokens) * $multiplier, 1_000_000_000);
    }

    /** Prix facturé au client par million de jetons, en µ$ (affichage). */
    public function billedPerMillion(string $price): int
    {
        return self::ceilDiv(Money::scale($price, 6) * Money::scale($this->multiplier(), 3), 1000);
    }

    /** Coût réel en millionièmes de µ$ (prix stockés avec 6 décimales). */
    private function costE6(AiRate $rate, int $inputTokens, int $outputTokens): int
    {
        return max(0, $inputTokens) * Money::scale((string) $rate->input_price, 6)
            + max(0, $outputTokens) * Money::scale((string) $rate->output_price, 6);
    }

    private static function ceilDiv(int $a, int $b): int
    {
        return intdiv($a + $b - 1, $b);
    }
}
