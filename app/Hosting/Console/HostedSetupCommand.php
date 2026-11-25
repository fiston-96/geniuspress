<?php

namespace App\Hosting\Console;

use App\Builder\DefaultContent;
use App\Hosting\CurrentSite;
use App\Install\InstallState;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Installe un site hébergé qui vient d'être préparé : tables, compte
 * administrateur du propriétaire, page d'accueil, verrou d'installation.
 * Lancée par la plateforme dans le contexte du site (GP_SITE=…), jamais à la main.
 */
class HostedSetupCommand extends Command
{
    protected $signature = 'geniuspress:hosted-setup';

    protected $description = 'Installe un site hébergé (usage interne de la plateforme)';

    protected $hidden = true;

    public function handle(CurrentSite $site, InstallState $state): int
    {
        if (! $site->isHosted()) {
            $this->error(__('Cette commande ne s’exécute que dans un site hébergé (GP_SITE=…).'));

            return self::FAILURE;
        }
        if ($state->isInstalled()) {
            $this->info(__('GeniusPress est déjà installé.'));

            return self::SUCCESS;
        }

        $path = dirname((string) getenv('GP_HOSTED_SITE')).DIRECTORY_SEPARATOR.'setup.json';
        $setup = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        if (! is_array($setup) || empty($setup['owner']['email'])) {
            $this->error(__('Informations d’installation introuvables.'));

            return self::FAILURE;
        }

        Artisan::call('migrate', ['--force' => true]);

        DB::transaction(function () use ($setup) {
            $owner = $setup['owner'];
            // Mot de passe déjà chiffré par la plateforme : inséré tel quel.
            $id = DB::table('users')->insertGetId([
                'name' => Str::limit((string) $owner['name'], 250, ''),
                'email' => (string) $owner['email'],
                'password' => $owner['password_hash'] ?: bcrypt(Str::random(40)),
                'role' => User::ROLE_ADMIN,
                'locale' => in_array($owner['locale'] ?? null, array_keys(config('geniuspress.ui_locales')), true) ? $owner['locale'] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $locale = $setup['locale'] ?? 'fr';
            Setting::write(['site_name' => $setup['site_name'], 'locales' => [$locale]]);
            Page::create([
                'title' => $locale === 'en' ? 'Home' : 'Accueil',
                'slug' => $locale === 'en' ? 'home' : 'accueil',
                'locale' => $locale,
                'is_home' => true,
                'status' => Page::STATUS_PUBLISHED,
                'published_at' => now(),
                'author_id' => $id,
                'content' => DefaultContent::home($setup['site_name']),
            ]);
        });

        $state->markInstalled();
        @unlink($path);
        $this->info(__('Site hébergé installé.'));

        return self::SUCCESS;
    }
}
