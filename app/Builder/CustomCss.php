<?php

namespace App\Builder;

/**
 * CSS personnalisé d'un élément (réglage « custom_css », onglet Avancé).
 *
 * Le mot « selector » désigne l'élément lui-même : « selector:hover { … } »,
 * « selector .gp-btn { … } ». Une règle qui ne le contient pas est limitée à
 * l'élément (« h3 { … } » devient « #gp-… h3 { … } ») et de simples déclarations
 * (« color: red; ») s'appliquent à l'élément. Seuls @media et @supports sont
 * acceptés autour des règles.
 *
 * Miroir exact de cleanCustomCss() et scopeCustomCss() dans resources/js/builder/style.js.
 */
final class CustomCss
{
    public const MAX = 10000;

    /** Instructions refusées : chargements externes, scripts d'anciens navigateurs, échappements. */
    private const FORBIDDEN = '/[<\\\\]|@import|@charset|@namespace|expression\s*\(|javascript:|vbscript:|behavior\s*:|-moz-binding/i';

    /** CSS accepté (commentaires retirés), ou null s'il contient une instruction interdite ou des accolades déséquilibrées. */
    public static function clean(mixed $css): ?string
    {
        if (! is_string($css)) {
            return null;
        }
        $css = trim((string) preg_replace('~/\*.*?\*/~s', '', $css));
        if ($css === '' || strlen($css) > self::MAX || preg_match(self::FORBIDDEN, $css)) {
            return null;
        }
        $depth = 0;
        foreach (str_split($css) as $char) {
            $depth += $char === '{' ? 1 : ($char === '}' ? -1 : 0);
            if ($depth < 0 || $depth > 2) {
                return null;
            }
        }

        return $depth === 0 ? $css : null;
    }

    /** Règles limitées à l'élément désigné par $selector (« #gp-abc123 »). */
    public static function scope(string $css, string $selector, bool $nested = false): string
    {
        if (! $nested && ! str_contains($css, '{')) {
            return $selector.'{'.trim($css).'}';
        }
        $out = '';
        $i = 0;
        $length = strlen($css);
        while ($i < $length) {
            $open = strpos($css, '{', $i);
            if ($open === false) {
                break;
            }
            // Ce qui précède la règle (déclarations sans sélecteur…) est ignoré.
            $prelude = trim((string) preg_replace('/^.*[;}]/s', '', substr($css, $i, $open - $i)));
            $depth = 1;
            $j = $open + 1;
            while ($j < $length && $depth > 0) {
                $depth += $css[$j] === '{' ? 1 : ($css[$j] === '}' ? -1 : 0);
                $j++;
            }
            $body = substr($css, $open + 1, $j - $open - 2);
            $i = $j;

            if (! $nested && preg_match('/^@(media|supports)\b/i', $prelude)) {
                $inner = self::scope($body, $selector, true);
                if ($inner !== '') {
                    $out .= $prelude.'{'.$inner.'}';
                }

                continue;
            }
            if ($prelude === '' || str_starts_with($prelude, '@') || str_contains($body, '{')) {
                continue;
            }
            $selectors = [];
            foreach (explode(',', $prelude) as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }
                $selectors[] = preg_match('/(?<![\w-])selector(?![\w-])/', $part)
                    ? preg_replace('/(?<![\w-])selector(?![\w-])/', $selector, $part)
                    : $selector.' '.$part;
            }
            if ($selectors) {
                $out .= implode(',', $selectors).'{'.trim($body).'}';
            }
        }

        return $out;
    }
}
