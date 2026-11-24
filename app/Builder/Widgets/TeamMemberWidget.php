<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Membre d'une équipe : photo, nom, fonction, présentation, réseaux. */
class TeamMemberWidget extends Widget
{
    public function type(): string { return 'team-member'; }

    public function label(): string { return __('Membre de l’équipe'); }

    public function rules(): array
    {
        return [
            'url' => 'url',
            'media_id' => 'int:0:2147483647',
            'name' => 'string:120',
            'role' => 'string:160',
            'bio' => 'string:800',
            'socials' => 'list:8:icon=icon;url=url',
            'style' => 'enum:plain,card',
            'image_shape' => 'enum:square,rounded,circle',
            'align' => 'enum:left,center,right@r',
        ];
    }

    public function translatable(): array { return ['role', 'bio']; }

    public function fillable(): array { return ['name', 'role', 'bio']; }

    public function aiExcluded(): array { return ['url', 'media_id', 'socials']; }

    public function aiHint(): ?string
    {
        return 'Team member card (name, role, short bio). Never invent real names: use "[Prénom Nom]" and a role placeholder.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $media = $ctx->media($s['media_id'] ?? null);
        $photo = $media?->url ?? ($s['url'] ?? null);
        $ctx->rule($id, ['text-align' => $s['align'] ?? 'center'], false, ' .gp-team');
        $ctx->responsive($id, array_diff_key($s, ['align' => 1]), 'align', fn ($v) => ['text-align' => $v], ' .gp-team');

        $html = '<div class="gp-team gp-team--'.e($s['style'] ?? 'plain').'">';
        if ($photo) {
            $html .= '<img class="gp-team__photo gp-team__photo--'.e($s['image_shape'] ?? 'rounded').'" src="'.e($photo).'" alt="'.e($s['name'] ?? '').'" loading="lazy" decoding="async">';
        }
        $html .= '<h3 class="gp-team__name">'.e($s['name'] ?? '').'</h3>';
        if (isset($s['role'])) {
            $html .= '<p class="gp-team__role">'.e($s['role']).'</p>';
        }
        if (isset($s['bio'])) {
            $html .= '<p class="gp-team__bio">'.e($s['bio']).'</p>';
        }
        if (! empty($s['socials'])) {
            $html .= '<ul class="gp-team__socials">';
            foreach ($s['socials'] as $link) {
                if (isset($link['url'], $link['icon'])) {
                    $name = str_starts_with($link['icon'], 'brand:') ? (Icons::brandTitle(substr($link['icon'], 6)) ?? '') : '';
                    $html .= '<li><a href="'.e($link['url']).'" aria-label="'.e($name ?: $link['url']).'">'.Icons::svg($link['icon']).'</a></li>';
                }
            }
            $html .= '</ul>';
        }

        return $html.'</div>';
    }
}
