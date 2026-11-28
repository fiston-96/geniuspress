<?php

namespace App\Scheduling;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

/**
 * Tâches planifiées sans cron (hébergement mutualisé) : le planificateur de Laravel
 * (routes/console.php : newsletter, sauvegardes automatiques…) est lancé
 *  - par les visites du site, au plus une fois par minute, après l'envoi de la page ;
 *  - ou par une adresse secrète (/cron/{jeton}) appelée par le cron de l'hébergeur ou un
 *    service gratuit (cron-job.org…).
 * Dès qu'un vrai cron lance « schedule:run » en ligne de commande, les visites ne le font plus.
 */
final class WebCron
{
    /** Un lancement web est en cours : ne pas le prendre pour un vrai cron. */
    public static bool $running = false;

    private const CLI_SEEN = 'gp-cron-cli-seen';

    private const LOCK = 'gp-web-cron';

    /** Jeton de l'adresse secrète (créé au premier besoin, réglage privé). */
    public static function token(): string
    {
        $token = Setting::read('cron_token');
        if (! is_string($token) || strlen($token) < 32) {
            $token = Str::random(40);
            Setting::write(['cron_token' => $token]);
        }

        return $token;
    }

    public static function url(): string
    {
        return route('cron.run', ['token' => self::token()]);
    }

    /** Un vrai cron a lancé le planificateur dans les 10 dernières minutes. */
    public static function cliActive(): bool
    {
        return Cache::has(self::CLI_SEEN);
    }

    public static function markCli(): void
    {
        if (! self::$running) {
            Cache::put(self::CLI_SEEN, now()->toIso8601String(), now()->addMinutes(10));
            Setting::write(['cron_last_run' => now()->toIso8601String(), 'cron_last_source' => 'cli']);
        }
    }

    /** Déclenchement par une visite : au plus une fois par minute, jamais si un vrai cron tourne. */
    public static function onVisit(): void
    {
        if (self::cliActive() || ! (bool) Setting::read('cron_visits', true)) {
            return;
        }
        $lock = Cache::lock(self::LOCK, 55);
        if (! $lock->get()) {
            return;
        }
        self::run('visits');
    }

    /** Lance le planificateur (tâches dues seulement). */
    public static function run(string $source): bool
    {
        self::$running = true;
        try {
            @set_time_limit(300);
            Artisan::call('schedule:run');
            Setting::write(['cron_last_run' => now()->toIso8601String(), 'cron_last_source' => $source]);

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        } finally {
            self::$running = false;
        }
    }
}
