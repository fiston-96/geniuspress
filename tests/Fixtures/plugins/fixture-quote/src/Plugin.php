<?php

namespace Fixtures\Quote;

use App\Builder\RenderContext;
use App\Builder\Widgets\Widget;
use App\Plugins\Plugin as BasePlugin;
use Illuminate\Support\Facades\Route;

class Plugin extends BasePlugin
{
    public static array $published = [];

    public function boot(): void
    {
        $this->widget(new class extends Widget {
            public function type(): string { return 'fixture-quote'; }
            public function label(): string { return 'Fixture quote'; }
            public function rules(): array { return ['text' => 'string:100', 'accent' => 'color']; }
            public function translatable(): array { return ['text']; }
            public function render(array $s, string $id, RenderContext $ctx): string
            {
                $ctx->rule($id, ['color' => $s['accent'] ?? null]);

                return '<q class="fixture">'.e($s['text'] ?? '').'</q>';
            }
        });
        $this->filter('page.head', fn (string $html) => $html.'<meta name="fixture" content="ok">');
        $this->on('page.published', fn ($page) => self::$published[] = $page->id);
        $this->routes(fn () => Route::get('/hello', fn () => 'bonjour du plugin')->name('hello'));
    }
}
