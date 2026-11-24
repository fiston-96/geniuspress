<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Liste de liens : sommaire de documentation, menu dans une page, liens utiles. */
class LinksWidget extends Widget
{
    public function type(): string { return 'links'; }

    public function label(): string { return __('Liste de liens'); }

    public function rules(): array
    {
        return [
            'title' => 'string:120',
            'items' => 'links:40',
            'layout' => 'enum:vertical,horizontal',
            'style' => 'enum:plain,boxed',
            'color' => 'color',
        ];
    }

    public function translatable(): array { return ['title']; }

    public function aiHint(): ?string
    {
        return 'List of links ("items": [{label, url}]) with an optional title, e.g. a table of contents or useful links. Only link to pages of this site or anchors.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = $s['items'] ?? [];
        if (! $items) {
            return '';
        }
        $ctx->rule($id, ['--gp-links-color' => $s['color'] ?? null]);

        $current = '/'.trim(request()->path(), '/');
        $layout = ($s['layout'] ?? 'vertical') === 'horizontal' ? 'horizontal' : 'vertical';
        $style = ($s['style'] ?? 'plain') === 'boxed' ? ' gp-links--boxed' : '';

        $html = '<nav class="gp-links gp-links--'.$layout.$style.'"'.(isset($s['title']) ? ' aria-label="'.e($s['title']).'"' : '').'>';
        if (isset($s['title'])) {
            $html .= '<p class="gp-links__title">'.e($s['title']).'</p>';
        }
        $html .= '<ul>';
        foreach ($items as $item) {
            $path = rtrim(parse_url($item['url'], PHP_URL_PATH) ?: '', '/') ?: '/';
            $active = ! str_starts_with($item['url'], '#') && ! str_contains($item['url'], '://') && $path === (rtrim($current, '/') ?: '/');
            $html .= '<li><a href="'.e($item['url']).'"'.($active ? ' aria-current="page"' : '').'>'.e($item['label']).'</a></li>';
        }

        return $html.'</ul></nav>';
    }
}
