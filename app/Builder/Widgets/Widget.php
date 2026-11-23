<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/**
 * Type de widget du builder. Les widgets du cœur et ceux des plugins
 * héritent tous de cette classe et sont déclarés dans le WidgetRegistry.
 *
 * Règles de validation (rules) : string:MAX | html | color | int:MIN:MAX |
 * enum:a,b,c | url | slug:MAX | bool. Les réglages communs (marge basse,
 * masquage mobile/ordinateur) sont ajoutés automatiquement.
 */
abstract class Widget
{
    /** Identifiant unique, en minuscules (ex. « heading », « acme-quote »). */
    abstract public function type(): string;

    /** Nom affiché dans l'interface. */
    abstract public function label(): string;

    /** Réglages autorisés et leur règle de validation. */
    abstract public function rules(): array;

    /**
     * HTML du widget. Les réglages sont déjà validés, mais tout texte doit
     * quand même être échappé avec e() (sauf règle « html », déjà nettoyée).
     */
    abstract public function render(array $settings, string $id, RenderContext $ctx): string;

    /** Réglages contenant du texte à traduire. */
    public function translatable(): array
    {
        return [];
    }

    /** Réglages que l'assistant IA ne doit pas remplir (ex. adresses d'images). */
    public function aiExcluded(): array
    {
        return [];
    }

    /** Conseil d'utilisation donné à l'assistant IA (en anglais), ou null pour ne pas lui proposer ce widget. */
    public function aiHint(): ?string
    {
        return null;
    }
}
