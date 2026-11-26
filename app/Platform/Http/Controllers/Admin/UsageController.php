<?php

namespace App\Platform\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Platform\Models\AiUsage;
use Illuminate\Http\Request;

/** Journal du relais IA : appels, jetons, coût réel et montant débité. */
class UsageController extends Controller
{
    public function index(Request $request)
    {
        $query = AiUsage::query()->with(['customer', 'license'])
            ->when($request->integer('customer'), fn ($q, $id) => $q->where('customer_id', $id))
            ->when(in_array($request->query('status'), ['ok', 'refused', 'error', 'blocked'], true), fn ($q) => $q->where('status', $request->query('status')));

        return view('platform.admin.usage', [
            'usages' => (clone $query)->latest('id')->paginate(50)->withQueryString(),
            'byModel' => (clone $query)->where('created_at', '>=', now()->startOfMonth())
                ->selectRaw('model, COUNT(*) as calls, SUM(input_tokens) as input, SUM(output_tokens) as output, SUM(cost) as cost, SUM(billed) as billed')
                ->groupBy('model')->get(),
        ]);
    }
}
