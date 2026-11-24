<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

class TestimonialWidget extends Widget
{
    public function type(): string { return 'testimonial'; }

    public function label(): string { return __('Témoignage'); }

    public function rules(): array
    {
        return [
            'text' => 'string:1500',
            'name' => 'string:120',
            'role' => 'string:160',
            'url' => 'url',
            'media_id' => 'int:0:2147483647',
            'rating' => 'int:0:5',
            'style' => 'enum:plain,card',
            'align' => 'enum:left,center,right@r',
            'text_size' => 'int:10:60@r',
            'accent' => 'color',
        ];
    }

    public function translatable(): array { return ['text', 'role']; }

    public function aiExcluded(): array { return ['url', 'media_id']; }

    public function aiHint(): ?string
    {
        return 'Testimonial quote with the person\'s name and role. Never invent real people: use clear placeholders such as "[Prénom Nom]" and "[Fonction]" for the user to replace.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $media = $ctx->media($s['media_id'] ?? null);
        $photo = $media?->url ?? ($s['url'] ?? null);
        $ctx->responsive($id, $s, 'align', fn ($v) => ['text-align' => $v], ' .gp-testi');
        $ctx->responsive($id, $s, 'text_size', fn ($v) => ['font-size' => $v.'px'], ' blockquote');
        $ctx->rule($id, ['--gp-accent-local' => $s['accent'] ?? null], false, ' .gp-testi');

        $rating = (int) ($s['rating'] ?? 0);
        $html = '<figure class="gp-testi gp-testi--'.e($s['style'] ?? 'card').'">';
        if ($rating > 0) {
            $html .= '<div class="gp-testi__stars" role="img" aria-label="'.e(__(':n sur 5', ['n' => $rating])).'">'.str_repeat('★', $rating).'<span class="gp-testi__off">'.str_repeat('★', 5 - $rating).'</span></div>';
        }
        $html .= '<blockquote>'.nl2br(e($s['text'] ?? '')).'</blockquote><figcaption>';
        if ($photo) {
            $html .= '<img src="'.e($photo).'" alt="" loading="lazy" decoding="async">';
        }
        $html .= '<span><span class="gp-testi__name">'.e($s['name'] ?? '').'</span>';
        if (isset($s['role'])) {
            $html .= '<span class="gp-testi__role">'.e($s['role']).'</span>';
        }

        return $html.'</span></figcaption></figure>';
    }
}
