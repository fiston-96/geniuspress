<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Builder\Schema;
use App\Site\SiteContext;

/** Titre de l'archive (blog, catégorie, étiquette, recherche), avec sa description. */
class ArchiveTitleWidget extends ThemeWidget
{
    public function type(): string { return 'archive-title'; }

    public function label(): string { return __('Titre de l’archive'); }

    public function rules(): array
    {
        return [
            'tag' => 'enum:h1,h2,h3,p',
            'show_prefix' => 'bool',
            'show_description' => 'bool',
            'align' => 'enum:left,center,right@r',
            'color' => 'color',
            'font_size' => 'int:8:160@r',
        ] + Schema::TYPOGRAPHY;
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $site = $ctx->site;
        if (! $site) {
            return '';
        }
        $locale = $site->locale;
        $this->text($ctx, $id, $s, ' .gp-atitle__title');
        $ctx->responsive($id, $s, 'align', fn ($v) => ['text-align' => $v], ' .gp-atitle');
        $tag = $s['tag'] ?? 'h1';
        $prefix = match ($site->kind) {
            SiteContext::CATEGORY => __('Catégorie', [], $locale),
            SiteContext::TAG => __('Étiquette', [], $locale),
            default => null,
        };

        return '<div class="gp-atitle">'
            .(($s['show_prefix'] ?? true) && $prefix ? '<p class="gp-atitle__prefix">'.e($prefix).'</p>' : '')
            ."<{$tag} class=\"gp-atitle__title\">".e($site->title())."</{$tag}>"
            .(($s['show_description'] ?? true) && $site->term?->description ? '<p class="gp-atitle__desc">'.e($site->term->description).'</p>' : '')
            .'</div>';
    }
}
