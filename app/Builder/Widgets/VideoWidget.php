<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

class VideoWidget extends Widget
{
    public function type(): string { return 'video'; }

    public function label(): string { return 'Vidéo'; }

    public function rules(): array
    {
        return ['url' => 'url', 'aspect' => 'enum:16:9,4:3,1:1,9:16'];
    }

    public function aiExcluded(): array { return ['url']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $embed = self::embedUrl($s['url'] ?? '');
        if (! $embed) {
            return '';
        }
        $ratio = str_replace(':', ' / ', $s['aspect'] ?? '16:9');

        return '<div class="gp-video" style="aspect-ratio:'.e($ratio).'"><iframe src="'.e($embed).'" loading="lazy" allowfullscreen allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" title="Vidéo"></iframe></div>';
    }

    /** N'autorise que YouTube et Vimeo. */
    public static function embedUrl(string $url): ?string
    {
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,20})~', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1];
        }
        if (preg_match('~vimeo\.com/(?:video/)?(\d{4,15})~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }
}
