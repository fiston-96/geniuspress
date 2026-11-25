<?php

namespace App\Hosting\Console;

use App\Hosting\HostingAccounts;
use App\Hosting\HostingException;
use App\Hosting\Site;
use App\Hosting\SiteProvisioner;
use App\Hosting\Trials;
use App\Models\User;
use Illuminate\Console\Command;
use Throwable;

/**
 * Administration des sites d'essai hébergés, pour l'exploitant :
 *
 *   php artisan geniuspress:trial list
 *   php artisan geniuspress:trial create --email=amani@exemple.org --name="Coopérative" --site=cooperative
 *   php artisan geniuspress:trial extend cooperative --days=7
 *   php artisan geniuspress:trial suspend cooperative --reason="…"
 *   php artisan geniuspress:trial resume cooperative
 *   php artisan geniuspress:trial delete cooperative --force
 *   php artisan geniuspress:trial expire      (planifiée toutes les heures)
 *   php artisan geniuspress:trial migrate     (après chaque mise à jour du code)
 */
class TrialCommand extends Command
{
    protected $signature = 'geniuspress:trial
        {action : list, create, extend, suspend, resume, delete, expire ou migrate}
        {site? : Adresse du site (ex. cooperative)}
        {--email= : create : e-mail du compte propriétaire}
        {--name= : create : nom du site}
        {--site= : create : adresse souhaitée}
        {--days=7 : extend : nombre de jours ajoutés}
        {--reason= : suspend : raison (visible par le propriétaire)}
        {--force : delete : sans confirmation}';

    protected $description = 'Gère les sites d’essai hébergés (création, prolongation, suspension, expiration, suppression)';

    public function handle(SiteProvisioner $provisioner, Trials $trials, HostingAccounts $accounts): int
    {
        try {
            return match ($this->argument('action')) {
                'list' => $this->list(),
                'create' => $this->createTrial($provisioner, $trials, $accounts),
                'extend' => $this->withSite(fn (Site $s) => $provisioner->extend($s, (int) $this->option('days')), __('Essai prolongé.')),
                'suspend' => $this->withSite(fn (Site $s) => $provisioner->suspend($s, $this->option('reason')), __('Site suspendu.')),
                'resume' => $this->withSite(fn (Site $s) => $provisioner->resume($s), __('Site rétabli.')),
                'delete' => $this->deleteSite($provisioner),
                'expire' => $this->expire($provisioner),
                'migrate' => $this->migrateAll($provisioner),
                default => $this->invalid(),
            };
        } catch (HostingException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function list(): int
    {
        $sites = Site::query()->where('status', '!=', Site::STATUS_DELETED)->orderBy('id')->get();
        if ($sites->isEmpty()) {
            $this->info(__('Aucun site hébergé.'));

            return self::SUCCESS;
        }
        $this->table(
            [__('Adresse'), __('Nom'), __('Propriétaire'), __('Offre'), __('État'), __('Fin')],
            $sites->map(fn (Site $s) => [$s->slug ?? '—', $s->name, $s->owner_email, $s->plan, $s->effectiveStatus(), $s->expires_at?->format('Y-m-d H:i') ?? '—'])->all(),
        );

        return self::SUCCESS;
    }

    private function createTrial(SiteProvisioner $provisioner, Trials $trials, HostingAccounts $accounts): int
    {
        $user = User::query()->where('email', (string) $this->option('email'))->first();
        if (! $user) {
            $this->error(__('Aucun compte avec cette adresse e-mail.'));

            return self::FAILURE;
        }
        $name = (string) ($this->option('name') ?: $user->name);
        $slug = strtolower((string) ($this->option('site') ?: $trials->suggestSlug($name)));
        if ($error = $trials->slugError($slug)) {
            throw new HostingException($error);
        }
        if ($error = $trials->capacityRefusal()) {
            throw new HostingException($error);
        }
        // L'exploitant peut créer un essai même si le compte a déjà eu le sien.
        $owner = $accounts->owner($accounts->accountId($user)) ?? throw new HostingException(__('Compte introuvable.'));
        $site = $provisioner->create($owner, $name, $slug, $accounts->trialPlan($user));
        $provisioner->build($site);
        $this->info(__('Site d’essai créé : :url', ['url' => $site->url()]));

        return self::SUCCESS;
    }

    private function withSite(callable $action, string $done): int
    {
        $site = $this->site();
        $action($site);
        $this->info($done.' '.__('État : :status, fin : :end.', ['status' => $site->effectiveStatus(), 'end' => $site->expires_at?->format('Y-m-d H:i') ?? '—']));

        return self::SUCCESS;
    }

    private function deleteSite(SiteProvisioner $provisioner): int
    {
        $site = $this->site();
        if (! $this->option('force') && ! $this->confirm(__('Supprimer définitivement le site :site et toutes ses données ?', ['site' => $site->slug]))) {
            return self::FAILURE;
        }
        $provisioner->delete($site);
        $this->info(__('Site supprimé.'));

        return self::SUCCESS;
    }

    /** Termine les essais arrivés à leur date de fin, puis supprime ceux dont le délai de grâce est passé. */
    private function expire(SiteProvisioner $provisioner): int
    {
        $expired = 0;
        foreach (Site::query()->where('kind', 'trial')->where('status', Site::STATUS_ACTIVE)->where('expires_at', '<=', now())->get() as $site) {
            $provisioner->expire($site);
            $expired++;
        }

        $deleted = 0;
        $grace = (int) config('hosting.trial.grace_days', 30);
        $due = Site::query()->where('kind', 'trial')->where('status', Site::STATUS_EXPIRED)->where('expired_at', '<=', now()->subDays($grace))->get();
        foreach ($due as $site) {
            $provisioner->delete($site);
            $deleted++;
        }

        // Créations échouées ou bloquées depuis plus d'un jour : traces effacées.
        Site::query()->where('status', Site::STATUS_PROVISIONING)->where('created_at', '<=', now()->subDay())
            ->get()->each(fn (Site $s) => $s->forceFill(['status' => Site::STATUS_FAILED, 'slug' => null, 'error' => 'Préparation interrompue.'])->save());

        $this->info(__(':expired essai(s) terminé(s), :deleted site(s) supprimé(s).', ['expired' => $expired, 'deleted' => $deleted]));

        return self::SUCCESS;
    }

    private function migrateAll(SiteProvisioner $provisioner): int
    {
        $sites = $this->argument('site') ? [$this->site()] : Site::query()->live()->orderBy('id')->get();
        $failed = 0;
        foreach ($sites as $site) {
            try {
                $provisioner->migrate($site);
                $this->line('✓ '.$site->slug);
            } catch (Throwable $e) {
                $failed++;
                $this->error('✗ '.$site->slug.' : '.$e->getMessage());
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function site(): Site
    {
        $slug = (string) $this->argument('site');

        return Site::query()->where('slug', $slug)->first() ?? throw new HostingException(__('Aucun site à l’adresse « :site ».', ['site' => $slug]));
    }

    private function invalid(): int
    {
        $this->error(__('Action inconnue. Actions : list, create, extend, suspend, resume, delete, expire, migrate.'));

        return self::FAILURE;
    }
}
