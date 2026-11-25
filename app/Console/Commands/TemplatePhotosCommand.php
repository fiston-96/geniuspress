<?php

namespace App\Console\Commands;

use App\Templates\Kit;
use App\Templates\PhotoLibrary;
use App\Templates\TemplateBuilder;
use App\Templates\Theme;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Throwable;

/**
 * Prépare les photos libres de droits des modèles, depuis Pixabay
 * (clé gratuite : https://pixabay.com/api/docs/, variable PIXABAY_API_KEY)
 * ou Pexels (variable PEXELS_API_KEY).
 *
 *   php artisan geniuspress:templates:photos --search            → candidates pour les emplacements sans photo choisie
 *   php artisan geniuspress:templates:photos --review            → dist/photos-review.html (pour comparer et choisir)
 *   php artisan geniuspress:templates:photos --choose=ong-accueil:3   → retenir la 3ᵉ candidate
 *   php artisan geniuspress:templates:photos --download          → télécharge les photos choisies (1600 px au plus, JPEG)
 *
 * Un nom de pack limite l'opération à ce secteur : … --search ong
 */
class TemplatePhotosCommand extends Command
{
    protected $signature = 'geniuspress:templates:photos {pack? : Limiter à un secteur}
        {--search : Chercher des photos pour les emplacements sans choix}
        {--refresh : Avec --search : chercher à nouveau, même pour les emplacements déjà choisis}
        {--review : Construire la page de comparaison des candidates}
        {--choose=* : Retenir une candidate (emplacement:numéro)}
        {--download : Télécharger les photos choisies}
        {--source= : Source des photos (pixabay ou pexels ; par défaut, celle dont la clé est définie)}
        {--candidates=8 : Nombre de candidates par emplacement}';

    protected $description = 'Prépare les photos libres de droits (Pixabay ou Pexels) des modèles de sites';

    private const SOURCES = [
        'pixabay' => ['name' => 'Pixabay', 'env' => 'PIXABAY_API_KEY', 'signup' => 'https://pixabay.com/api/docs/', 'license' => 'Pixabay Content License'],
        'pexels' => ['name' => 'Pexels', 'env' => 'PEXELS_API_KEY', 'signup' => 'https://www.pexels.com/api/', 'license' => 'Pexels License'],
    ];

    public function handle(): int
    {
        $slots = $this->slots($this->argument('pack'));
        $this->line(__(':count emplacements de photos.', ['count' => count($slots)]));

        foreach ((array) $this->option('choose') as $choice) {
            $this->choose((string) $choice);
        }
        if ($this->option('search') && ! $this->search($slots)) {
            return self::FAILURE;
        }
        if ($this->option('download') && ! $this->download($slots)) {
            return self::FAILURE;
        }
        if ($this->option('review')) {
            $this->review($slots);
        }

        $choices = PhotoLibrary::read('choices');
        $files = count(array_filter(array_keys($slots), fn ($s) => PhotoLibrary::file($s)));
        $this->info(__('Photos choisies : :chosen / :total · téléchargées : :files', ['chosen' => count(array_intersect_key($choices, $slots)), 'total' => count($slots), 'files' => $files]));

        return self::SUCCESS;
    }

    /** Emplacements de photos des packs, avec leur texte alternatif en anglais et leur secteur. */
    private function slots(?string $only): array
    {
        $builder = new TemplateBuilder(base_path('templates/sectors'));
        $theme = new Theme(['primary' => '#000000', 'secondary' => '#000000', 'accent' => '#ffffff', 'light' => '#ffffff', 'text' => '#000000', 'muted' => '#333333'], hero: 'image');
        $slots = [];
        foreach (glob(base_path('templates/sectors/*.php')) as $file) {
            $sector = basename($file, '.php');
            if ($only && $sector !== $only) {
                continue;
            }
            Kit::reset();
            $site = ($builder->pack($sector)['build'])('en', $theme, []);
            // Les articles du blog ont aussi leurs photos (image à la une).
            foreach ($site['blog']['posts'] ?? [] as $post) {
                Kit::photoFields(...$post['photo']);
            }
            foreach (Kit::slots() as $slot => $info) {
                $slots[$slot] = ['sector' => $sector, 'alt' => $info['alt'], 'icon' => $info['icon']];
            }
        }

        return $slots;
    }

    private function key(string $source): string
    {
        return (string) config('geniuspress.catalog.'.$source.'_key');
    }

