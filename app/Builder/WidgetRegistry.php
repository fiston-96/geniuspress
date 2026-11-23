<?php

namespace App\Builder;

use App\Builder\Widgets\Widget;
use InvalidArgumentException;

/** Liste des types de widgets disponibles (cœur + plugins actifs). */
class WidgetRegistry
{
    /** @var array<string, Widget> */
    private array $widgets = [];

    public function register(Widget $widget): void
    {
        $type = $widget->type();
        if (! preg_match('/^[a-z][a-z0-9-]{1,40}$/', $type)) {
            throw new InvalidArgumentException("Type de widget invalide : « {$type} » (minuscules, chiffres et tirets).");
        }
        if (isset($this->widgets[$type])) {
            throw new InvalidArgumentException("Le widget « {$type} » est déjà déclaré.");
        }
        $this->widgets[$type] = $widget;
    }

    public function has(string $type): bool
    {
        return isset($this->widgets[$type]);
    }

    public function get(string $type): ?Widget
    {
        return $this->widgets[$type] ?? null;
    }

    /** @return array<string, Widget> */
    public function all(): array
    {
        return $this->widgets;
    }

    /** Règles complètes d'un widget, réglages communs compris. */
    public function rules(string $type): array
    {
        return isset($this->widgets[$type]) ? $this->widgets[$type]->rules() + Schema::COMMON_WIDGET : [];
    }
}
