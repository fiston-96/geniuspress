<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Carte interactive à partir d'une adresse (sans clé d'API). */
class MapWidget extends Widget
{
    public function type(): string { return 'map'; }

    public function label(): string { return __('Carte'); }

    public function rules(): array
    {
        return [
            'address' => 'string:300',
            'zoom' => 'int:1:20',
            'height' => 'int:100:1200@r',
            'radius' => 'int:0:60',
            'grayscale' => 'bool',
        ];
    }

    public function aiExcluded(): array { return ['address']; }

    public function fillable(): array { return ['address']; }

    public static function embedUrl(string $address, int $zoom): string
    {
        return 'https://maps.google.com/maps?q='.rawurlencode($address).'&z='.$zoom.'&output=embed';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        if (! isset($s['address'])) {
            return '';
        }
        $ctx->rule($id, ['height' => ($s['height'] ?? 400).'px', 'border-radius' => isset($s['radius']) ? $s['radius'].'px' : null, 'filter' => ! empty($s['grayscale']) ? 'grayscale(1)' : null], false, ' iframe');
        foreach (['tablet', 'mobile'] as $device) {
            if (isset($s['height_'.$device])) {
                $ctx->rule($id, ['height' => $s['height_'.$device].'px'], $device, ' iframe');
            }
        }

        return '<div class="gp-map"><iframe src="'.e(self::embedUrl($s['address'], (int) ($s['zoom'] ?? 15))).'" title="'.e(__('Carte : :address', ['address' => $s['address']])).'" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>';
    }
}
