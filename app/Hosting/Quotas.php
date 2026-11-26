<?php

namespace App\Hosting;

use App\Models\AiRequest;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

/**
 * Quotas d'un site hébergé (pages, articles, médias, IA), vérifiés au moment de
 * créer : par le builder, l'installation d'un modèle, l'import ou l'IA.
 */
class Quotas
{
    public function __construct(private CurrentSite $site) {}

    /** Branche les vérifications sur les modèles. Sans effet hors d'un site hébergé. */
    public static function register(): void
    {
        Page::creating(fn () => app(self::class)->checkCount('pages', Page::query()->count()));
        Post::creating(fn () => app(self::class)->checkCount('posts', Post::query()->count()));
        Media::creating(function (Media $media) {
            try {
                app(self::class)->checkMedia((int) $media->size);
            } catch (QuotaExceeded $e) {
                // Le fichier est déjà enregistré par le contrôleur : on ne le garde pas.
                Storage::disk($media->disk ?: 'public')->delete($media->path);
                throw $e;
            }
        });
    }

    public function checkCount(string $key, int $current): void
    {
        $limit = $this->site->limit($key);
        if ($limit !== null && $current >= $limit) {
            throw new QuotaExceeded($key === 'pages'
                ? __('Votre essai est limité à :n pages. Supprimez une page ou passez à une offre payante.', ['n' => $limit])
                : __('Votre essai est limité à :n articles. Supprimez un article ou passez à une offre payante.', ['n' => $limit]));
        }
    }

    public function checkMedia(int $size): void
    {
        $limit = $this->site->limit('media_mb');
        if ($limit !== null && Media::query()->sum('size') + $size > $limit * 1024 * 1024) {
            throw new QuotaExceeded(__('Votre essai est limité à :n Mo de médias. Supprimez des fichiers ou passez à une offre payante.', ['n' => $limit]));
        }
    }

    /** Lève une erreur affichable si les crédits IA du site sont épuisés. */
    public function checkAi(): void
    {
        if (! $this->site->isHosted()) {
            return;
        }
        $requests = $this->site->limit('ai_requests');
        $tokens = $this->site->limit('ai_tokens');
        $usage = $this->aiUsage();
        if (($requests !== null && $usage['requests'] >= $requests) || ($tokens !== null && $usage['tokens'] >= $tokens)) {
            throw new QuotaExceeded(__('Les crédits IA de votre essai sont épuisés. Passez à une offre payante pour continuer à utiliser l’assistant.'));
        }
    }

    /** @return array{requests: int, tokens: int} */
    public function aiUsage(): array
    {
        $row = AiRequest::query()->whereIn('status', ['ok', 'refused'])
            ->selectRaw('count(*) as n, coalesce(sum(input_tokens), 0) + coalesce(sum(output_tokens), 0) as t')
            ->first();

        return ['requests' => (int) ($row->n ?? 0), 'tokens' => (int) ($row->t ?? 0)];
    }

    /** Utilisation et quotas, pour le bandeau de l'administration. */
    public function summary(): array
    {
        $ai = $this->aiUsage();

        return [
            'pages' => [Page::query()->count(), $this->site->limit('pages')],
            'posts' => [Post::query()->count(), $this->site->limit('posts')],
            'media_mb' => [(int) ceil(Media::query()->sum('size') / 1048576), $this->site->limit('media_mb')],
            'ai_requests' => [$ai['requests'], $this->site->limit('ai_requests')],
        ];
    }
}
