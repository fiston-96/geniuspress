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
        {--db= : mysql ou sqlite}
        {--db-host=127.0.0.1} {--db-port=3306} {--db-name=geniuspress} {--db-user=root} {--db-password=}
        {--site-name= : Nom du site}
        {--locale=fr : Langue du site}
        {--admin-name=} {--admin-email=} {--admin-password=}
        {--url= : Adresse publique du site (ex. https://exemple.org)}
        {--blank : Commencer avec une page d’accueil vide}';

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
            $driver = $this->option('db') ?: select(__('Base de données'), ['mysql' => 'MySQL / MariaDB', 'sqlite' => 'SQLite'], 'mysql');
            $db = $driver === 'sqlite'
                ? ['driver' => 'sqlite', 'database' => config('geniuspress.sqlite_path')]
                : [
                    'driver' => 'mysql',
                    'host' => $this->ask(__('Serveur'), $this->option('db-host')),
                    'port' => (int) $this->ask(__('Port'), $this->option('db-port')),
                    'database' => $this->ask(__('Nom de la base'), $this->option('db-name')),
                    'username' => $this->ask(__('Utilisateur'), $this->option('db-user')),
                    'password' => $this->option('db-password') ?? ($this->input->isInteractive() ? password(__('Mot de passe')) : ''),
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
            'site_name' => $this->option('site-name') ?: text(__('Nom du site ou de l’organisation'), required: true),
            'locale' => $this->option('locale'),
            'name' => $this->option('admin-name') ?: text(__('Votre nom'), required: true),
            'email' => $this->option('admin-email') ?: text(__('Adresse e-mail'), required: true),
            'password' => $this->option('admin-password') ?: password(__('Mot de passe'), required: true, hint: __('10 caractères minimum, avec des lettres et des chiffres.')),
            'start' => $this->option('blank') ? 'blank' : 'welcome',
            'url' => $this->option('url'),
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

        $installer->createSite($site);
        $this->info(__('GeniusPress est installé. Connectez-vous sur :url', ['url' => rtrim($site['url'] ?: config('app.url'), '/').'/admin/login']));

        return self::SUCCESS;
    }
}