    /** Source choisie (--source), sinon la première dont la clé est définie. */
    private function source(): ?string
    {
        $source = $this->option('source');
        if ($source && ! isset(self::SOURCES[$source])) {
            $this->error(__('Source inconnue : :source (pixabay ou pexels).', ['source' => $source]));

            return null;
        }
        $source ??= collect(array_keys(self::SOURCES))->first(fn ($s) => $this->key($s) !== '') ?? 'pixabay';
        if ($this->key($source) === '') {
            $s = self::SOURCES[$source];
            $this->error(__('Ajoutez votre clé API :name (gratuite, :url) dans le fichier .env : :env=…', ['name' => $s['name'], 'url' => $s['signup'], 'env' => $s['env']]));

            return null;
        }

        return $source;
    }

    private function search(array $slots): bool
    {
        if (! $source = $this->source()) {
            return false;
        }
        $wanted = max(3, (int) $this->option('candidates'));
        $candidates = PhotoLibrary::read('candidates');
        $choices = PhotoLibrary::read('choices');
        if ($this->option('refresh')) {
            $choices = array_diff_key($choices, $slots);
        }
        $todo = array_filter($slots, fn ($s, $slot) => ! isset($choices[$slot]), ARRAY_FILTER_USE_BOTH);
        $bar = $this->output->createProgressBar(count($todo));

        foreach ($todo as $slot => $info) {
            // Du plus précis au plus général, jusqu'à avoir assez de candidates.
            $photos = [];
            $used = null;
            $limited = false;
            foreach (PhotoLibrary::queries($slot, $info['alt'], $info['sector']) as $query) {
                try {
                    $response = $this->request($source, $query, $wanted);
                    if ($response->status() === 429 && $source === 'pixabay') {
                        // Pixabay : 100 requêtes par minute ; on attend la fin de la minute, puis on réessaie.
                        $this->waitForReset($response);
                        $response = $this->request($source, $query, $wanted);
                    }
                } catch (Throwable $e) {
                    $this->newLine();
                    $this->error(__('Recherche impossible pour :slot : :error', ['slot' => $slot, 'error' => $this->hideKey($source, $e->getMessage())]));

                    return false;
                }
                if (in_array($response->status(), [401, 403], true) || ($source === 'pixabay' && $response->status() === 400 && str_contains($response->body(), 'key'))) {
                    $this->newLine();
                    $this->error(__('Clé API :name refusée.', ['name' => self::SOURCES[$source]['name']]));

                    return false;
                }
                if ($response->status() === 429 && $source === 'pexels') {
                    return $this->hourlyLimit();
                }
                if (! $response->successful()) {
                    $this->newLine();
                    $this->error(__('Recherche impossible pour :slot : :error', ['slot' => $slot, 'error' => 'HTTP '.$response->status()]));

                    return false;
                }
                $used ??= $query;
                $seen = array_column($photos, 'id');
                foreach ($this->photos($source, $response) as $photo) {
                    if (count($photos) < $wanted && ! in_array($photo['id'], $seen, true)) {
                        $photos[] = $photo;
                    }
                }
                $limited = $this->exhausted($response);
                if (count($photos) >= (int) ceil($wanted / 2)) {
                    $used = $query;
                    break;
                }
                if ($limited && $source === 'pexels') {
                    break; // la progression de cet emplacement est enregistrée, puis on s'arrête
                }
                if ($limited) {
                    $this->waitForReset($response);
                    $limited = false;
                }
                Sleep::for(200)->milliseconds();
            }
            $candidates[$slot] = ['query' => $used, 'source' => $source, 'photos' => $photos];
            // Première candidate qui n'est pas déjà la photo d'un autre emplacement du secteur.
            $taken = array_values(array_intersect_key($choices, array_filter($slots, fn ($s) => $s['sector'] === $info['sector'])));
            $free = array_values(array_filter($photos, fn ($p) => ! in_array($p['id'], $taken, true)));
            if ($photo = $free[0] ?? $photos[0] ?? null) {
                $choices[$slot] = $photo['id'];
            }
            // Enregistré à chaque emplacement : une interruption ne fait rien perdre.
            PhotoLibrary::write('candidates', $candidates);
            PhotoLibrary::write('choices', $choices);
            $bar->advance();
            if ($limited) {
                if ($source === 'pexels') {
                    // Pexels : 200 requêtes par heure. La progression est enregistrée ; on reprendra plus tard.
                    return $this->hourlyLimit();
                }
                $this->waitForReset($response);
            }
            Sleep::for(200)->milliseconds();
        }
        $bar->finish();
        $this->newLine();
        $empty = array_keys(array_filter(array_intersect_key($candidates, $todo), fn ($c) => ! $c['photos']));
        if ($empty) {
            $this->warn(__('Aucune photo trouvée pour : :slots', ['slots' => implode(', ', $empty)]));
        }

        return true;
    }

