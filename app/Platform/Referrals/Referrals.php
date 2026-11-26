<?php

namespace App\Platform\Referrals;

use App\Models\Setting;
use App\Platform\Models\Customer;
use App\Platform\Models\Operator;
use App\Platform\Models\Order;
use App\Platform\Models\Payout;
use App\Platform\Models\ReferralCommission;
use App\Platform\Notifications\PayoutProcessed;
use App\Platform\Notifications\PayoutRequested;
use App\Platform\Notifications\ReferralCommissionAvailable;
use App\Platform\Notifications\ReferralCommissionEarned;
use App\Platform\Notifications\ReferralSignup;
use App\Platform\PlatformException;
use App\Platform\PlatformNotifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Affiliation de la plateforme.
 *
 * - Chaque client a un code ; son lien (…?ref=CODE) est retenu 30 jours par un cookie, puis
 *   l'inscription (mot de passe ou Google) rattache le nouveau client à son affilié.
 * - À la première commande payée du client apporté, l’affilié gagne le taux du réglage
 *   platform_referral_rate (25 % par défaut) sur la commande hors noms de domaine.
 * - La commission attend platform_referral_hold_days jours (30 par défaut, le temps d'un
 *   remboursement), puis rejoint le solde d’affiliation (Wallet) : l’affilié s'en sert pour
 *   payer ses commandes ou en demande le retrait par Mobile Money (traité dans la console).
 */
class Referrals
{
    public const COOKIE = 'gp_ref';
    public const COOKIE_DAYS = 30;
    /** Lignes de commande exclues du calcul (domaines : faible marge). */
    public const EXCLUDED_KINDS = ['domain_register', 'domain_renew'];
    /** Opérateurs proposés pour les retraits (transfert fait par l'exploitant). */
    public const PAYOUT_OPERATORS = [
        'airtel' => 'Airtel Money', 'mpesa' => 'M-Pesa', 'orange' => 'Orange Money', 'afrimoney' => 'Afrimoney',
        'mtn' => 'MTN MoMo', 'moov' => 'Moov Money', 'wave' => 'Wave',
    ];

    public function __construct(private Wallet $wallet) {}

    public static function rate(): int
    {
        return max(0, min(100, (int) Setting::read('platform_referral_rate', 25)));
    }

    public static function holdDays(): int
    {
        return max(0, min(365, (int) Setting::read('platform_referral_hold_days', 30)));
    }

    public static function validCode(mixed $code): ?string
    {
        return is_string($code) && preg_match('/^[A-Za-z0-9]{6,16}$/', $code) ? strtoupper($code) : null;
    }

    /** Code d’affiliation du client (créé au premier besoin ; sans 0/O ni 1/I pour être dicté sans erreur). */
    public function codeFor(Customer $customer): string
    {
        if ($customer->referral_code) {
            return $customer->referral_code;
        }
        do {
            $code = substr(str_shuffle(str_repeat('ABCDEFGHJKLMNPQRSTUVWXYZ23456789', 4)), 0, 8);
        } while (Customer::query()->where('referral_code', $code)->exists());
        $customer->forceFill(['referral_code' => $code])->save();

        return $code;
    }

    public function link(Customer $customer): string
    {
        return route('platform.register', ['ref' => $this->codeFor($customer)]);
    }

    /** Rattache un nouveau client à son affilié (jamais à lui-même, jamais deux fois). */
    public function attach(Customer $customer, mixed $code): ?Customer
    {
        $code = self::validCode($code);
        if (! $code || $customer->referred_by) {
            return null;
        }
        $sponsor = Customer::query()->where('referral_code', $code)->first();
        if (! $sponsor || $sponsor->id === $customer->id || ! $sponsor->isActive() || Str::lower($sponsor->email) === Str::lower($customer->email)) {
            return null;
        }
        $customer->forceFill(['referred_by' => $sponsor->id, 'referred_at' => now()])->save();
        PlatformNotifier::send($sponsor, new ReferralSignup($customer));

        return $sponsor;
    }

    /** Commande payée : commission si c'est la première commande payée d'un client apporté. */
    public function onOrderPaid(Order $order): ?ReferralCommission
    {
        $customer = $order->customer;
        $sponsor = $customer?->sponsor;
        if (! $sponsor || ! $sponsor->isActive() || self::rate() === 0) {
            return null;
        }
        $firstPaid = $customer->orders()->where('status', 'paid')->orderBy('paid_at')->orderBy('id')->value('id');
        if ($firstPaid !== $order->id || ReferralCommission::query()->where('referred_id', $customer->id)->exists()) {
            return null;
        }
        $base = (int) $order->items()->whereNotIn('kind', self::EXCLUDED_KINDS)->sum('amount');
        $rateBps = self::rate() * 100;
        $amount = intdiv($base * $rateBps + 5000, 10000);
        if ($amount <= 0) {
            return null;
        }

        $commission = ReferralCommission::query()->firstOrCreate(['order_id' => $order->id], [
            'sponsor_id' => $sponsor->id, 'referred_id' => $customer->id, 'base_amount' => $base, 'amount' => $amount,
            'currency' => $order->currency, 'rate_bps' => $rateBps, 'status' => 'pending', 'available_at' => now()->addDays(self::holdDays()),
        ]);
        if ($commission->wasRecentlyCreated) {
            PlatformNotifier::send($sponsor, new ReferralCommissionEarned($commission));
            if (self::holdDays() === 0) {
                $this->release($commission);
            }
        }

        return $commission;
    }

