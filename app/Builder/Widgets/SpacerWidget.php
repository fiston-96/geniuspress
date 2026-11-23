<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

class SpacerWidget extends Widget
{
    public function type(): string { return 'spacer'; }

    public function label(): string { return __('Espace'); }

    public function rules(): array
    {
        return ['height' => 'int:0:600', 'height_mobile' => 'int:0:600'];
    }

    public function aiHint(): ?string { return 'Vertical space.'; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['height' => RenderContext::px($s['height'] ?? 40)]);
        $ctx->rule($id, ['height' => RenderContext::px($s['height_mobile'] ?? null)], true);

        return '';
    }
}
