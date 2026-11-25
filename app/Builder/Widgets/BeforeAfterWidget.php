<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Avant / après : deux images superposées et une poignée pour comparer (rénovation, chantier, transformation). */
class BeforeAfterWidget extends Widget
{
    public function type(): string { return 'before-after'; }

    public function label(): string { return __('Avant / après'); }

    public function rules(): array
    {
        return [
            'before_url' => 'url',
            'before_media_id' => 'int:0:2147483647',
            'after_url' => 'url',
            'after_media_id' => 'int:0:2147483647',
            'before_label' => 'string:40',
            'after_label' => 'string:40',
            'alt' => 'string:300',
            'start' => 'int:0:100',
            'radius' => 'int:0:60',
            'handle_color' => 'color',
        ];
    }

    public function translatable(): array { return ['before_label', 'after_label', 'alt']; }

    public function aiExcluded(): array { return ['before_url', 'after_url']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $before = $ctx->media($s['before_media_id'] ?? null)?->url ?? ($s['before_url'] ?? null);
        $after = $ctx->media($s['after_media_id'] ?? null)?->url ?? ($s['after_url'] ?? null);
        if (! $before || ! $after) {
            return '';
        }
        $start = $s['start'] ?? 50;
        $ctx->rule($id, ['border-radius' => isset($s['radius']) ? $s['radius'].'px' : null, '--gp-ba-handle' => $s['handle_color'] ?? null], false, ' .gp-ba');
        $locale = $ctx->site?->locale;

        return '<div class="gp-ba" data-gp-ba style="--gp-ba:'.$start.'%">'
            .'<img class="gp-ba__after" src="'.e($after).'" alt="'.e($s['alt'] ?? '').'" loading="lazy">'
            .'<img class="gp-ba__before" src="'.e($before).'" alt="" loading="lazy">'
            .'<span class="gp-ba__label gp-ba__label--before">'.e($s['before_label'] ?? __('Avant', [], $locale)).'</span>'
            .'<span class="gp-ba__label gp-ba__label--after">'.e($s['after_label'] ?? __('Après', [], $locale)).'</span>'
            .'<span class="gp-ba__handle" aria-hidden="true"></span>'
            .'<input class="gp-ba__range" type="range" min="0" max="100" value="'.$start.'" aria-label="'.e(__('Comparer avant et après', [], $locale)).'">'
            .'</div>';
    }
}
