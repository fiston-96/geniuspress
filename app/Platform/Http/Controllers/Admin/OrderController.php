<?php

namespace App\Platform\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Platform\Models\Domain;
use App\Platform\Models\Order;
use App\Platform\OrderFulfiller;
use Illuminate\Http\Request;

/** Commandes, paiements et domaines vendus. */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        return view('platform.admin.orders.index', [
            'status' => $status,
            'orders' => Order::query()->with(['customer', 'items'])
                ->when(in_array($status, ['pending', 'paid', 'failed', 'canceled'], true), fn ($q) => $q->where('status', $status))
                ->latest('id')->paginate(30)->withQueryString(),
        ]);
    }

    public function show(Order $order)
    {
        return view('platform.admin.orders.show', ['order' => $order->load(['customer', 'items', 'payments'])]);
    }

    /** Relance la livraison des lignes en échec d'une commande payée. */
    public function deliver(Order $order, OrderFulfiller $fulfiller)
    {
        $fulfiller->deliver($order);
        $failed = $order->items()->whereNull('fulfilled_at')->count();

        return back()->with('status', $failed ? __('Livraison relancée : :count ligne(s) toujours en échec.', ['count' => $failed]) : __('Commande livrée.'));
    }

    public function domains()
    {
        return view('platform.admin.domains', [
            'domains' => Domain::query()->with('customer')->orderBy('expires_at')->paginate(50),
        ]);
    }
}
