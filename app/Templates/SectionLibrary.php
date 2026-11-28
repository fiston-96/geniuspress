<?php

namespace App\Templates;

use App\Builder\Renderer;
use App\Models\Media;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Bibliothèque de sections du builder : des sections prêtes à l'emploi tirées des packs
 * de secteur (textes réalistes en français et en anglais, photos libres de droits),
 * classées par catégorie et sans doublon de structure. Toutes leurs couleurs sont
 * globales : insérées dans une page, elles prennent les couleurs du site.
 *
 * Les photos ne sont copiées dans la médiathèque du site qu'à l'insertion (insert()).
 * La liste est calculée une fois puis gardée en cache (clé liée aux fichiers des packs).
 */
final class SectionLibrary
{
    /** Packs utilisés, variés par secteur, chacun avec une variante d'en-tête et de cartes. */
    private const PACKS = [
        ['ong', 'split', 'shadow'], ['restaurant', 'image', 'flat'], ['saas', 'gradient', 'border'], ['ecole', 'center', 'tinted'],
        ['hotel', 'image', 'shadow'], ['avocats', 'split', 'border'], ['fitness', 'gradient', 'flat'], ['agence-marketing', 'center', 'shadow'],
        ['immobilier', 'split', 'tinted'], ['photographe', 'image', 'border'], ['eglise', 'center', 'flat'], ['medias-magazine', 'split', 'shadow'],
    ];

    /** Nombre maximal de sections par catégorie et par langue. */
    private const PER_CATEGORY = 12;

    public const CATEGORIES = ['hero', 'header', 'services', 'content', 'stats', 'pricing', 'testimonials', 'team', 'faq', 'gallery', 'cta', 'contact', 'blog'];

    /** Libellés des catégories, dans la langue de l'interface. */
    public static function categoryLabels(): array
    {
        return [
            'hero' => __('En-têtes d’accueil'), 'header' => __('Titres de page'), 'services' => __('Services'), 'content' => __('Texte et image'),
            'stats' => __('Chiffres clés'), 'pricing' => __('Tarifs'), 'testimonials' => __('Témoignages'), 'team' => __('Équipe'),
            'faq' => __('Questions fréquentes'), 'gallery' => __('Galerie'), 'cta' => __('Appels à l’action'), 'contact' => __('Contact'), 'blog' => __('Articles'),
        ];
    }

    /**
     * Sections d'une langue (fr ou en ; les autres langues reçoivent l'anglais).
     *
     * @return list<array{id: string, category: string, name: string, node: array, slots: array}>
     */
    public static function all(string $locale): array
    {
        $lang = $locale === 'fr' ? 'fr' : 'en';

        return Cache::rememberForever('gp-sections-'.$lang.'-'.self::version(), fn () => self::build($lang));
    }

    /** @return array{id: string, category: string, name: string, node: array, slots: array}|null */
    public static function find(string $id, string $locale): ?array
    {
        foreach (self::all($locale) as $section) {
            if ($section['id'] === $id) {
                return $section;
            }
        }

        return null;
    }

    /** Liste envoyée au builder (sans le contenu, chargé à l'insertion). */
    public static function index(string $locale): array
    {
        return array_map(fn ($s) => ['id' => $s['id'], 'category' => $s['category'], 'name' => $s['name']], self::all($locale));
    }

    public static function version(): string
    {
        $files = [__FILE__, app_path('Templates/Blocks.php'), app_path('Templates/MediaBlocks.php'), app_path('Templates/Kit.php')];
        foreach (self::PACKS as [$pack]) {
            $files[] = base_path('templates/sectors/'.$pack.'.php');
        }

        return substr(md5(implode('|', array_map(fn ($f) => $f.@filemtime($f), $files))), 0, 12);
    }

    private static function build(string $lang): array
    {
        $builder = new TemplateBuilder(base_path('templates/sectors'));
        $byCategory = array_fill_keys(self::CATEGORIES, []);
        $seen = [];
        foreach (self::PACKS as [$name, $hero, $cards]) {
            Kit::reset();
            $theme = new Theme(colors: ['primary' => '#2563eb', 'secondary' => '#0f172a', 'accent' => '#f59e0b', 'light' => '#f1f5f9', 'text' => '#0f172a', 'muted' => '#475569'], hero: $hero, cards: $cards);
            try {
                $site = ($builder->pack($name)['build'])($lang, $theme, []);
            } catch (\Throwable $e) {
                report($e);

                continue;
            }
            $slots = Kit::slots();
            foreach ($site['pages'] as $page) {
                foreach (array_values($page['sections']) as $i => $node) {
                    $category = self::category($node, $i === 0 ? (! empty($page['home']) ? 'hero' : 'header') : null);
                    // Même structure mais autre style (cartes, en-tête) : une variante utile, gardée.
                    $signature = md5($category.$cards.($i === 0 ? $hero : '').json_encode(self::shape($node)));
                    if (isset($seen[$signature]) || count($byCategory[$category]) >= self::PER_CATEGORY) {
                        continue;
                    }
                    $seen[$signature] = true;
                    $used = self::slotsOf($node, $slots);
                    $byCategory[$category][] = [
                        'id' => $name.'-'.($page['key'] ?? 'p').'-'.$i,
                        'category' => $category,
                        'name' => self::name($node) ?? self::categoryLabels()[$category],
                        'node' => $node,
                        'slots' => $used,
                    ];
                }
            }
        }

        return array_merge(...array_values($byCategory));
    }

