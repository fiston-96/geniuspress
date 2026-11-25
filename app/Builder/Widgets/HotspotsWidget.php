<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Image avec des points interactifs : une bulle s'ouvre au survol ou au toucher. */
class HotspotsWidget extends Widget
{
    public function type(): string { return 'hotspots'; }

    public function label(): string { return __('Points interactifs'); }

    public function rules(): array
    {
        return [
            'url' => 'url',
            'media_id' => 'int:0:2147483647',
            'alt' => 'string:300',
            'spots' => 'list:30:x=int:0:100;y=int:0:100;title=string:120;text=string:400;link=url',
            'dot_color' => 'color',
            'radius' => 'int:0:80',
        ];
    }

    public function translatable(): array { return ['alt', 'spots.*.title', 'spots.*.text']; }

    public function aiExcluded(): array { return ['url', 'spots.*.x', 'spots.*.y', 'spots.*.link']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $img = $ctx->media($s['media_id'] ?? null)?->url ?? ($s['url'] ?? null);
        if (! $img) {
            return '';
        }
        $ctx->rule($id, ['--gp-spot' => $s['dot_color'] ?? null], false, ' .gp-spots');
        $ctx->rule($id, ['border-radius' => isset($s['radius']) ? $s['radius'].'px' : null], false, ' .gp-spots__img');

        $html = '<div class="gp-spots"><img class="gp-spots__img" src="'.e($img).'" alt="'.e($s['alt'] ?? '').'" loading="lazy">';
        foreach ($s['spots'] ?? [] as $i => $spot) {
            $x = $spot['x'] ?? 50;
            $y = $spot['y'] ?? 50;
            $side = $x > 60 ? ' gp-spot--left' : '';
            $title = e($spot['title'] ?? '');
            $html .= '<div class="gp-spot'.$side.'" style="left:'.$x.'%;top:'.$y.'%" tabindex="0" aria-label="'.$title.'">'
                .'<span class="gp-spot__dot">'.($i + 1).'</span><span class="gp-spot__tip">'
                .($title !== '' ? '<strong>'.$title.'</strong>' : '').(! empty($spot['text']) ? '<span>'.e($spot['text']).'</span>' : '')
                .(! empty($spot['link']) ? '<a href="'.e($spot['link']).'">→</a>' : '').'</span></div>';
        }

        return $html.'</div>';
    }
}
