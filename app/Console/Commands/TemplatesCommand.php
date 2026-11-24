<?php

namespace App\Console\Commands;

use App\Templates\TemplateBuilder;
use Illuminate\Console\Command;

/**
 * Construit le catalogue des modèles de sites à partir de templates/catalog.php
 * (modèles) et de templates/sectors/*.php (pages et textes par secteur).
 *
 *   php artisan geniuspress:templates                 → dist/catalog/v1/ (index.json + un zip par modèle)
 *   php artisan geniuspress:templates --only=ong-espoir
 *
 * Le dossier produit est publié tel quel par geniuspress.site (catalogue en ligne).
 * En local, les modèles se prévisualisent sur /catalogue/{modèle}.
 */
class TemplatesCommand extends Command
{
    protected $signature = 'geniuspress:templates {--output= : Dossier du catalogue} {--only= : Construire seulement ce modèle}';

    protected $description = 'Construit le catalogue des modèles de sites (archives et index)';

    public function handle(): int
    {
        $catalog = require base_path('templates/catalog.php');
        $output = $this->option('output') ?: config('geniuspress.catalog.path');
        $builder = new TemplateBuilder(base_path('templates/sectors'));

        $only = $this->option('only');
        if ($only && ! isset($catalog[$only])) {
            $this->error(__('Modèle inconnu : :id', ['id' => $only]));

            return self::FAILURE;
        }

        $indexFile = $output.'/index.json';
        $previous = is_file($indexFile) ? (json_decode((string) file_get_contents($indexFile), true)['templates'] ?? []) : [];
        $entries = collect($previous)->keyBy('id')->all();

        foreach ($catalog as $id => $def) {
            if ($only && $id !== $only) {
                continue;
            }
            $entries[$id] = $builder->build($id, $def, $output);
            $this->line(__('Modèle construit : :id (:pages pages par langue)', ['id' => $id, 'pages' => count($entries[$id]['pages'])]));
        }
        // On ne garde que les modèles encore déclarés, dans l'ordre du catalogue.
        $entries = array_values(array_filter(array_map(fn ($id) => $entries[$id] ?? null, array_keys($catalog))));

        $sectors = [];
        foreach (array_unique(array_column($entries, 'sector')) as $sector) {
            $sectors[$sector] = $builder->pack($sector)['sector'] ?? ['name' => ['fr' => $sector, 'en' => $sector]];
        }
        file_put_contents($indexFile, json_encode([
            'format' => 'geniuspress-catalog',
            'version' => TemplateBuilder::CATALOG_VERSION,
            'generated_at' => now()->toIso8601String(),
            'sectors' => $sectors,
            'templates' => $entries,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->info(__('Catalogue : :count modèles dans :path', ['count' => count($entries), 'path' => $output]));

        return self::SUCCESS;
    }
}
