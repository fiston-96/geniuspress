<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Plugins\PluginManager;
use Throwable;

class PluginController extends Controller
{
    public function __construct(private PluginManager $plugins) {}

    public function index()
    {
        return view('admin.plugins.index', [
            'plugins' => $this->plugins->discover(),
            'active' => $this->plugins->activeSlugs(),
            'errors' => $this->plugins->errors(),
            'directory' => $this->plugins->directory(),
            'version' => config('geniuspress.version'),
        ]);
    }

    public function activate(string $slug)
    {
        abort_unless($this->plugins->find($slug), 404);

        try {
            $this->plugins->activate($slug);
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', __('Impossible d’activer « :plugin » : :error', ['plugin' => $slug, 'error' => $e->getMessage()]));
        }

        return back()->with('status', __('Plugin activé.'));
    }

    public function deactivate(string $slug)
    {
        abort_unless($this->plugins->find($slug), 404);
        $this->plugins->deactivate($slug);

        return back()->with('status', __('Plugin désactivé. Ses widgets restent dans vos pages mais ne s’affichent plus.'));
    }
}
