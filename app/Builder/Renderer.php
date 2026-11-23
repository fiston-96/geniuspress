<?php

namespace App\Builder;

use App\Models\Media;

/**
 * Transforme l'arbre JSON d'une page en HTML + CSS.
 * Chaque élément reçoit un id (#gp-xxxx) et ses styles sont collectés dans
 * une feuille unique, avec des surcharges mobiles (< 768px).
 */
class Renderer
{
    private array $css = [];
    private array $cssMobile = [];
    private array $media = [];

    public function __construct(private ContentSanitizer $sanitizer) {}

    /** @return array{html: string, css: string} */
    public function render(mixed $content): array
    {
        $this->css = $this->cssMobile = [];
        $tree = $this->sanitizer->sanitize($content);
        $this->preloadMedia($tree);

        $html = '';
        foreach ($tree['sections'] as $section) {
            $html .= $this->section($section);
        }

        $css = implode("\n", $this->css);
        if ($this->cssMobile) {
            $css .= "\n@media (max-width: 767px) {\n".implode("\n", $this->cssMobile)."\n}";
        }

        return ['html' => $html, 'css' => $css];
    }

    private function preloadMedia(array $tree): void
    {
        $ids = [];
        foreach ($tree['sections'] as $s) {
            foreach ($s['columns'] as $c) {
                foreach ($c['widgets'] as $w) {
                    if ($w['type'] === 'image' && ! empty($w['settings']['media_id'])) {
                        $ids[] = $w['settings']['media_id'];
                    }
                }
            }
        }
        $this->media = $ids ? Media::query()->whereIn('id', $ids)->get()->keyBy('id')->all() : [];
    }

    private function rule(string $id, array $props, bool $mobile = false, string $suffix = ''): void
    {
        $props = array_filter($props, fn ($v) => $v !== null && $v !== '');
        if (! $props) {
            return;
        }
        $body = implode(';', array_map(fn ($k, $v) => "$k:$v", array_keys($props), $props));
        $line = "#gp-{$id}{$suffix}{{$body}}";
        $mobile ? $this->cssMobile[] = $line : $this->css[] = $line;
    }

    private static function px(?int $v): ?string
    {
        return $v === null ? null : $v.'px';
    }

    private function section(array $s): string
    {
        $o = $s['settings'];
        $id = $s['id'];

        $bg = isset($o['background_image']) ? 'url("'.$o['background_image'].'") center/cover no-repeat' : null;
        $this->rule($id, [
            'background-color' => $o['background_color'] ?? null,
            'background' => $bg ? $bg.(isset($o['background_color']) ? ' '.$o['background_color'] : '') : null,
            'color' => $o['text_color'] ?? null,
            'padding-top' => self::px($o['padding_top'] ?? 64),
            'padding-bottom' => self::px($o['padding_bottom'] ?? 64),
            'min-height' => isset($o['min_height']) ? $o['min_height'].'px' : null,
        ]);
        $this->rule($id, [
            'padding-top' => self::px($o['padding_top_mobile'] ?? null),
            'padding-bottom' => self::px($o['padding_bottom_mobile'] ?? null),
        ], true);

        $full = ($o['content_width'] ?? 'boxed') === 'full';
        $this->rule($id, [
            'max-width' => $full ? 'none' : (($o['max_width'] ?? 1140).'px'),
            'gap' => self::px($o['gap'] ?? 24),
            'align-items' => match ($o['vertical_align'] ?? 'stretch') {
                'start' => 'flex-start', 'center' => 'center', 'end' => 'flex-end', default => 'stretch',
            },
        ], false, ' > .gp-row');

        $overlay = '';
        if (isset($o['overlay_color'])) {
            $this->rule($id, [
                'background' => $o['overlay_color'],
                'opacity' => (($o['overlay_opacity'] ?? 50) / 100),
            ], false, ' > .gp-overlay');
            $overlay = '<div class="gp-overlay"></div>';
        }

        $cols = '';
        foreach ($s['columns'] as $c) {
            $cols .= $this->column($c, count($s['columns']), $o['gap'] ?? 24);
        }
        $htmlId = isset($o['anchor']) ? e($o['anchor']) : 'gp-'.$id;
        // L'ancre remplace l'id : on garde la classe pour le CSS.
        if (isset($o['anchor'])) {
            $this->css = array_map(fn ($l) => str_replace("#gp-{$id}", "#{$htmlId}", $l), $this->css);
            $this->cssMobile = array_map(fn ($l) => str_replace("#gp-{$id}", "#{$htmlId}", $l), $this->cssMobile);
        }

        return "<section id=\"{$htmlId}\" class=\"gp-section\">{$overlay}<div class=\"gp-row\">{$cols}</div></section>\n";
    }

    private function column(array $c, int $count, int $gap): string
    {
        $o = $c['settings'];
        $id = $c['id'];
        $width = $o['width'] ?? round(100 / max(1, $count), 4);
        $share = round($gap * ($count - 1) / max(1, $count), 2);

        $this->rule($id, [
            'flex' => "0 1 calc({$width}% - {$share}px)",
            'padding' => self::px($o['padding'] ?? null),
            'background-color' => $o['background_color'] ?? null,
            'border-radius' => self::px($o['border_radius'] ?? null),
            'justify-content' => match ($o['vertical_align'] ?? null) {
                'center' => 'center', 'end' => 'flex-end', 'start' => 'flex-start', default => null,
            },
        ]);

        $widgets = '';
        foreach ($c['widgets'] as $w) {
            $widgets .= $this->widget($w);
        }

        return "<div id=\"gp-{$id}\" class=\"gp-column\">{$widgets}</div>";
    }

