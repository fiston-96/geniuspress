<?php

namespace App\Templates;

use App\Models\Media;

/** Image d'un modèle dans sa démonstration : servie depuis l'archive du modèle, jamais enregistrée. */
final class DemoMedia extends Media
{
    public function getUrlAttribute(): string
    {
        return (string) ($this->attributes['demo_url'] ?? '');
    }
}
