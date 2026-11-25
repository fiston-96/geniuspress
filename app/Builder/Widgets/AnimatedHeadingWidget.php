<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Builder\Schema;

/**
 * Titre animé : machine à écrire, mots qui se succèdent, ou mot surligné par un trait
 * dessiné (cercle, soulignement…). Sans JavaScript, le premier mot s'affiche normalement ;
 * les lecteurs d'écran lisent toujours la phrase complète avec le premier mot.
 */
class AnimatedHeadingWidget extends Widget
{
    /** Tracés des surlignages, dans un cadre de 500 × 150 étiré sous le mot. */
    public const SHAPES = [
        'circle' => 'M250,8C385,6 497,38 494,77C491,117 382,144 250,143C112,142 6,118 7,76C8,38 112,12 268,17',
        'underline' => 'M6,134C150,121 350,120 494,131',
        'curly' => 'M4,138C40,112 80,112 120,138C160,162 200,162 240,136C280,110 320,110 360,137C400,162 440,162 496,128',
        'double' => 'M6,126C170,116 330,116 494,124M32,146C190,136 320,136 468,144',
        'strike' => 'M6,82C170,72 330,72 494,78',
        'diagonal' => 'M10,140L490,14',
    ];

    public function type(): string { return 'animated-heading'; }

    public function label(): string { return __('Titre animé'); }

    public function rules(): array
    {
        return [
            'before' => 'string:300',
            'words' => 'string:600',
            'after' => 'string:300',
            'effect' => 'enum:typing,rotate,highlight',
            'shape' => 'enum:circle,underline,curly,double,strike,diagonal',
            'tag' => 'enum:h1,h2,h3,h4,p',
            'speed' => 'int:1:10',
            'pause' => 'int:500:10000',
            'loop' => 'bool',
            'align' => 'enum:left,center,right@r',
            'color' => 'color',
            'words_color' => 'color',
            'shape_color' => 'color',
            'shape_width' => 'int:1:30',
            'font_size' => 'int:8:160@r',
        ] + Schema::TYPOGRAPHY;
    }

    public function translatable(): array { return ['before', 'words', 'after']; }

    public function aiHint(): ?string
    {
        return 'Animated hero heading: "before" text, then "words" (one per line) shown one after another with effect "typing" or "rotate", or one word underlined with a drawn shape (effect "highlight", shape circle/underline/curly), then "after". Example: before "Nous accompagnons", words "les écoles\nles ONG\nles entreprises".';
    }

    /** Mots à faire défiler : un par ligne, au plus 10. */
    public static function words(?string $text): array
    {
        return array_slice(array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text)), fn ($w) => $w !== '')), 0, 10);
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $words = self::words($s['words'] ?? null) ?: [''];
        $effect = $s['effect'] ?? 'typing';
        $tag = $s['tag'] ?? 'h2';

        $ctx->rule($id, ['color' => $s['color'] ?? null, 'font-family' => isset($s['font_family']) ? null : 'var(--gp-heading-font)'], false, ' > *');
        $ctx->responsive($id, $s, 'align', fn ($v) => ['text-align' => $v], ' > *');
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px'], ' > *');
        $ctx->typography($id, $s, ' > *');
        $ctx->rule($id, ['color' => $s['words_color'] ?? null], false, ' .gp-ah__words');
        $ctx->rule($id, ['color' => $s['shape_color'] ?? null, 'stroke-width' => $s['shape_width'] ?? null], false, ' .gp-ah__shape');

        if ($effect === 'highlight') {
            $path = self::SHAPES[$s['shape'] ?? 'circle'];
            $inner = '<span class="gp-ah__hl">'.e($words[0]).'<svg class="gp-ah__shape" viewBox="0 0 500 150" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="'.$path.'" pathLength="1"/></svg></span>';
        } else {
            $inner = implode('', array_map(fn ($w, $i) => '<span class="gp-ah__word'.($i === 0 ? ' is-on' : '').'">'.e($w).'</span>', $words, array_keys($words)));
        }

        $before = ($s['before'] ?? '') !== '' ? e($s['before']).' ' : '';
        $after = ($s['after'] ?? '') !== '' ? ' '.e($s['after']) : '';
        $sentence = trim(($s['before'] ?? '').' '.$words[0].' '.($s['after'] ?? ''));
        $data = ' data-gp-ah data-pause="'.($s['pause'] ?? 2500).'" data-speed="'.($s['speed'] ?? 5).'"'.(! empty($s['loop']) ? ' data-loop' : '');

        return "<{$tag} class=\"gp-ah gp-ah--".e($effect)."\"{$data}>"
            .'<span class="gp-sr-only">'.e($sentence).'</span>'
            .'<span aria-hidden="true">'.$before.'<span class="gp-ah__words">'.$inner.'</span>'.$after.'</span>'
            ."</{$tag}>";
    }
}
