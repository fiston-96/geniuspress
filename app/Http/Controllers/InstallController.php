<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\SettingController;
use App\Install\EnvironmentFile;
use App\Install\Installer;
use App\Install\InstallState;
use App\Install\Requirements;
use App\Models\Setting;
use App\Transfer\SiteImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Throwable;

/**
 * Assistant d'installation en 3 étapes : serveur, base de données, site.
 * Inaccessible (404) dès que l'installation est terminée (middleware InstallerOpen).
 */
class InstallController extends Controller
{
    public function __construct(private InstallState $state, private EnvironmentFile $env) {}

    /** Étape 1 : vérification du serveur. */
    public function show(Requirements $requirements)
    {
        $checks = $requirements->check($this->env);

        return view('install.requirements', [
            'checks' => $checks,
            'passes' => $requirements->passes($checks),
            'next' => $this->state->databaseIsReady() ? route('install.site') : route('install.database'),
        ]);
    }

    /** Étape 2 : base de données. */
    public function database()
    {
        // Valeurs déjà fournies : variables du serveur (Docker, hébergeur) ou fichier .env.
        $read = fn (string $key) => getenv($key) !== false ? getenv($key) : $this->env->get($key);
        $isMysql = in_array($read('DB_CONNECTION'), ['mysql', 'mariadb'], true);
        $current = fn (string $key, string $default = '') => old($key, ($isMysql ? $read($key) : null) ?: $default);

        return view('install.database', [
            'values' => [
                // MySQL par défaut (site en ligne), sauf si le serveur ne l'a pas.
                'driver' => old('driver', extension_loaded('pdo_mysql') ? 'mysql' : 'sqlite'),
                'host' => $current('DB_HOST', '127.0.0.1'),
                'port' => $current('DB_PORT', '3306'),
                'database' => $current('DB_DATABASE', 'geniuspress'),
                'username' => $current('DB_USERNAME', 'root'),
            ],
            'sqlitePath' => config('geniuspress.sqlite_path'),
            'serverPassword' => getenv('DB_PASSWORD') !== false && getenv('DB_PASSWORD') !== '',
            'hasMysql' => extension_loaded('pdo_mysql'),
            'hasSqlite' => extension_loaded('pdo_sqlite'),
        ]);
    }

    public function saveDatabase(Request $request, Installer $installer)
    {
        $data = $request->validate([
            'driver' => ['required', Rule::in(['mysql', 'sqlite'])],
            'host' => ['exclude_if:driver,sqlite', 'required', 'string', 'max:255', 'regex:/^[A-Za-z0-9.\-:\[\]_]+$/'],
            'port' => ['exclude_if:driver,sqlite', 'required', 'integer', 'between:1,65535'],
            'database' => ['exclude_if:driver,sqlite', 'required', 'string', 'max:64'],
            'username' => ['exclude_if:driver,sqlite', 'required', 'string', 'max:128'],
            'password' => ['exclude_if:driver,sqlite', 'nullable', 'string', 'max:255'],
            'create' => ['exclude_if:driver,sqlite', 'boolean'],
        ]);
        if ($data['driver'] === 'sqlite') {
            $data['database'] = config('geniuspress.sqlite_path');
        }
        // Champ vide : mot de passe fourni par le serveur (Docker, hébergeur), s'il y en a un.
        $data['password'] = (string) ($request->input('password') ?: (getenv('DB_PASSWORD') ?: ''));

        if ($error = $installer->testDatabase($data)) {
            return back()->withInput($request->except('password'))->withErrors(['database' => $error]);
        }

        try {
            $installer->configureDatabase($data);
        } catch (Throwable $e) {
            report($e);

            return back()->withInput($request->except('password'))
                ->withErrors(['database' => __('Connexion réussie, mais la création des tables a échoué : :error', ['error' => mb_substr($e->getMessage(), 0, 200)])]);
        }

        return redirect()->route('install.site');
    }

    /** Étape 3 : le site et le compte administrateur. */
    public function site()
    {
        if (! $this->state->databaseIsReady()) {
            return redirect()->route('install.database');
        }

        return view('install.site', ['locales' => SettingController::AVAILABLE_LOCALES]);
    }

    public function saveSite(Request $request, Installer $installer, SiteImporter $importer)
    {
        if (! $this->state->databaseIsReady()) {
            return redirect()->route('install.database');
        }

        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'locale' => ['required', Rule::in(array_keys(SettingController::AVAILABLE_LOCALES))],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
            'start' => ['required', Rule::in(['welcome', 'blank', 'import'])],
            'archive' => ['required_if:start,import', 'nullable', 'file', 'mimes:zip', 'max:512000'],
        ]);

        $user = $installer->createSite($data + ['url' => $request->root()]);

        Auth::login($user);
        $request->session()->regenerate();
        $redirect = redirect()->route('admin.dashboard')->with('status', __('Bienvenue sur GeniusPress !'));

        // Déménagement d'un site existant : on importe l'archive dans le site tout neuf.
        if ($data['start'] === 'import') {
            @set_time_limit(300);
            try {
                $report = $importer->import($request->file('archive')->getRealPath(), $user, replace: true, withSettings: true);
                Setting::write(['site_name' => $data['site_name']]); // Le nom saisi l'emporte.
                $redirect->with('status', __('Bienvenue sur GeniusPress ! Import terminé : :pages pages et :media médias.', ['pages' => $report['pages'], 'media' => $report['media']]))
                    ->with('warnings', $report['warnings']);
            } catch (Throwable $e) {
                report($e);
                $redirect->with('status', __('GeniusPress est installé, mais l’import a échoué : :error Vous pouvez le relancer depuis Sauvegarde.', ['error' => $e->getMessage()]));
            }
        }

        return $redirect;
    }

    /** Langue de l'assistant, avant que le compte existe. */
    public function language(Request $request, string $locale)
    {
        if (array_key_exists($locale, config('geniuspress.ui_locales'))) {
            $request->session()->put('locale', $locale);
        }

        return back();
    }
}
