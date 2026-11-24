<?php

namespace App\Models;

use App\Models\Concerns\HasBuilderContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Gabarit du thème, conçu dans le builder : en-tête, pied de page, article,
 * archive (blog, catégorie, étiquette), résultats de recherche, page 404.
 * Il s'applique selon ses conditions d'affichage (App\Site\ThemeLayouts).
 * « published » = actif sur le site.
 */
#[Fillable(['name', 'type', 'locale', 'status', 'conditions', 'priority', 'content', 'draft_content'])]
class Layout extends Model
{
    use HasBuilderContent, SoftDeletes;

    public const TYPES = ['header', 'footer', 'single', 'archive', 'search', 'not_found'];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'draft_content' => 'array',
            'conditions' => 'array',
            'priority' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Layout $layout) {
            $layout->content ??= self::emptyContent();
            $layout->conditions ??= [['mode' => 'include', 'target' => 'all']];
        });
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /** Libellés des types de gabarits, dans la langue de l'interface. */
    public static function typeLabels(): array
    {
        return [
            'header' => __('En-tête'),
            'footer' => __('Pied de page'),
            'single' => __('Article'),
            'archive' => __('Archive (blog, catégorie, étiquette)'),
            'search' => __('Résultats de recherche'),
            'not_found' => __('Page introuvable (404)'),
        ];
    }
}
