<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Le client de test se présente par défaut comme un navigateur anglais :
        // les tests tournent en français, la langue source de l'interface.
        $this->withHeader('Accept-Language', 'fr');
    }
}
