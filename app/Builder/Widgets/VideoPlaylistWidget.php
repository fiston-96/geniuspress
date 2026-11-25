<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Playlist vidéo : un lecteur et la liste des vidéos (YouTube ou Vimeo) — prédications, formations, reportages. */
class VideoPlaylistWidget extends Widget
{
    public function type(): string { return 'video-playlist'; }

    public function label(): string { return __('Playlist vidéo'); }

    public function rules(): array
    {
        return [
            'items' => 'list:30:title=string:200;url=url;duration=string:20',
            'layout' => 'enum:side,below',
            'accent' => 'color',
            'list_bg' => 'color',
        ];
    }

    public function translatable(): array { return ['items.*.title']; }

    public function aiExcluded(): array { return ['items.*.url']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $videos = array_values(array_filter(array_map(fn ($i) => ($embed = VideoWidget::embedUrl((string) ($i['url'] ?? ''))) ? $i + ['embed' => $embed] : null, $s['items'] ?? [])));
        if (! $videos) {
            return '';
        }
        $ctx->rule($id, ['--gp-vp-accent' => $s['accent'] ?? null, '--gp-vp-bg' => $s['list_bg'] ?? null], false, ' .gp-vplay');

        $html = '<div class="gp-vplay gp-vplay--'.e($s['layout'] ?? 'side').'" data-gp-playlist>'
            .'<div class="gp-vplay__player"><iframe src="'.e($videos[0]['embed']).'" title="'.e($videos[0]['title'] ?? '').'" loading="lazy" allowfullscreen allow="encrypted-media; picture-in-picture"></iframe></div><ol class="gp-vplay__list">';
        foreach ($videos as $i => $v) {
            $html .= '<li><a href="'.e($v['url']).'" data-embed="'.e($v['embed']).'"'.($i === 0 ? ' aria-current="true"' : '').'>'
                .'<span class="gp-vplay__num">'.($i + 1).'</span><span class="gp-vplay__title">'.e($v['title'] ?? '').'</span>'
                .(! empty($v['duration']) ? '<span class="gp-vplay__time">'.e($v['duration']).'</span>' : '').'</a></li>';
        }

        return $html.'</ol></div>';
    }
}