    /** Catégorie d'une section d'après ses widgets (ou sa place dans la page). */
    private static function category(array $node, ?string $first): string
    {
        if ($first) {
            return $first;
        }
        $types = [];
        Renderer::walk([$node], function ($n) use (&$types) {
            $types[$n['type']] = ($types[$n['type']] ?? 0) + 1;
        });
        $has = fn (string ...$t) => (bool) array_intersect($t, array_keys($types));

        return match (true) {
            $has('pricing', 'price-list') => 'pricing',
            $has('accordion') => 'faq',
            $has('testimonial', 'testimonial-carousel') => 'testimonials',
            $has('team-member') => 'team',
            $has('contact-form', 'form', 'map', 'business-hours') => 'contact',
            $has('counter', 'progress') => 'stats',
            $has('gallery', 'image-accordion', 'carousel') => 'gallery',
            $has('posts') => 'blog',
            ($types['icon-box'] ?? 0) >= 3 || ($types['image-box'] ?? 0) >= 3 || ($types['flip-box'] ?? 0) >= 3 => 'services',
            $has('button', 'dual-button', 'newsletter') && ! $has('image') && ($types['container'] ?? 0) <= 4 => 'cta',
            default => 'content',
        };
    }

    /** Structure d'une section (types imbriqués) : deux sections de même forme sont des doublons. */
    private static function shape(array $node): array
    {
        return [$node['type'], array_map([self::class, 'shape'], $node['children'] ?? []), $node['settings']['background_type'] ?? null, isset($node['settings']['background_image'])];
    }

    /** Nom affiché : le premier titre de la section. */
    private static function name(array $node): ?string
    {
        $name = null;
        Renderer::walk([$node], function ($n) use (&$name) {
            if ($name === null && $n['type'] === 'heading' && ! empty($n['settings']['text'])) {
                $name = mb_strimwidth(strip_tags($n['settings']['text']), 0, 70, '…');
            }
        });

        return $name;
    }

    /** Emplacements de photos d'une section : slot => [ancien identifiant, icône, texte alternatif]. */
    private static function slotsOf(array $node, array $slots): array
    {
        $json = json_encode($node, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $used = [];
        foreach ($slots as $slot => $info) {
            if (str_contains($json, '/storage/templates/'.$slot.'.')) {
                $used[$slot] = $info;
            }
        }

        return $used;
    }

    /**
     * Section prête à insérer : photos copiées dans la médiathèque du site (une seule fois
     * par photo), adresses et numéros de médias réécrits.
     */
    public static function insert(array $section, ?int $userId): array
    {
        $node = $section['node'];
        $ids = [];
        $urls = [];
        foreach ($section['slots'] as $slot => $info) {
            $media = self::media($slot, $info, $userId);
            $ids[$info['id']] = $media->id;
            $urls['/storage/templates/'.$slot.'.jpg'] = $media->url;
            $urls['/storage/templates/'.$slot.'.svg'] = $media->url;
        }

        $walk = function (mixed $v, ?string $key = null) use (&$walk, $ids, $urls) {
            if (is_array($v)) {
                $out = [];
                foreach ($v as $k => $x) {
                    $out[$k] = $walk($x, is_string($k) ? $k : $key);
                }

                return $out;
            }
            if (is_string($v) && str_contains($v, '/storage/templates/')) {
                return strtr($v, $urls);
            }
            if (is_int($v) && $key !== null && str_ends_with($key, 'media_id')) {
                return $ids[$v] ?? null;
            }

            return $v;
        };

        return $walk($node);
    }

    /** Média du site pour un emplacement : photo libre de droits, sinon illustration aux couleurs du site. */
    private static function media(string $slot, array $info, ?int $userId): Media
    {
        $photo = PhotoLibrary::file($slot);
        $path = 'templates/'.$slot.($photo ? '.jpg' : '.svg');
        $existing = Media::query()->where('disk', 'public')->where('path', $path)->first();
        if ($existing && Storage::disk('public')->exists($path)) {
            return $existing;
        }
        $data = $photo ? (string) file_get_contents($photo) : Placeholder::svg(self::siteTheme(), $info['icon'], $slot);
        Storage::disk('public')->put($path, $data);
        [$w, $h] = $photo ? (@getimagesize($photo) ?: [null, null]) : [1600, 1000];

        return $existing ?? Media::create([
            'user_id' => $userId, 'disk' => 'public', 'path' => $path, 'original_name' => basename($path),
            'mime_type' => $photo ? 'image/jpeg' : 'image/svg+xml', 'size' => strlen($data), 'width' => $w, 'height' => $h, 'alt' => $info['alt'],
        ]);
    }

    /** Couleurs du site, pour les illustrations d'attente. */
    private static function siteTheme(): Theme
    {
        $s = Setting::publicValues();

        return new Theme(colors: [
            'primary' => $s['primary_color'] ?? '#2563eb', 'secondary' => $s['secondary_color'] ?? '#0f172a', 'accent' => $s['accent_color'] ?? '#f59e0b',
            'light' => '#f1f5f9', 'text' => '#0f172a', 'muted' => '#475569',
        ]);
    }

    /** Section pour l'aperçu : sans numéros de médias (provisoires, propres au modèle), seulement les adresses. */
    public static function previewNode(array $node): array
    {
        $strip = function (array $v) use (&$strip) {
            $out = [];
            foreach ($v as $k => $x) {
                if (is_string($k) && str_ends_with($k, 'media_id')) {
                    continue;
                }
                $out[$k] = is_array($x) ? $strip($x) : $x;
            }

            return $out;
        };

        return $strip($node);
    }

    /** Adresse d'aperçu d'une photo de la bibliothèque (avant insertion). */
    public static function previewPhoto(string $slot): array
    {
        $photo = PhotoLibrary::file($slot);

        return $photo
            ? ['data' => (string) file_get_contents($photo), 'type' => 'image/jpeg']
            : ['data' => Placeholder::svg(self::siteTheme(), 'image', $slot), 'type' => 'image/svg+xml'];
    }
}
