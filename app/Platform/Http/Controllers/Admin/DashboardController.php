<?php

namespace App\Platform\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Platform\Models\AiUsage;
use App\Platform\Models\Customer;
use App\Platform\Models\Order;
use App\Platform\Models\OrderItem;
use App\Platform\Models\Subscription;

/** Vue d'ensemble de la plateforme pour l'exploitant. */
class DashboardController extends Controller
{
    public function __invoke()
    {
        $month = now()->startOfMonth();

        return view('platform.admin.dashboard', [
            'customers' => Customer::query()->count(),
            'newCustomers' => Customer::query()->where('created_at', '>=', $month)->count(),
            'subscriptions' => Subscription::query()->with('plan')->whereIn('status', ['trialing', 'active', 'canceled'])->get()->filter->isActive(),
            'revenue' => Order::query()->where('status', 'paid')->where('paid_at', '>=', $month)
                ->selectRaw('currency, SUM(total) as total, COUNT(*) as count')->groupBy('currency')->get(),
            'ai' => AiUsage::query()->where('created_at', '>=', $month)
                ->selectRaw('COUNT(*) as calls, COALESCE(SUM(cost), 0) as cost, COALESCE(SUM(billed), 0) as billed, COALESCE(SUM(input_tokens), 0) as input, COALESCE(SUM(output_tokens), 0) as output')->first(),
            'failedItems' => OrderItem::query()->with('order.customer')->whereNull('fulfilled_at')->whereNotNull('error')
                ->whereHas('order', fn ($q) => $q->where('status', 'paid'))->latest('id')->limit(20)->get(),
            'orders' => Order::query()->with('customer')->latest('id')->limit(10)->get(),
        ]);
    }
}
