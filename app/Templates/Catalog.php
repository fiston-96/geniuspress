<?php

namespace App\Templates;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Catalogue des modèles vu depuis un site GeniusPress : liste des modèles
 * (index.json), téléchargement vérifié d'un modèle, adresse de sa démonstration.
 *
 * Source : le catalogue en ligne (geniuspress.site), ou le dossier construit par
 * « geniuspress:templates » quand il existe en local (développement, hors ligne).
 */
class Catalog
{
    public function isLocal(): bool
    {
        $local = config('geniuspress.catalog.local');
        if ($local !== null && $local !== '') {
            return filter_var($local, FILTER_VALIDATE_BOOLEAN);
        }

        return app()->isLocal() && is_file($this->path().'/index.json');
    }

    private function path(): string
    {
        return rtrim((string) config('geniuspress.catalog.path'), '/');
    }

    private function url(): string
    {
        return rtrim((string) config('geniuspress.catalog.url'), '/');
    }

    /**
     * @return array{sectors: array, templates: list<array>}
     *
     * @throws RuntimeException message en français, montré à l'utilisateur
     */
    public function index(): array
    {
        if ($this->isLocal()) {
            $data = json_decode((string) @file_get_contents($this->path().'/index.json'), true);
        } else {
            $data = Cache::remember('catalog-index', 3600, function () {
                try {
                    $response = Http::timeout(15)->acceptJson()->get($this->url().'/index.json');
                } catch (Throwable) {
                    throw new RuntimeException(__('Le catalogue des modèles est injoignable. Vérifiez la connexion Internet du serveur, puis réessayez.'));
                }
                if (! $response->successful()) {
                    throw new RuntimeException(__('Le catalogue des modèles est injoignable. Vérifiez la connexion Internet du serveur, puis réessayez.'));
                }

                return $response->json();
            });
        }
        if (! is_array($data) || ($data['format'] ?? null) !== 'geniuspress-catalog' || ! is_array($data['templates'] ?? null)) {
            throw new RuntimeException(__('Le catalogue des modèles est illisible.'));
        }

        // On ne garde que des fiches bien formées.
        $templates = array_values(array_filter($data['templates'], fn ($t) => is_array($t)
            && is_string($t['id'] ?? null) && preg_match('/^[a-z0-9-]{2,60}$/', $t['id'])
            && ($t['file'] ?? null) === $t['id'].'.zip' && is_string($t['sha256'] ?? null)));

        return ['sectors' => is_array($data['sectors'] ?? null) ? $data['sectors'] : [], 'templates' => $templates];
    }

    public function find(string $id): ?array
    {
        return collect($this->index()['templates'])->firstWhere('id', $id);
    }

    public function demoUrl(string $id): string
    {
        return $this->isLocal() ? url('/catalogue/'.$id) : rtrim((string) config('geniuspress.catalog.demo_url'), '/').'/'.$id;
    }

    /**
     * Copie locale du modèle, vérifiée par son empreinte SHA-256.
     *
     * @return string chemin d'un fichier temporaire (à supprimer après usage)
     */
    public function download(string $id): string
    {
        $entry = $this->find($id) ?? throw new RuntimeException(__('Ce modèle n’existe pas dans le catalogue.'));
        $tmp = tempnam(sys_get_temp_dir(), 'gp-tpl-');
        $max = (int) config('geniuspress.catalog.max_bytes');

        if ($this->isLocal()) {
            $source = $this->path().'/'.$entry['file'];
            if (! is_file($source) || filesize($source) > $max) {
                throw new RuntimeException(__('Le fichier du modèle est introuvable.'));
            }
            copy($source, $tmp);
        } else {
            try {
                $response = Http::timeout(120)->withOptions(['sink' => $tmp])->get($this->url().'/'.$entry['file']);
            } catch (Throwable) {
                @unlink($tmp);
                throw new RuntimeException(__('Le téléchargement du modèle a échoué. Réessayez.'));
            }
            if (! $response->successful() || filesize($tmp) > $max) {
                @unlink($tmp);
                throw new RuntimeException(__('Le téléchargement du modèle a échoué. Réessayez.'));
            }
        }

        if (! hash_equals($entry['sha256'], (string) hash_file('sha256', $tmp))) {
            @unlink($tmp);
            throw new RuntimeException(__('Le fichier du modèle est abîmé ou a été modifié : installation annulée.'));
        }

        return $tmp;
    }
}
