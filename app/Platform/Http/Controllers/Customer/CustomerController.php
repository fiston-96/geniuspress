<?php

namespace App\Platform\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Platform\Models\Customer;
use Illuminate\Http\Request;

/** Base des pages de l'espace client : le client connecté (garde « customer »). */
abstract class CustomerController extends Controller
{
    protected function customer(Request $request): Customer
    {
        return $request->user('customer');
    }

    /** Refuse l'accès à un élément d'un autre client. */
    protected function own(Request $request, object $model): void
    {
        abort_unless((int) $model->customer_id === (int) $this->customer($request)->id, 404);
    }
}
