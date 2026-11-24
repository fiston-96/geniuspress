<?php

namespace App\Templates;

/**
 * Photos libres de droits des modèles (Pixabay, https://pixabay.com/service/license-summary/,
 * ou Pexels, https://www.pexels.com/license/).
 *
 * Seuls les choix sont versionnés, dans templates/photos/ :
 *   - candidates.json : pour chaque emplacement, les photos proposées par la recherche ;
 *   - queries.json    : mots-clés choisis à la main pour chaque emplacement (recherche par étiquettes) ;
 *   - choices.json    : l'identifiant de la photo retenue pour chaque emplacement ;
 *   - credits.json    : photographe et lien de chaque photo téléchargée.
 * Les fichiers (files/{emplacement}.jpg) se re-téléchargent à l'identique avec
 * « geniuspress:templates:photos --download ». Un emplacement sans photo garde
 * son illustration d'attente.
 */
final class PhotoLibrary
{
    /** Contexte ajouté aux recherches, par secteur (les photos doivent ressembler au public visé). */
    public const CONTEXT = [
        'ong' => 'african community',
        'eglise' => 'african church',
        'ecole' => 'african school',
        'gouvernement' => 'african city',
        'hopital' => 'african hospital',
        'tech' => 'african tech office',
        'commerce' => 'african shop',
        'art-deco' => 'african interior decor',
        'restaurant' => 'african restaurant',
        'cabinet' => 'african business office',
    ];

    public static function path(string $file = ''): string
    {
        return rtrim((string) config('geniuspress.catalog.photos_path'), '/\\').($file !== '' ? '/'.$file : '');
    }

    /** Fichier de la photo d'un emplacement, s'il a été téléchargé. */
    public static function file(string $slot): ?string
    {
        $file = self::path('files/'.$slot.'.jpg');

        return preg_match('/^[a-z0-9-]{2,80}$/', $slot) && is_file($file) ? $file : null;
    }

    public static function read(string $name): array
    {
        $data = json_decode((string) @file_get_contents(self::path($name.'.json')), true);

        return is_array($data) ? $data : [];
    }

    public static function write(string $name, array $data): void
    {
        @mkdir(self::path(), 0775, true);
        ksort($data);
        file_put_contents(self::path($name.'.json'), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
    }

    /** Mots trop vagues pour une recherche courte. */
    private const STOPWORDS = ['a', 'an', 'the', 'of', 'in', 'on', 'at', 'to', 'for', 'with', 'and', 'or', 'by', 'from', 'our', 'their', 'its', 'his', 'her', 'during', 'into', 'over', 'under', 'near', 'some', 'two', 'three', 'four', 'several', 'group', 'people', 'person'];

    /**
     * Requêtes de recherche d'un emplacement, de la plus précise à la plus générale :
     * ses mots-clés (queries.json) s'ils existent, sinon son texte alternatif en anglais
     * avec le contexte du secteur, puis ses mots principaux, puis le contexte seul.
     * Les portraits d'équipe (« [Prénom Nom] ») cherchent un portrait.
     *
     * @return list<string>
     */
    public static function queries(string $slot, string $alt, string $sector): array
    {
        $context = self::CONTEXT[$sector] ?? 'africa';
        $chosen = trim((string) (self::read('queries')[$slot] ?? ''));
        if ($chosen !== '') {
            $words = explode(' ', $chosen);

            return array_values(array_unique(array_filter([$chosen, count($words) > 2 ? implode(' ', array_slice($words, 0, 2)) : null, $context])));
        }
        if (str_contains($slot, '-equipe-') || str_contains($slot, '-team-') || str_starts_with(trim($alt), '[')) {
            return array_values(array_unique(['african professional portrait '.str_replace('african ', '', $context), 'african professional portrait', 'african portrait']));
        }
        $alt = strtolower(trim(preg_replace('/\s+/', ' ', preg_replace('/\[[^\]]*\]|[^\pL\pN\s-]/u', ' ', $alt))));
        $words = array_values(array_filter(explode(' ', $alt), fn ($w) => strlen($w) > 2 && ! in_array($w, self::STOPWORDS, true)));

        return array_values(array_unique(array_filter([
            trim($alt.' '.$context),
            trim(implode(' ', array_slice($words, 0, 2)).' african'),
            $context,
        ])));
    }

    public static function query(string $slot, string $alt, string $sector): string
    {
        return self::queries($slot, $alt, $sector)[0];
    }
}
