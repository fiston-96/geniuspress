<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Image longue (capture d'un site, affiche) qui défile au survol dans un cadre de hauteur fixe. */
class ImageScrollWidget extends Widget
{
    public function type(): string { return 'image-scroll'; }

    public function label(): string { return __('Défilement d’image'); }

    public function rules(): array
    {
        return [
            'url' => 'url',
            'media_id' => 'int:0:2147483647',
            'alt' => 'string:300',
            'height' => 'int:100:1200@r',
            'duration' => 'int:1:30',
            'radius' => 'int:0:60',
            'frame' => 'bool',
            'link' => 'url',
        ];
    }

    public function translatable(): array { return ['alt']; }

    public function aiExcluded(): array { return ['url', 'link']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $img = $ctx->media($s['media_id'] ?? null)?->url ?? ($s['url'] ?? null);
        if (! $img) {
            return '';
        }
        $ctx->responsive($id, $s, 'height', fn ($v) => ['height' => $v.'px', '--gp-iscroll-h' => $v.'px'], ' .gp-iscroll');
        $ctx->rule($id, ['--gp-iscroll-time' => isset($s['duration']) ? $s['duration'].'s' : null, 'border-radius' => isset($s['radius']) ? $s['radius'].'px' : null], false, ' .gp-iscroll');
        $tag = ! empty($s['link']) ? 'a' : 'div';

        return "<{$tag} class=\"gp-iscroll".(! empty($s['frame']) ? ' gp-iscroll--frame' : '').'"'.($tag === 'a' ? ' href="'.e($s['link']).'"' : ' tabindex="0"').'>'
            .'<img src="'.e($img).'" alt="'.e($s['alt'] ?? '').'" loading="lazy">'."</{$tag}>";
    }
}
