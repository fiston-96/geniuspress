<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Tarif réel d'un modèle Claude, en USD par million de jetons, renseigné par l'exploitant. */
#[Fillable(['model', 'label', 'input_price', 'output_price', 'active'])]
class AiRate extends Model
{
    protected $table = 'platform_ai_rates';

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
