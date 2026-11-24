<?php

namespace App\Templates;

/**
 * Photos libres de droits des modèles (Pexels, https://www.pexels.com/license/).
 *
 * Seuls les choix sont versionnés, dans templates/photos/ :
 *   - candidates.json : pour chaque emplacement, les photos proposées par la recherche ;
 *   - choices.json    : l'identifiant Pexels retenu pour chaque emplacement ;
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

    /**
     * Requête de recherche d'un emplacement : son texte alternatif en anglais et le
     * contexte du secteur. Les portraits d'équipe (« [Prénom Nom] ») cherchent un portrait.
     */
    public static function query(string $slot, string $alt, string $sector): string
    {
        $context = self::CONTEXT[$sector] ?? 'africa';
        if (str_contains($slot, '-equipe-') || str_contains($slot, '-team-') || str_starts_with(trim($alt), '[')) {
            return 'african professional portrait '.str_replace('african ', '', $context);
        }
        $alt = trim(preg_replace('/\[[^\]]*\]|[^\pL\pN\s-]/u', ' ', $alt));

        return trim($alt.' '.$context);
    }

    /** Crédit lisible : « Photo : Prénom Nom (Pexels) ». */
    public static function creditLine(array $credit): string
    {
        return ($credit['photographer'] ?? 'Pexels').' — Pexels';
    }
}
