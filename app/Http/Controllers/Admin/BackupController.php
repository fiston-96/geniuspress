<?php

namespace App\Http\Controllers\Admin;

use App\Backup\Backups;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Throwable;

/** Sauvegardes complètes (Sauvegarde → Sauvegardes complètes) : créer, télécharger, restaurer, supprimer, programmer. */
class BackupController extends Controller
{
    public function __construct(private Backups $backups) {}

    public function store()
    {
        try {
            $name = $this->backups->create();
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['backup' => $e->getMessage()]);
        }

        return back()->with('status', __('Sauvegarde créée : :name', ['name' => $name]));
    }

    public function download(string $name)
    {
        $path = $this->backups->path($name);
        abort_unless($path, 404);

        return response()->download($path, $name, ['Content-Type' => 'application/zip']);
    }

    public function destroy(string $name)
    {
        $path = $this->backups->path($name);
        abort_unless($path, 404);
        @unlink($path);

        return back()->with('status', __('Sauvegarde supprimée.'));
    }

    /** Restaure une sauvegarde gardée sur le serveur. */
    public function restore(string $name)
    {
        $path = $this->backups->path($name);
        abort_unless($path, 404);

        return $this->doRestore($path);
    }

    /** Restaure une sauvegarde envoyée depuis l'ordinateur. */
    public function upload(Request $request)
    {
        $request->validate(['backup' => ['required', 'file', 'mimes:zip']]);

        return $this->doRestore($request->file('backup')->getRealPath());
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'backup_frequency' => ['required', 'in:'.implode(',', Backups::FREQUENCIES)],
            'backup_keep' => ['required', 'integer', 'between:1,60'],
        ]);
        Setting::write($data);

        return back()->with('status', __('Réglages des sauvegardes enregistrés.'));
    }

    /** Tâches planifiées : déclenchement par les visites, nouvelle adresse secrète, lancement immédiat. */
    public function cron(Request $request)
    {
        $data = $request->validate(['cron_visits' => ['nullable', 'boolean'], 'action' => ['nullable', 'in:save,renew,run']]);
        $action = $data['action'] ?? 'save';
        if ($action === 'renew') {
            Setting::write(['cron_token' => null]);
            \App\Scheduling\WebCron::token();

            return back()->with('status', __('Nouvelle adresse secrète créée : mettez-la à jour chez votre hébergeur ou votre service de cron.'));
        }
        if ($action === 'run') {
            return back()->with('status', \App\Scheduling\WebCron::run('manual') ? __('Tâches planifiées lancées.') : __('Les tâches planifiées ont rencontré une erreur : consultez le journal.'));
        }
        Setting::write(['cron_visits' => $request->boolean('cron_visits')]);

        return back()->with('status', __('Réglages des tâches planifiées enregistrés.'));
    }

    private function doRestore(string $path)
    {
        try {
            $result = $this->backups->restore($path);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['backup' => $e->getMessage()]);
        }

        // Les comptes viennent de la sauvegarde : on se reconnecte.
        auth()->logout();
        request()->session()->invalidate();

        return redirect()->route('login')->with('status', __('Site restauré : :rows enregistrements et :files fichiers. Reconnectez-vous. Une copie de l’état précédent a été gardée : :safety', [
            'rows' => $result['rows'], 'files' => $result['files'], 'safety' => $result['safety'],
        ]));
    }
}
