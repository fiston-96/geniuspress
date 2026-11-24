<?php

use App\Builder\ContentMigrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Format du contenu, version 2 : les sections et colonnes deviennent des
 * conteneurs imbriqués (voir App\Builder\Schema et ContentMigrator).
 * Pages publiées, brouillons et versions enregistrées sont convertis.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->convert('pages', ['content', 'draft_content']);
        $this->convert('page_revisions', ['content']);
    }

    /** Pas de retour arrière automatique : le format 2 ne se convertit pas en format 1 sans perte. */
    public function down(): void {}

    private function convert(string $table, array $columns): void
    {
        DB::table($table)->select(['id', ...$columns])->orderBy('id')->chunkById(100, function ($rows) use ($table, $columns) {
            foreach ($rows as $row) {
                $changes = [];
                foreach ($columns as $column) {
                    $content = is_string($row->{$column}) ? json_decode($row->{$column}, true) : null;
                    if (ContentMigrator::isV1($content)) {
                        $changes[$column] = json_encode(ContentMigrator::toV2($content), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }
                }
                if ($changes) {
                    DB::table($table)->where('id', $row->id)->update($changes);
                }
            }
        });
    }
};
