<?php

namespace App\Platform\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Platform\Models\Customer;
use App\Platform\Models\Payout;
use App\Platform\Models\ReferralCommission;
use App\Platform\Referrals\Referrals;
use Illuminate\Http\Request;

/** Parrainage dans la console : réglages, retraits à traiter, commissions, meilleurs parrains. */
class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('statut'), ['pending', 'available', 'canceled'], true) ? $request->query('statut') : null;

        return view('platform.admin.referrals', [
            'rate' => Referrals::rate(),
            'holdDays' => Referrals::holdDays(),
            'requested' => Payout::query()->with('customer:id,name,email')->where('status', 'requested')->oldest()->get(),
            'processed' => Payout::query()->with(['customer:id,name', 'operator:id,name'])->where('status', '!=', 'requested')->latest('processed_at')->limit(15)->get(),
            'commissions' => ReferralCommission::query()->with(['sponsor:id,name', 'referred:id,name', 'order:id,number'])
                ->when($status, fn ($q) => $q->where('status', $status))->latest('id')->paginate(30)->withQueryString(),
            'status' => $status,
            'totals' => ReferralCommission::query()->selectRaw('status, currency, sum(amount) as total, count(*) as n')->groupBy('status', 'currency')->get(),
            'referredCount' => Customer::query()->whereNotNull('referred_by')->count(),
            'topSponsors' => Customer::query()->whereHas('referrals')->withCount('referrals')->orderByDesc('referrals_count')->limit(5)->get(['id', 'name', 'email']),
            'operators' => Referrals::PAYOUT_OPERATORS,
        ]);
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'platform_referral_rate' => ['required', 'integer', 'min:0', 'max:100'],
            'platform_referral_hold_days' => ['required', 'integer', 'min:0', 'max:365'],
        ], [], ['platform_referral_rate' => __('taux'), 'platform_referral_hold_days' => __('délai')]);
        Setting::write($data);

        return back()->with('status', __('Réglages du parrainage enregistrés. Ils s’appliquent aux prochaines commissions.'));
    }

    public function cancel(Request $request, ReferralCommission $commission, Referrals $referrals)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']], [], ['reason' => __('motif')]);
        $referrals->cancel($commission, $data['reason'], $request->user('operator'));

        return back()->with('status', __('Commission annulée.'));
    }

    public function paid(Request $request, Payout $payout, Referrals $referrals)
    {
        $data = $request->validate(['transfer_reference' => ['required', 'string', 'max:120']], [], ['transfer_reference' => __('référence du transfert')]);
        $referrals->markPaid($payout, $request->user('operator'), $data['transfer_reference']);

        return back()->with('status', __('Retrait marqué comme envoyé. Le client est prévenu.'));
    }

    public function reject(Request $request, Payout $payout, Referrals $referrals)
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:255']], [], ['note' => __('motif')]);
        $referrals->reject($payout, $request->user('operator'), $data['note']);

        return back()->with('status', __('Retrait refusé ; le montant est revenu sur le solde du client.'));
    }
}
