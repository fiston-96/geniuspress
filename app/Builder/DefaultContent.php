<?php

namespace App\Builder;

/** Contenu de démarrage pour la page d'accueil créée à l'installation. */
class DefaultContent
{
    public static function home(string $siteName): array
    {
        return ContentMigrator::toV2(['version' => 1, 'sections' => [
            [
                'id' => 'hero000001', 'type' => 'section',
                'settings' => ['background_color' => '#0f172a', 'text_color' => '#ffffff', 'padding_top' => 120, 'padding_bottom' => 120, 'padding_top_mobile' => 72, 'padding_bottom_mobile' => 72],
                'columns' => [[
                    'id' => 'herocol001', 'type' => 'column', 'settings' => [],
                    'widgets' => [
                        ['id' => 'herohead01', 'type' => 'heading', 'settings' => ['text' => $siteName, 'tag' => 'h1', 'align' => 'center', 'font_size' => 56, 'font_size_mobile' => 36, 'font_weight' => '800', 'margin_bottom' => 16]],
                        ['id' => 'herotext01', 'type' => 'text', 'settings' => ['html' => '<p>Votre site est prêt. Modifiez cette page avec le builder ou demandez à l’assistant IA de la créer pour vous.</p>', 'align' => 'center', 'font_size' => 20, 'margin_bottom' => 32]],
                        ['id' => 'herobtn001', 'type' => 'button', 'settings' => ['text' => 'Nous contacter', 'url' => '#contact', 'size' => 'lg', 'align' => 'center']],
                    ],
                ]],
            ],
        ]]);
    }
}
