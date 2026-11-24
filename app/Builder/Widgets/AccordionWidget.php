<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Questions fréquentes (FAQ) ou contenu repliable. Fonctionne sans JavaScript. */
class AccordionWidget extends Widget
{
    public function type(): string { return 'accordion'; }

    public function label(): string { return __('Accordéon (FAQ)'); }

    public function rules(): array
    {
        return [
            'items' => 'list:40:title=string:300;html=html',
            'open_first' => 'bool',
            'style' => 'enum:boxed,plain',
            'title_color' => 'color',
            'accent' => 'color',
            'faq_schema' => 'bool',
        ];
    }

    public function translatable(): array { return ['items.*.title', 'items.*.html']; }

    public function aiHint(): ?string
    {
        return 'FAQ: list of questions and answers ("items": [{title, html}]). Write real, useful questions visitors ask; keep answers short; never invent prices, dates or contacts.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = $s['items'] ?? [];
        if (! $items) {
            return '';
        }
        $ctx->rule($id, ['color' => $s['title_color'] ?? null], false, ' summary');
        $ctx->rule($id, ['--gp-acc-accent' => $s['accent'] ?? null], false, ' .gp-acc');

        $html = '<div class="gp-acc gp-acc--'.e($s['style'] ?? 'boxed').'">';
        foreach ($items as $i => $item) {
            $open = $i === 0 && ! empty($s['open_first']) ? ' open' : '';
            $html .= "<details{$open}><summary>".e($item['title'] ?? '').'</summary><div class="gp-acc__body">'.($item['html'] ?? '').'</div></details>';
        }
        $html .= '</div>';

        // Données structurées FAQ (schema.org) pour les moteurs de recherche.
        if (! empty($s['faq_schema'])) {
            $data = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['title'] ?? '',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(strip_tags($item['html'] ?? ''))],
            ], $items)];
            $html .= '<script type="application/ld+json">'.json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).'</script>';
        }

        return $html;
    }
}