    private function widget(array $w): string
    {
        $o = $w['settings'];
        $id = $w['id'];
        $classes = 'gp-widget gp-'.$w['type'];
        if (! empty($o['hide_desktop'])) {
            $classes .= ' gp-hide-desktop';
        }
        if (! empty($o['hide_mobile'])) {
            $classes .= ' gp-hide-mobile';
        }
        $this->rule($id, ['margin-bottom' => self::px($o['margin_bottom'] ?? null)]);

        $inner = match ($w['type']) {
            'heading' => $this->heading($id, $o),
            'text' => $this->text($id, $o),
            'image' => $this->image($id, $o),
            'button' => $this->button($id, $o),
            'spacer' => $this->spacer($id, $o),
            'divider' => $this->divider($id, $o),
            'video' => $this->video($o),
            default => '',
        };

        return "<div id=\"gp-{$id}\" class=\"{$classes}\">{$inner}</div>";
    }

    private function heading(string $id, array $o): string
    {
        $tag = $o['tag'] ?? 'h2';
        $this->rule($id, [
            'text-align' => $o['align'] ?? null,
            'color' => $o['color'] ?? null,
            'font-size' => self::px($o['font_size'] ?? null),
            'font-weight' => $o['font_weight'] ?? null,
        ], false, ' > *');
        $this->rule($id, ['font-size' => self::px($o['font_size_mobile'] ?? null)], true, ' > *');

        return "<{$tag}>".e($o['text'] ?? '')."</{$tag}>";
    }

    private function text(string $id, array $o): string
    {
        $this->rule($id, [
            'text-align' => $o['align'] ?? null,
            'color' => $o['color'] ?? null,
            'font-size' => self::px($o['font_size'] ?? null),
        ]);

        // Déjà nettoyé par ContentSanitizer (liste blanche de balises).
        return $o['html'] ?? '';
    }

    private function image(string $id, array $o): string
    {
        $media = isset($o['media_id']) ? ($this->media[$o['media_id']] ?? null) : null;
        $src = $media?->url ?? ($o['url'] ?? null);
        if (! $src) {
            return '';
        }
        $this->rule($id, ['text-align' => $o['align'] ?? 'center']);
        $this->rule($id, [
            'width' => isset($o['width']) ? $o['width'].'%' : null,
            'border-radius' => self::px($o['border_radius'] ?? null),
        ], false, ' img');

        $alt = e($o['alt'] ?? $media?->alt ?? '');
        $size = $media?->width ? " width=\"{$media->width}\" height=\"{$media->height}\"" : '';
        $img = '<img src="'.e($src).'" alt="'.$alt.'"'.$size.' loading="lazy" decoding="async">';

        return isset($o['link']) ? '<a href="'.e($o['link']).'">'.$img.'</a>' : $img;
    }

    private function button(string $id, array $o): string
    {
        $variant = $o['variant'] ?? 'filled';
        $bg = $o['bg_color'] ?? 'var(--gp-primary)';
        $fg = $o['text_color'] ?? ($variant === 'filled' ? '#ffffff' : $bg);
        $align = $o['align'] ?? 'left';

        $this->rule($id, ['text-align' => $align === 'full' ? null : $align]);
        $this->rule($id, [
            'background' => $variant === 'filled' ? $bg : 'transparent',
            'border-color' => $bg,
            'color' => $fg,
            'border-radius' => self::px($o['border_radius'] ?? null),
            'display' => $align === 'full' ? 'block' : null,
        ], false, ' .gp-btn');

        $target = ! empty($o['new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $size = 'gp-btn-'.($o['size'] ?? 'md');

        return '<a class="gp-btn '.$size.'" href="'.e($o['url'] ?? '#').'"'.$target.'>'.e($o['text'] ?? 'Bouton').'</a>';
    }

    private function spacer(string $id, array $o): string
    {
        $this->rule($id, ['height' => self::px($o['height'] ?? 40)]);
        $this->rule($id, ['height' => self::px($o['height_mobile'] ?? null)], true);

        return '';
    }

    private function divider(string $id, array $o): string
    {
        $margin = match ($o['align'] ?? 'center') {
            'left' => '0 auto 0 0', 'right' => '0 0 0 auto', default => '0 auto',
        };
        $this->rule($id, [
            'border-top' => ($o['thickness'] ?? 1).'px '.($o['style'] ?? 'solid').' '.($o['color'] ?? '#e2e8f0'),
            'width' => ($o['width'] ?? 100).'%',
            'margin' => $margin,
        ], false, ' hr');

        return '<hr>';
    }

    private function video(array $o): string
    {
        $embed = self::embedUrl($o['url'] ?? '');
        if (! $embed) {
            return '';
        }
        $ratio = str_replace(':', ' / ', $o['aspect'] ?? '16:9');

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
