<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    /**
     * Widgets d'un contenu, dans l'ordre, quel que soit leur niveau d'imbrication
     * (le contenu au format 1 est d'abord converti).
     */
    protected function widgetsOf(array $content): array
    {
        if (\App\Builder\ContentMigrator::isV1($content)) {
            $content = \App\Builder\ContentMigrator::toV2($content);
        }
        $out = [];
        \App\Builder\Renderer::walk($content['elements'] ?? [], function ($node) use (&$out) {
            if ($node['type'] !== 'container') {
                $out[] = $node;
            }
        });

        return $out;
    }

    /** Dossier temporaire propre à chaque test (.env, verrou d'installation, SQLite). */
    protected string $sandbox;

    protected function setUp(): void
    {
        parent::setUp();

        // Le client de test se présente par défaut comme un navigateur anglais :
        // les tests tournent en français, la langue source de l'interface.
        $this->withHeader('Accept-Language', 'fr');

        // Jamais le vrai .env ni le vrai verrou d'installation.
        $this->sandbox = storage_path('framework/testing/sandbox-'.uniqid());
        File::ensureDirectoryExists($this->sandbox);
        config([
            'geniuspress.env_path' => $this->sandbox.'/.env',
            'geniuspress.install_lock' => $this->sandbox.'/installed.lock',
            'geniuspress.sqlite_path' => $this->sandbox.'/geniuspress.sqlite',
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->sandbox);
        parent::tearDown();
    }
}