    /** Commissions arrivées à échéance → solde d’affiliation. Renvoie le nombre libéré. */
    public function releaseDue(): int
    {
        $count = 0;
        ReferralCommission::query()->where('status', 'pending')->where('available_at', '<=', now())->orderBy('id')
            ->each(function (ReferralCommission $c) use (&$count) {
                $count += $this->release($c) ? 1 : 0;
            });

        return $count;
    }

    public function release(ReferralCommission $commission): bool
    {
        $released = DB::transaction(function () use ($commission) {
            $locked = ReferralCommission::query()->whereKey($commission->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== 'pending') {
                return false;
            }
            $this->wallet->credit($locked->sponsor, $locked->amount, $locked->currency, 'referral',
                __('Commission d’affiliation : :name', ['name' => $locked->referred?->name ?? '—']), 'com-'.$locked->id);
            $locked->update(['status' => 'available', 'released_at' => now()]);
            $commission->setRawAttributes($locked->getAttributes(), true);

            return true;
        });
        if ($released) {
            PlatformNotifier::send($commission->sponsor, new ReferralCommissionAvailable($commission));
        }

        return $released;
    }

    /** Annulation par l'exploitant (remboursement, fraude) ; une commission déjà versée est reprise sur le solde. */
    public function cancel(ReferralCommission $commission, string $reason, Operator $operator): void
    {
        DB::transaction(function () use ($commission, $reason, $operator) {
            $locked = ReferralCommission::query()->whereKey($commission->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'canceled') {
                return;
            }
            if ($locked->status === 'available') {
                $this->wallet->debit($locked->sponsor, $locked->amount, $locked->currency, 'reversal',
                    __('Commission annulée : :reason', ['reason' => $reason]), 'com-'.$locked->id, $operator->id, allowNegative: true);
            }
            $locked->update(['status' => 'canceled', 'canceled_at' => now(), 'cancel_reason' => mb_substr($reason, 0, 255)]);
        });
    }

    // ─── Retraits ───────────────────────────────────────────────────────

    /** Demande de retrait : le montant est réservé tout de suite (débité du solde). */
    public function requestPayout(Customer $customer, int $amount, string $currency, string $mobileOperator, string $phone): Payout
    {
        if ($amount <= 0) {
            throw new PlatformException(__('Indiquez un montant à retirer.'));
        }
        if (Payout::query()->where('customer_id', $customer->id)->where('status', 'requested')->exists()) {
            throw new PlatformException(__('Une demande de retrait est déjà en cours. Attendez qu’elle soit traitée.'));
        }

        $payout = DB::transaction(function () use ($customer, $amount, $currency, $mobileOperator, $phone) {
            $payout = Payout::create(['customer_id' => $customer->id, 'amount' => $amount, 'currency' => strtoupper($currency),
                'mobile_operator' => $mobileOperator, 'phone' => $phone, 'status' => 'requested']);
            $this->wallet->debit($customer, $amount, $currency, 'payout', __('Demande de retrait par Mobile Money'), 'pay-'.$payout->id);

            return $payout;
        });
        PlatformNotifier::admins(new PayoutRequested($payout));

        return $payout;
    }

    /** L'exploitant a fait le transfert Mobile Money. */
    public function markPaid(Payout $payout, Operator $operator, string $transferReference): void
    {
        $done = Payout::query()->whereKey($payout->id)->where('status', 'requested')->update([
            'status' => 'paid', 'transfer_reference' => mb_substr($transferReference, 0, 120), 'operator_id' => $operator->id, 'processed_at' => now(),
        ]);
        if ($done) {
            PlatformNotifier::send($payout->customer, new PayoutProcessed($payout->fresh()));
        }
    }

    /** Refus : le montant revient sur le solde. */
    public function reject(Payout $payout, Operator $operator, string $note): void
    {
        $done = DB::transaction(function () use ($payout, $operator, $note) {
            $locked = Payout::query()->whereKey($payout->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== 'requested') {
                return false;
            }
            $this->wallet->credit($locked->customer, $locked->amount, $locked->currency, 'payout_refund', __('Retrait refusé : :note', ['note' => $note]), 'pay-'.$locked->id, $operator->id);
            $locked->update(['status' => 'rejected', 'note' => mb_substr($note, 0, 255), 'operator_id' => $operator->id, 'processed_at' => now()]);

            return true;
        });
        if ($done) {
            PlatformNotifier::send($payout->customer, new PayoutProcessed($payout->fresh()));
        }
    }
}
