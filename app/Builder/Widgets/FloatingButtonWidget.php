<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Bouton flottant en bas de l'écran : WhatsApp, appel ou lien, toujours à portée de main. */
class FloatingButtonWidget extends Widget
{
    public function type(): string { return 'floating-button'; }

    public function label(): string { return __('Bouton flottant'); }

    public function rules(): array
    {
        return [
            'kind' => 'enum:whatsapp,phone,link',
            'number' => 'string:30',
            'message' => 'string:300',
            'link' => 'url',
            'label' => 'string:60',
            'position' => 'enum:right,left',
            'bg_color' => 'color',
            'color' => 'color',
        ];
    }

    public function translatable(): array { return ['message', 'label']; }

    public function fillable(): array { return [...$this->translatable(), 'number']; }

    public function aiHint(): ?string
    {
        return 'Floating WhatsApp or call button ("kind": whatsapp|phone, "number" with country code, optional "label" and "message"). Use "[numéro]" if unknown.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $kind = $s['kind'] ?? 'whatsapp';
        $digits = preg_replace('/[^0-9+]/', '', (string) ($s['number'] ?? ''));
        $href = match ($kind) {
            'whatsapp' => $digits !== '' ? 'https://wa.me/'.ltrim($digits, '+').(! empty($s['message']) ? '?text='.rawurlencode($s['message']) : '') : null,
            'phone' => $digits !== '' ? 'tel:'.$digits : null,
            default => $s['link'] ?? null,
        };
        if (! $href) {
            return '';
        }
        $ctx->rule($id, ['background' => $s['bg_color'] ?? null, 'color' => $s['color'] ?? null], false, ' .gp-float');
        $icon = ['whatsapp' => 'brand:whatsapp', 'phone' => 'lucide:phone', 'link' => 'lucide:message-circle'][$kind];
        $label = $s['label'] ?? null;

        return '<a class="gp-float gp-float--'.e($kind).' gp-float--'.e($s['position'] ?? 'right').'" href="'.e($href).'"'.($kind === 'whatsapp' ? ' target="_blank" rel="noopener"' : '')
            .' aria-label="'.e($label ?: ($kind === 'phone' ? __('Appeler', [], $ctx->site?->locale) : 'WhatsApp')).'">'
            .Icons::svg($icon).($label ? '<span class="gp-float__label">'.e($label).'</span>' : '').'</a>';
    }
}
