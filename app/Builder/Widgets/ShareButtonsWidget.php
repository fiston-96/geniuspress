<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Boutons de partage de la page affichée : WhatsApp, Facebook, X, Telegram, e-mail, copie du lien. */
class ShareButtonsWidget extends Widget
{
    public const NETWORKS = ['whatsapp', 'facebook', 'x', 'telegram', 'email', 'copy'];

    public function type(): string { return 'share-buttons'; }

    public function label(): string { return __('Boutons de partage'); }

    public function rules(): array
    {
        return [
            'whatsapp' => 'bool',
            'facebook' => 'bool',
            'x' => 'bool',
            'telegram' => 'bool',
            'email' => 'bool',
            'copy' => 'bool',
            'label' => 'string:60',
            'style' => 'enum:brand,outline,plain',
            'shape' => 'enum:circle,rounded,square',
            'size' => 'int:24:72',
            'align' => 'enum:left,center,right@r',
        ];
    }

    public function translatable(): array { return ['label']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $locale = $ctx->site?->locale;
        $url = request()->url();
        $title = $ctx->site ? $ctx->site->title() : '';
        $q = rawurlencode($url);
        $t = rawurlencode($title);
        $links = [
            'whatsapp' => ['brand:whatsapp', 'https://wa.me/?text='.rawurlencode(trim($title.' '.$url)), 'WhatsApp'],
            'facebook' => ['brand:facebook', 'https://www.facebook.com/sharer/sharer.php?u='.$q, 'Facebook'],
            'x' => ['brand:x', 'https://x.com/intent/post?url='.$q.'&text='.$t, 'X'],
            'telegram' => ['brand:telegram', 'https://t.me/share/url?url='.$q.'&text='.$t, 'Telegram'],
            'email' => ['lucide:mail', 'mailto:?subject='.$t.'&body='.$q, __('E-mail', [], $locale)],
        ];
        $ctx->rule($id, ['--gp-share-size' => isset($s['size']) ? $s['size'].'px' : null], false, ' .gp-share');
        $ctx->responsive($id, $s, 'align', fn ($v) => ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$v]], ' .gp-share');

        $html = '<div class="gp-share gp-share--'.e($s['style'] ?? 'brand').' gp-share--'.e($s['shape'] ?? 'circle').'">'
            .(! empty($s['label']) ? '<span class="gp-share__label">'.e($s['label']).'</span>' : '');
        foreach ($links as $key => [$icon, $href, $name]) {
            if ($s[$key] ?? true) {
                $html .= '<a class="gp-share__btn gp-share__btn--'.$key.'" href="'.e($href).'" target="_blank" rel="noopener" aria-label="'.e(__('Partager sur :name', ['name' => $name], $locale)).'">'.Icons::svg($icon).'</a>';
            }
        }
        if ($s['copy'] ?? true) {
            $html .= '<button type="button" class="gp-share__btn gp-share__btn--copy" data-gp-copy="'.e($url).'" data-done="'.e(__('Lien copié', [], $locale)).'" hidden aria-label="'.e(__('Copier le lien', [], $locale)).'">'.Icons::svg('lucide:link').'</button>';
        }

        return $html.'</div>';
    }
}