    private function request(string $source, string $query, int $count): Response
    {
        $http = Http::timeout(20)->retry(2, 1000, throw: false);

        return $source === 'pixabay'
            ? $http->get('https://pixabay.com/api/', ['key' => $this->key('pixabay'), 'q' => mb_substr($query, 0, 100), 'image_type' => 'photo', 'orientation' => 'horizontal', 'safesearch' => 'true', 'per_page' => max(3, $count)])
            : $http->withHeaders(['Authorization' => $this->key('pexels')])->get('https://api.pexels.com/v1/search', ['query' => $query, 'per_page' => $count, 'orientation' => 'landscape']);
    }

    private function hourlyLimit(): bool
    {
        $this->newLine();
        $this->warn(__('Limite horaire de :name atteinte : relancez la même commande dans une heure, elle reprendra là où elle s’est arrêtée.', ['name' => 'Pexels']));

        return true;
    }

    /** Pixabay : 100 requêtes par minute ; X-RateLimit-Reset donne les secondes restantes. */
    private function waitForReset(Response $response): void
    {
        Sleep::for(min(65, max(1, (int) $response->header('X-RateLimit-Reset')) + 1))->seconds();
    }

    private function exhausted(Response $response): bool
    {
        $remaining = $response->header('X-Ratelimit-Remaining');

        return $remaining !== '' && is_numeric($remaining) && (int) $remaining <= 1;
    }

    /** Photos d'une réponse, au même format quelle que soit la source. */
    private function photos(string $source, Response $response): array
    {
        if ($source === 'pixabay') {
            return array_map(fn ($p) => [
                'id' => $p['id'], 'source' => 'pixabay', 'photographer' => $p['user'] ?? '',
                'photographer_url' => isset($p['user'], $p['user_id']) ? 'https://pixabay.com/users/'.rawurlencode($p['user']).'-'.$p['user_id'].'/' : '',
                'url' => $p['pageURL'] ?? '', 'alt' => $p['tags'] ?? '', 'preview' => $p['webformatURL'] ?? ($p['previewURL'] ?? ''), 'src' => $p['largeImageURL'] ?? ($p['webformatURL'] ?? ''),
            ], (array) $response->json('hits'));
        }

        return array_map(fn ($p) => [
            'id' => $p['id'], 'source' => 'pexels', 'photographer' => $p['photographer'] ?? '', 'photographer_url' => $p['photographer_url'] ?? '',
            'url' => $p['url'] ?? '', 'alt' => $p['alt'] ?? '', 'preview' => $p['src']['medium'] ?? '', 'src' => $p['src']['large2x'] ?? ($p['src']['original'] ?? ''),
        ], (array) $response->json('photos'));
    }

    /** Les adresses de Pixabay contiennent la clé : elle ne doit jamais s'afficher. */
    private function hideKey(string $source, string $message): string
    {
        $key = $this->key($source);

        return $key === '' ? $message : str_replace($key, '…', $message);
    }

    private function choose(string $choice): void
    {
        [$slot, $n] = array_pad(explode(':', $choice, 2), 2, '');
        $photos = PhotoLibrary::read('candidates')[$slot]['photos'] ?? [];
        $index = (int) $n - 1;
        if (! isset($photos[$index])) {
            $this->warn(__('Candidate introuvable : :choice', ['choice' => $choice]));

            return;
        }
        $choices = PhotoLibrary::read('choices');
        $choices[$slot] = $photos[$index]['id'];
        PhotoLibrary::write('choices', $choices);
        @unlink(PhotoLibrary::path('files/'.$slot.'.jpg')); // sera re-téléchargée
        $this->line(__('Emplacement :slot : candidate :n retenue.', ['slot' => $slot, 'n' => $n]));
    }

