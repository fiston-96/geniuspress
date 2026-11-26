<?php

namespace App\Platform\Ai;

use App\Models\Setting;
use App\Platform\Models\CreditTransaction;
use App\Platform\Models\Customer;
use App\Platform\Notifications\AiCreditsLow;
use App\Platform\PlatformNotifier;
use Illuminate\Support\Facades\DB;

/**
 * Compte de crédits IA d'un client (µ$). Chaque mouvement est inscrit au grand
 * livre ; le solde du client est mis à jour dans la même transaction, avec un
 * verrou sur sa ligne, pour rester juste même avec des appels simultanés.
 */
class CreditAccount
{
    public function credit(Customer $customer, int $amount, string $kind, string $description, ?string $reference = null, ?int $userId = null): CreditTransaction
    {
        return $this->apply($customer, abs($amount), $kind, $description, $reference, $userId);
    }

    /** Débit (le solde peut devenir légèrement négatif : le coût n'est connu qu'après l'appel). */
    public function debit(Customer $customer, int $amount, string $kind, string $description, ?string $reference = null): CreditTransaction
    {
        return $this->apply($customer, -abs($amount), $kind, $description, $reference, null);
    }

    /** Ajustement manuel de l'exploitant, positif ou négatif, avec motif. */
    public function adjust(Customer $customer, int $amount, string $reason, int $userId): CreditTransaction
    {
        return $this->apply($customer, $amount, 'adjustment', $reason, null, $userId);
    }

    public function balance(Customer $customer): int
    {
        return (int) Customer::query()->whereKey($customer->id)->value('ai_balance');
    }

    /** Seuil d'alerte « crédits presque épuisés » (µ$, réglage de l'exploitant ; 0,50 $ par défaut). */
    public static function lowThreshold(): int
    {
        return (int) Setting::read('platform_low_credit_threshold', 500_000);
    }

    private function apply(Customer $customer, int $signed, string $kind, string $description, ?string $reference, ?int $userId): CreditTransaction
    {
        $before = null;
        $transaction = DB::transaction(function () use ($customer, $signed, $kind, $description, $reference, $userId, &$before) {
            $locked = Customer::query()->whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $before = $locked->ai_balance;
            $balance = $locked->ai_balance + $signed;
            Customer::query()->whereKey($customer->id)->update(['ai_balance' => $balance]);
            $customer->setAttribute('ai_balance', $balance)->syncOriginalAttribute('ai_balance');

            return CreditTransaction::create([
                'customer_id' => $customer->id,
                'amount' => $signed,
                'balance_after' => $balance,
                'kind' => $kind,
                'reference' => $reference,
                'description' => mb_substr($description, 0, 255),
                'user_id' => $userId,
            ]);
        });

        // Alerte une seule fois, au moment où le solde passe sous le seuil (jamais à chaque appel).
        $threshold = self::lowThreshold();
        if ($signed < 0 && $threshold > 0 && $before >= $threshold && $transaction->balance_after < $threshold) {
            PlatformNotifier::send($customer, new AiCreditsLow($transaction->balance_after));
        }

        return $transaction;
    }
}
