<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Captures d'écran des démonstrations des modèles (dist/website/captures/{modèle}[-en].jpg,
 * 1200 × 750), utilisées par la vitrine et publiées avec le catalogue (aperçus des modèles).
 * Chrome (ou Edge) sans fenêtre photographie /catalogue/{modèle} d'un serveur en marche.
 *
 *   php artisan geniuspress:captures --missing                     → modèles sans capture
 *   php artisan geniuspress:captures --only=ong-horizon --base=http://localhost:8000
 *
 * Outil de préparation du catalogue (poste de développement), jamais appelé par un site.
 */
class CapturesCommand extends Command
{
    protected $signature = 'geniuspress:captures {--base=http://localhost:8000 : Adresse du serveur qui sert les démonstrations}
        {--only= : Un seul modèle} {--missing : Seulement les modèles sans capture} {--browser= : Chemin de Chrome ou Edge}';

    protected $description = 'Capture la page d’accueil des démonstrations des modèles (Chrome sans fenêtre)';

    public function handle(): int
    {
        $browser = $this->option('browser') ?: $this->findBrowser();
        if (! $browser) {
            $this->error(__('Chrome ou Edge introuvable : indiquez son chemin avec --browser.'));

            return self::FAILURE;
        }
        $dir = base_path('dist/website/captures');
        @mkdir($dir, 0775, true);
        $ids = array_keys(require base_path('templates/catalog.php'));
        if ($only = $this->option('only')) {
            $ids = array_values(array_intersect($ids, [$only]));
        }

        $done = 0;
        foreach ($ids as $id) {
            foreach (['fr' => '', 'en' => '-en'] as $lang => $suffix) {
                $file = $dir.'/'.$id.$suffix.'.jpg';
                if ($this->option('missing') && is_file($file)) {
                    continue;
                }
                $url = rtrim((string) $this->option('base'), '/').'/catalogue/'.$id.($lang === 'en' ? '/en' : '').'?capture=1';
                if ($this->shoot($browser, $url, $file)) {
                    $done++;
                    $this->line(__('Capture : :file', ['file' => basename($file)]));
                } else {
                    $this->warn(__('Échec de la capture : :url', ['url' => $url]));
                }
            }
        }
        $this->info(__(':count capture(s) réalisée(s).', ['count' => $done]));

        return self::SUCCESS;
    }

    /** Photographie la page à 1440 × 900, puis l'enregistre en JPEG 1200 × 750. */
    private function shoot(string $browser, string $url, string $file): bool
    {
        $png = tempnam(sys_get_temp_dir(), 'gp-shot-').'.png';
        $profile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'gp-chrome-'.getmypid();
        $process = new Process([$browser, '--headless=new', '--disable-gpu', '--hide-scrollbars', '--no-first-run', '--mute-audio',
            '--user-data-dir='.$profile, '--window-size=1440,900', '--virtual-time-budget=4000', '--screenshot='.$png, $url]);
        $process->setTimeout(90)->run();
        if (! is_file($png) || filesize($png) < 1000) {
            @unlink($png);

            return false;
        }
        $source = @imagecreatefrompng($png);
        @unlink($png);
        if (! $source) {
            return false;
        }
        $out = imagecreatetruecolor(1200, 750);
        imagecopyresampled($out, $source, 0, 0, 0, 0, 1200, 750, imagesx($source), (int) round(imagesx($source) * 750 / 1200));
        $ok = imagejpeg($out, $file, 82);
        imagedestroy($source);
        imagedestroy($out);

        return $ok;
    }

    private function findBrowser(): ?string
    {
        foreach ([
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            '/usr/bin/google-chrome', '/usr/bin/chromium', '/usr/bin/chromium-browser',
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        ] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
