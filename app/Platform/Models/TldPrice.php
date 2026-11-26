<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Prix de vente d'une extension de domaine (« com », « cd »…). Une extension absente n'est pas vendue. */
#[Fillable(['tld', 'register_price', 'renew_price', 'currency', 'active', 'cost_register', 'cost_renew', 'cost_currency'])]
class TldPrice extends Model
{
    protected $table = 'platform_tld_prices';

    protected function casts(): array
    {
        return ['active' => 'boolean', 'register_price' => 'integer', 'renew_price' => 'integer', 'cost_register' => 'integer', 'cost_renew' => 'integer'];
    }
}
