<?php

namespace Tests\Feature\Platform;

use App\Platform\Models\TemplatePrice;

/**
 * Catalogue public (/catalogue/v1/…) consulté par les sites GeniusPress installés ailleurs :
 * index et zips des modèles gratuits ; jamais le zip d'un modèle payant.
 */
class CatalogFileTest extends PlatformTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $dir = $this->sandbox.DIRECTORY_SEPARATOR.'catalogue';
        @mkdir($dir, 0775, true);
        file_put_contents($dir.DIRECTORY_SEPARATOR.'index.json', json_encode(['format' => 'geniuspress-catalog', 'templates' => []]));
        file_put_contents($dir.DIRECTORY_SEPARATOR.'ong-horizon.zip', 'zip-gratuit');
        file_put_contents($dir.DIRECTORY_SEPARATOR.'cabinet-prestige.zip', 'zip-payant');
        config(['geniuspress.catalog.path' => $dir]);
    }

    public function test_index_and_free_templates_are_public_paid_ones_are_not(): void
    {
        $index = $this->get('/catalogue/v1/index.json')->assertOk()->assertHeader('Content-Type', 'application/json; charset=utf-8');
        $this->assertStringContainsString('geniuspress-catalog', (string) file_get_contents($index->baseResponse->getFile()->getPathname()));
        $this->get('/catalogue/v1/ong-horizon.zip')->assertOk()->assertHeader('Content-Type', 'application/zip');

        TemplatePrice::create(['template_id' => 'cabinet-prestige', 'price' => 5000, 'currency' => 'USD', 'active' => true]);
        $this->get('/catalogue/v1/cabinet-prestige.zip')->assertNotFound();

        $this->get('/catalogue/v1/absent.zip')->assertNotFound();
        $this->get('/catalogue/v1/..%2F.env')->assertNotFound();
    }
}
