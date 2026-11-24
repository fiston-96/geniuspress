<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\SettingController;
use App\Install\EnvironmentFile;
use App\Install\Installer;
use App\Install\InstallState;
use App\Install\Requirements;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Installation en ligne de commande, interactive ou entièrement par options :
 *
 *   php artisan geniuspress:install --db=sqlite --site-name="ONG Espoir" \
 *       --admin-name="Amani" --admin-email=admin@exemple.org --admin-password=… -n
 */
class InstallCommand extends Command
{
    protected $signature = 'geniuspress:install
        {--db= : mysql ou sqlite (défaut : DB_CONNECTION)}
        {--db-host= : défaut DB_HOST ou 127.0.0.1} {--db-port= : défaut DB_PORT ou 3306}
        {--db-name= : défaut DB_DATABASE ou geniuspress} {--db-user= : défaut DB_USERNAME ou root}
        {--db-password= : défaut DB_PASSWORD}
        {--site-name= : Nom du site (défaut GP_SITE_NAME)}
        {--locale= : Langue du site (défaut GP_SITE_LOCALE ou fr)}
        {--admin-name= : défaut GP_ADMIN_NAME} {--admin-email= : défaut GP_ADMIN_EMAIL}
        {--admin-password= : défaut GP_ADMIN_PASSWORD (évite de le laisser dans l’historique du shell)}
        {--url= : Adresse publique du site (ex. https://exemple.org)}
        {--blank : Commencer avec une page d’accueil vide}
        {--import= : Importer un site exporté (fichier zip) à la place de la page d’accueil}';

    protected $description = 'Installe GeniusPress (base de données, site et compte administrateur)';

    public function handle(InstallState $state, Installer $installer, Requirements $requirements, EnvironmentFile $env): int
    {
        if ($state->isInstalled()) {
            $this->info(__('GeniusPress est déjà installé.'));

            return self::SUCCESS;
        }

        // 1. Serveur
        $checks = $requirements->check($env);
        foreach ($checks as $c) {
            if (! $c['ok']) {
                $c['required'] ? $this->error('✗ '.$c['label']) : $this->warn('! '.$c['label'].' — '.$c['detail']);
            }
        }
        if (! $requirements->passes($checks)) {
            return self::FAILURE;
        }

        // 2. Base de données
        if (! $state->databaseIsReady()) {
            $driver = $this->opt('db', 'DB_CONNECTION') ?: select(__('Base de données'), ['mysql' => 'MySQL / MariaDB', 'sqlite' => 'SQLite'], 'mysql');
            $db = $driver === 'sqlite'
                ? ['driver' => 'sqlite', 'database' => config('geniuspress.sqlite_path')]
                : [
                    'driver' => 'mysql',
                    'host' => $this->ask(__('Serveur'), $this->opt('db-host', 'DB_HOST', '127.0.0.1')),
                    'port' => (int) $this->ask(__('Port'), $this->opt('db-port', 'DB_PORT', '3306')),
                    'database' => $this->ask(__('Nom de la base'), $this->opt('db-name', 'DB_DATABASE', 'geniuspress')),
                    'username' => $this->ask(__('Utilisateur'), $this->opt('db-user', 'DB_USERNAME', 'root')),
                    'password' => $this->opt('db-password', 'DB_PASSWORD') ?? ($this->input->isInteractive() ? password(__('Mot de passe')) : ''),
                    'create' => true,
                ];

            if ($error = $installer->testDatabase($db)) {
                $this->error($error);

                return self::FAILURE;
            }
            $installer->configureDatabase($db);
            $this->info(__('Base de données prête.'));
        }

        // 3. Site et compte administrateur
        $site = [
            'site_name' => $this->opt('site-name', 'GP_SITE_NAME') ?: text(__('Nom du site ou de l’organisation'), required: true),
            'locale' => $this->opt('locale', 'GP_SITE_LOCALE', 'fr'),
            'name' => $this->opt('admin-name', 'GP_ADMIN_NAME') ?: text(__('Votre nom'), required: true),
            'email' => $this->opt('admin-email', 'GP_ADMIN_EMAIL') ?: text(__('Adresse e-mail'), required: true),
            'password' => $this->opt('admin-password', 'GP_ADMIN_PASSWORD') ?: password(__('Mot de passe'), required: true, hint: __('10 caractères minimum, avec des lettres et des chiffres.')),
            'start' => $this->option('import') ? 'import' : ($this->option('blank') ? 'blank' : 'welcome'),
            'url' => $this->opt('url', 'APP_URL'),
        ];

        $validator = Validator::make($site, [
            'site_name' => ['required', 'string', 'max:120'],
            'locale' => ['required', 'in:'.implode(',', array_keys(SettingController::AVAILABLE_LOCALES))],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', Password::min(10)->letters()->numbers()],
            'url' => ['nullable', 'url'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        if ($site['start'] === 'import' && ! is_file($this->option('import'))) {
            $this->error(__('Fichier introuvable : :path', ['path' => $this->option('import')]));

            return self::FAILURE;
        }

        $user = $installer->createSite($site);

        if ($site['start'] === 'import') {
            $report = app(\App\Transfer\SiteImporter::class)->import($this->option('import'), $user, replace: true, withSettings: true);
            \App\Models\Setting::write(['site_name' => $site['site_name']]);
            foreach ($report['warnings'] as $warning) {
                $this->warn($warning);
            }
            $this->info(__('Import terminé : :pages pages et :media médias.', ['pages' => $report['pages'], 'media' => $report['media']]));
        }
        $this->info(__('GeniusPress est installé. Connectez-vous sur :url', ['url' => rtrim($site['url'] ?: config('app.url'), '/').'/admin/login']));

        return self::SUCCESS;
    }

    /** Option de la commande, sinon variable d'environnement, sinon valeur par défaut. */
    private function opt(string $option, string $env, ?string $default = null): ?string
    {
        $value = $this->option($option);
        if ($value !== null && $value !== '') {
            return (string) $value;
        }
        $fromEnv = getenv($env);

        return $fromEnv !== false && $fromEnv !== '' ? $fromEnv : $default;
    }
}
