<?php

namespace App\Platform\Referrals;

use App\Platform\Models\Customer;
use App\Platform\Models\WalletTransaction;
use App\Platform\PlatformException;
use Illuminate\Support\Facades\DB;

/**
 * Solde de parrainage d'un client : un grand livre par devise (les commandes peuvent être en CDF,
 * en USD…). Chaque mouvement est écrit avec le solde qui en résulte, sous verrou : deux
 * opérations simultanées ne peuvent pas dépenser deux fois le même argent.
 */
class Wallet
{
    public function balance(Customer $customer, string $currency): int
    {
        return (int) (WalletTransaction::query()->where('customer_id', $customer->id)->where('currency', strtoupper($currency))
            ->latest('id')->value('balance_after') ?? 0);
    }

    /** @return array<string, int> solde par devise (devises au solde non nul) */
    public function balances(Customer $customer): array
    {
        return WalletTransaction::query()->where('customer_id', $customer->id)
            ->whereIn('id', WalletTransaction::query()->selectRaw('max(id)')->where('customer_id', $customer->id)->groupBy('currency'))
            ->pluck('balance_after', 'currency')->map(fn ($v) => (int) $v)->filter()->all();
    }

    public function credit(Customer $customer, int $amount, string $currency, string $kind, string $description, ?string $reference = null, ?int $operatorId = null): WalletTransaction
    {
        return $this->apply($customer, abs($amount), $currency, $kind, $description, $reference, $operatorId, false);
    }

    /** Débit ; refusé si le solde ne suffit pas (sauf $allowNegative : reprise d'une commission annulée). */
    public function debit(Customer $customer, int $amount, string $currency, string $kind, string $description, ?string $reference = null, ?int $operatorId = null, bool $allowNegative = false): WalletTransaction
    {
        return $this->apply($customer, -abs($amount), $currency, $kind, $description, $reference, $operatorId, $allowNegative);
    }

    private function apply(Customer $customer, int $signed, string $currency, string $kind, string $description, ?string $reference, ?int $operatorId, bool $allowNegative): WalletTransaction
    {
        $currency = strtoupper($currency);

        return DB::transaction(function () use ($customer, $signed, $currency, $kind, $description, $reference, $operatorId, $allowNegative) {
            // Verrou sur le compte client : les mouvements d'un même client passent l'un après l'autre.
            Customer::query()->whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $balance = $this->balance($customer, $currency) + $signed;
            if ($signed < 0 && $balance < 0 && ! $allowNegative) {
                throw new PlatformException(__('Votre solde de parrainage ne suffit pas.'));
            }

            return WalletTransaction::create([
                'customer_id' => $customer->id, 'currency' => $currency, 'amount' => $signed, 'balance_after' => $balance,
                'kind' => $kind, 'reference' => $reference, 'description' => mb_substr($description, 0, 255), 'operator_id' => $operatorId,
            ]);
        });
    }
}
