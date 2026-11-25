<?php

namespace App\Hosting;

use App\Hosting\Database\SiteDatabase;
use App\Hosting\Events\SiteDeleted;
use App\Hosting\Events\SiteProvisioned;
use App\Hosting\Events\TrialExpired;
use App\Install\EnvironmentFile;
use App\Templates\Catalog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

/** Sites hébergés sur ce serveur, dans config('hosting.path'). */
class LocalSiteProvisioner implements SiteProvisioner
{
    public function __construct(
        private SiteDatabase $database,
        private SiteCommandRunner $runner,
        private HostingAccounts $accounts,
        private Catalog $catalog,
    ) {}

    public function create(SiteOwner $owner, string $name, string $slug, Plan $plan): Site
    {
        return Site::create([
            'slug' => $slug,
            'name' => $name,
            'account_id' => $owner->accountId,
            'owner_email' => $owner->email,
            'kind' => $plan->kind,
            'plan' => $plan->code,
            'status' => Site::STATUS_PROVISIONING,
            'host' => $slug.'.'.config('hosting.domain'),
            'limits' => $plan->limits,
            'expires_at' => $plan->days ? now()->addDays($plan->days) : null,
        ]);
    }

    public function build(Site $site): void
    {
        if ($site->status !== Site::STATUS_PROVISIONING) {
            return;
        }
        $dir = $site->directory();
        $created = false;

        try {
            $owner = $this->accounts->owner($site->account_id) ?? throw new HostingException(__('Le compte propriétaire de ce site n’existe plus.'));
            if (file_exists($dir)) {
                throw new HostingException(__('Le dossier du site :site existe déjà.', ['site' => $site->slug]));
            }

            foreach (['storage/app/public', 'storage/app/private', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'cache'] as $sub) {
                File::ensureDirectoryExists($dir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $sub), 0775);
            }
            $created = true;

            $db = $this->database->create($site);
            $site->database = $site->database ?: Str::limit(basename($db['DB_DATABASE']), 60, '');
            $this->writeEnvironment($site, $db, $owner);
            $this->writeManifest($site);

            // Informations du compte administrateur, lues puis effacées par geniuspress:hosted-setup.
            $setup = $dir.DIRECTORY_SEPARATOR.'setup.json';
            file_put_contents($setup, json_encode([
                'site_name' => $site->name,
                'locale' => $owner->locale && $owner->locale === 'en' ? 'en' : 'fr',
                'owner' => ['name' => $owner->name, 'email' => $owner->email, 'password_hash' => $owner->passwordHash, 'locale' => $owner->locale],
            ]));
            @chmod($setup, 0600);
            $this->runner->run($site->slug, ['geniuspress:hosted-setup']);

            $this->writeHost($site->host, $site->slug);
            $site->forceFill(['status' => Site::STATUS_ACTIVE, 'provisioned_at' => now(), 'error' => null])->save();
        } catch (Throwable $e) {
            report($e);
            if ($created) {
                $this->removeFiles($site);
            }
            // L'adresse est libérée : on peut réessayer.
            $site->forceFill(['status' => Site::STATUS_FAILED, 'slug' => null, 'error' => Str::limit($e->getMessage(), 990)])->save();

            throw $e instanceof HostingException ? $e : new HostingException(__('La création du site a échoué. Réessayez dans quelques minutes.'), 0, $e);
        }