    private function download(array $slots): bool
    {
        $choices = array_intersect_key(PhotoLibrary::read('choices'), $slots);
        $candidates = PhotoLibrary::read('candidates');
        $credits = PhotoLibrary::read('credits');
        @mkdir(PhotoLibrary::path('files'), 0775, true);
        $bar = $this->output->createProgressBar(count($choices));

        foreach ($choices as $slot => $id) {
            $target = PhotoLibrary::path('files/'.$slot.'.jpg');
            $source = $candidates[$slot]['source'] ?? 'pexels';
            if (is_file($target) && ($credits[$slot]['id'] ?? null) === $id && ($credits[$slot]['source'] ?? 'pexels') === $source) {
                $bar->advance();

                continue;
            }
            try {
                // Les informations de la photo viennent de la recherche ; sinon, une requête à l'API.
                $photo = collect($candidates[$slot]['photos'] ?? [])->firstWhere('id', $id)
                    ?? $this->apiPhoto($source, (int) $id);
                $image = Http::timeout(60)->retry(2, 1000)->get($photo['src'])->throw()->body();
            } catch (Throwable $e) {
                $this->newLine();
                $this->warn(__('Téléchargement impossible pour :slot : :error', ['slot' => $slot, 'error' => $this->hideKey($source, $e->getMessage())]));

                continue;
            }
            if (! $this->saveJpeg($image, $target)) {
                $this->warn(__('Image illisible pour :slot.', ['slot' => $slot]));

                continue;
            }
            $credits[$slot] = ['id' => $id, 'source' => $source, 'photographer' => $photo['photographer'] ?? '', 'photographer_url' => $photo['photographer_url'] ?? '', 'url' => $photo['url'] ?? '', 'license' => self::SOURCES[$source]['license']];
            PhotoLibrary::write('credits', $credits);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        return true;
    }

    private function apiPhoto(string $source, int $id): array
    {
        if ($source === 'pixabay') {
            $response = Http::timeout(20)->retry(2, 1000)->get('https://pixabay.com/api/', ['key' => $this->key('pixabay'), 'id' => $id]);

            return $this->photos('pixabay', $response)[0] ?? throw new \RuntimeException('Photo introuvable');
        }
        $p = Http::withHeaders(['Authorization' => $this->key('pexels')])->timeout(20)->retry(2, 1000)->get('https://api.pexels.com/v1/photos/'.$id)->throw()->json();

        return ['id' => $p['id'], 'photographer' => $p['photographer'] ?? '', 'photographer_url' => $p['photographer_url'] ?? '', 'url' => $p['url'] ?? '', 'src' => $p['src']['large2x'] ?? $p['src']['original']];
    }

    /** Réduit à 1600 px de large au plus, JPEG qualité 78 (environ 150 à 300 Ko). */
    private function saveJpeg(string $data, string $target): bool
    {
        $source = @imagecreatefromstring($data);
        if (! $source) {
            return false;
        }
        [$w, $h] = [imagesx($source), imagesy($source)];
        $width = min(1600, $w);
        $height = (int) round($h * $width / $w);
        $image = imagecreatetruecolor($width, $height);
        imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, $w, $h);
        imageinterlace($image, true);

        return imagejpeg($image, $target, 78);
    }

    /** Page de comparaison : pour chaque emplacement, les candidates et la photo retenue. */
    private function review(array $slots): void
    {
        $candidates = PhotoLibrary::read('candidates');
        $choices = PhotoLibrary::read('choices');
        $html = '<!doctype html><meta charset="utf-8"><title>Photos des modèles</title><style>body{font:14px system-ui;margin:24px;background:#f8fafc}h2{font-size:15px;margin:28px 0 4px}p{margin:0 0 8px;color:#475569}.row{display:flex;gap:8px;flex-wrap:wrap}figure{margin:0;width:180px;background:#fff;border:3px solid transparent;border-radius:8px;overflow:hidden}figure.on{border-color:#4f46e5}img{width:100%;height:110px;object-fit:cover;display:block}figcaption{padding:4px 6px;font-size:12px}</style>';
        $html .= '<h1>Photos des modèles</h1><p>Pour changer : <code>php artisan geniuspress:templates:photos --choose=emplacement:numéro</code> puis <code>--download</code>.</p>';
        foreach ($slots as $slot => $info) {
            $html .= '<h2 id="'.e($slot).'">'.e($slot).'</h2><p>'.e($info['alt']).' — recherche : « '.e($candidates[$slot]['query'] ?? '…').' »</p><div class="row">';
            foreach ($candidates[$slot]['photos'] ?? [] as $i => $p) {
                $html .= '<figure class="'.(($choices[$slot] ?? null) === $p['id'] ? 'on' : '').'"><img loading="lazy" src="'.e($p['preview']).'" alt="'.e($p['alt']).'"><figcaption>'.($i + 1).' · '.e($p['photographer']).'</figcaption></figure>';
            }
            $html .= '</div>';
        }
        @mkdir(base_path('dist'), 0775, true);
        file_put_contents(base_path('dist/photos-review.html'), $html);
        $this->line(__('Page de comparaison : :path', ['path' => base_path('dist/photos-review.html')]));
    }
}
