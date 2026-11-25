<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Prix d'un modèle de site payant (un modèle absent de la table est gratuit). */
#[Fillable(['template_id', 'price', 'currency', 'active'])]
class TemplatePrice extends Model
{
    protected $table = 'platform_template_prices';

    protected function casts(): array
    {
        return ['active' => 'boolean', 'price' => 'integer'];
    }
}