        event(new SiteProvisioned($site));
    }

    public function suspend(Site $site, ?string $reason = null): void
    {
        $this->assertLive($site);
        $site->forceFill(['status' => Site::STATUS_SUSPENDED, 'suspended_at' => now(), 'suspend_reason' => $reason ? Str::limit($reason, 490) : null])->save();
        $this->writeManifest($site);
    }

    public function resume(Site $site): void
    {
        $this->assertLive($site);
        $expired = $site->expires_at && $site->expires_at->isPast();
        $site->forceFill([
            'status' => $expired ? Site::STATUS_EXPIRED : Site::STATUS_ACTIVE,
            'suspended_at' => null,
            'suspend_reason' => null,
            'expired_at' => $expired ? ($site->expired_at ?? now()) : null,
        ])->save();
        $this->writeManifest($site);
    }

    public function extend(Site $site, int $days): void
    {
        $this->assertLive($site);
        $from = $site->expires_at && $site->expires_at->isFuture() ? $site->expires_at : now();
        $site->expires_at = $from->copy()->addDays(max(1, $days));
        if ($site->status === Site::STATUS_EXPIRED) {
            $site->status = Site::STATUS_ACTIVE;
            $site->expired_at = null;
        }
        $site->save();
        $this->writeManifest($site);
    }

    public function expire(Site $site): void
    {
        $this->assertLive($site);
        $site->forceFill(['status' => Site::STATUS_EXPIRED, 'expired_at' => now()])->save();
        $this->writeManifest($site);
        event(new TrialExpired($site));
    }

    public function convert(Site $site, Plan $plan): void
    {
        $this->assertLive($site);
        $site->forceFill([
            'kind' => $plan->kind,
            'plan' => $plan->code,
            'limits' => $plan->limits,
            'status' => Site::STATUS_ACTIVE,
            'expires_at' => $plan->days ? now()->addDays($plan->days) : null,
            'expired_at' => null,
            'suspended_at' => null,
            'suspend_reason' => null,
        ])->save();
        $this->writeManifest($site);
    }

    public function delete(Site $site): void
    {
        if ($site->status === Site::STATUS_DELETED) {
            return;
        }
        if ($site->slug) {
            $this->removeFiles($site);
        }
        $site->forceFill([
            'status' => Site::STATUS_DELETED,
            'slug' => null,
            'host' => null,
            'database' => null,
            'removed_at' => now(),
        ])->save();
        event(new SiteDeleted($site));
    }

    public function migrate(Site $site): void
    {
        $this->assertLive($site);
        $this->runner->run($site->slug, ['migrate', '--force']);
    }

    public function loginUrl(Site $site): string
    {
        $manifest = $site->manifest() ?? throw new HostingException(__('Ce site n’est pas disponible.'));

        return LoginLink::for($site, $manifest);
    }

    /** Réécrit la fiche du site (état, date de fin, quotas), en gardant son secret de connexion. */
    public function writeManifest(Site $site): void
    {
        $previous = $site->manifest();
        (new Manifest([
            'slug' => $site->slug,
            'name' => $site->name,
            'kind' => $site->kind,
            'plan' => $site->plan,
            // En préparation : le site n'est joignable qu'une fois prêt (index des adresses écrit en dernier).
            'status' => match (true) {
                in_array($site->status, Site::LIVE, true) => $site->status,
                $site->status === Site::STATUS_PROVISIONING => Manifest::STATUS_ACTIVE,
                default => Manifest::STATUS_SUSPENDED,
            },
            'expires_at' => $site->expires_at?->toIso8601String(),
            'limits' => $site->limits ?? [],
            'owner' => ['account_id' => $site->account_id, 'email' => $site->owner_email],
            'login_secret' => $previous?->loginSecret() ?: bin2hex(random_bytes(32)),
            'upgrade_url' => $this->accounts->upgradeUrl($site),
            'suspend_reason' => $site->suspend_reason,
            'updated_at' => now()->toIso8601String(),
        ]))->save($site->manifestPath());
    }

    private function writeEnvironment(Site $site, array $db, SiteOwner $owner): void
    {
        $local = app()->isLocal();
        $env = new EnvironmentFile($site->directory().DIRECTORY_SEPARATOR.'.env');
        $env->createFrom(null);
        $env->set([
            'APP_NAME' => 'GeniusPress',
            'APP_ENV' => $local ? 'local' : 'production',
            'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
            'APP_DEBUG' => $local,
            'APP_URL' => $site->url(),
            'APP_LOCALE' => 'fr',
            'APP_FALLBACK_LOCALE' => 'fr',
            'LOG_CHANNEL' => 'single',
        ] + $db + [
            'SESSION_DRIVER' => 'database',
            'SESSION_COOKIE' => 'gp_site_session',
            'CACHE_STORE' => 'database',
            'QUEUE_CONNECTION' => 'sync',
            'FILESYSTEM_DISK' => 'public',
            'MAIL_MAILER' => (string) config('hosting.mailer', 'log'),
            // IA : clé et modèle de la plateforme, dans la limite des quotas du site.
            'ANTHROPIC_API_KEY' => (string) config('hosting.ai_key'),
            'GP_AI_MODEL' => (string) config('geniuspress.ai.model'),
            // Modèles : le catalogue de la plateforme.
            'GP_CATALOG_URL' => (string) config('geniuspress.catalog.url'),
            'GP_CATALOG_PATH' => (string) config('geniuspress.catalog.path'),
            'GP_CATALOG_LOCAL' => $this->catalog->isLocal(),
            'GP_CATALOG_DEMOS' => (bool) config('geniuspress.catalog.demos'),
            'GP_CATALOG_DEMO_URL' => (string) config('geniuspress.catalog.demo_url'),
        ]);
        @chmod($env->path(), 0600);
    }

    private function writeHost(string $host, string $slug): void
    {
        $host = HostedBootstrap::normalizeHost($host) ?? throw new HostingException(__('Adresse de site invalide.'));
        $dir = config('hosting.path').DIRECTORY_SEPARATOR.'hosts';
        File::ensureDirectoryExists($dir, 0775);
        file_put_contents($dir.DIRECTORY_SEPARATOR.$host, $slug);
    }

    private function removeFiles(Site $site): void
    {
        try {
            $this->database->drop($site);
        } catch (Throwable $e) {
            report($e);
        }
        // Index des adresses qui mènent à ce site (adresse d'essai et domaines rattachés).
        $hosts = config('hosting.path').DIRECTORY_SEPARATOR.'hosts';
        foreach (is_dir($hosts) ? File::files($hosts) : [] as $file) {
            if (trim((string) @file_get_contents($file->getPathname())) === $site->slug) {
                @unlink($file->getPathname());
            }
        }
        File::deleteDirectory($site->directory());
    }

    private function assertLive(Site $site): void
    {
        if (! $site->isLive()) {
            throw new HostingException(__('Ce site n’est pas disponible.'));
        }
    }
}
