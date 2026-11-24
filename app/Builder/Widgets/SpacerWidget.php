<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

class SpacerWidget extends Widget
{
    public function type(): string { return 'spacer'; }

    public function label(): string { return __('Espace'); }

    public function rules(): array
    {
        return ['height' => 'int:0:600@r'];
    }

    public function aiHint(): ?string { return 'Vertical space.'; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['height' => RenderContext::px($s['height'] ?? 40)]);
        $ctx->responsive($id, array_diff_key($s, ['height' => 1]), 'height', fn ($v) => ['height' => $v.'px']);

        return '';
    }
}
