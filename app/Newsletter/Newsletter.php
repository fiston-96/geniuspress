<?php

namespace App\Newsletter;

use App\Mail\NewsletterMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewsletterConfirmation;
use App\Notifications\NewSubscriber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

/**
 * Newsletter intégrée à chaque site GeniusPress.
 *
 * - Inscription avec double confirmation : l'abonné reçoit un lien et n'est inscrit qu'après
 *   l'avoir ouvert (sauf adresse déjà vérifiée, par exemple un compte créé avec Google).
 * - Chaque e-mail contient un lien de désinscription en un clic (et l'en-tête List-Unsubscribe).
 * - Une campagne est envoyée par lots, au rythme du réglage newsletter_per_minute : un envoi par
 *   abonné et par campagne, enregistré, pour ne jamais envoyer deux fois et reprendre après une coupure.
 */
class Newsletter
{
    public const DEFAULT_PER_MINUTE = 30;

    /**
     * Inscrit une adresse. Renvoie l'abonné ; une adresse déjà inscrite reste inscrite, une
     * adresse désinscrite repasse par la confirmation.
     */
    public function subscribe(string $email, ?string $name = null, ?string $locale = null, ?string $source = null, bool $verified = false, ?string $ip = null): NewsletterSubscriber
    {
        $email = Str::lower(trim($email));
        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $email]);
        if ($subscriber->exists && $subscriber->status === NewsletterSubscriber::SUBSCRIBED) {
            return $subscriber;
        }

        $subscriber->fill([
            'name' => $name !== null && trim($name) !== '' ? Str::limit(trim(strip_tags($name)), 120, '') : $subscriber->name,
            'locale' => in_array($locale, array_keys((array) config('geniuspress.ui_locales')), true) ? $locale : ($subscriber->locale ?? 'fr'),
            'source' => $source ? Str::limit(strip_tags($source), 120, '') : $subscriber->source,
            'ip_hash' => $ip ? hash('sha256', $ip.'|'.config('app.key')) : $subscriber->ip_hash,
            'token' => $subscriber->token ?: Str::random(48),
            'status' => $verified ? NewsletterSubscriber::SUBSCRIBED : NewsletterSubscriber::PENDING,
            'confirmed_at' => $verified ? now() : null,
            'unsubscribed_at' => null,
        ])->save();

        if ($verified) {
            $this->announce($subscriber);
        } else {
            try {
                Notification::route('mail', $subscriber->email)->notify((new NewsletterConfirmation(route('newsletter.confirm', $subscriber->token)))->locale($subscriber->locale));
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $subscriber;
    }

    /** Lien de confirmation ouvert : l'abonné est inscrit (une seule annonce aux administrateurs). */
    public function confirm(string $token): ?NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::query()->where('token', $token)->first();
        if (! $subscriber || $subscriber->status === NewsletterSubscriber::UNSUBSCRIBED) {
            return $subscriber;
        }
        if ($subscriber->status !== NewsletterSubscriber::SUBSCRIBED) {
            $subscriber->update(['status' => NewsletterSubscriber::SUBSCRIBED, 'confirmed_at' => now()]);
            $this->announce($subscriber);
        }

        return $subscriber;
    }

    public function unsubscribe(string $token): ?NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::query()->where('token', $token)->first();
        if ($subscriber && $subscriber->status !== NewsletterSubscriber::UNSUBSCRIBED) {
            $subscriber->update(['status' => NewsletterSubscriber::UNSUBSCRIBED, 'unsubscribed_at' => now()]);
            // Les envois pas encore partis ne partiront pas.
            $subscriber->deliveries()->where('status', 'pending')->delete();
        }

        return $subscriber;
    }

    /** Met une campagne en file : un envoi par abonné inscrit (dans la langue choisie, le cas échéant). */
    public function queue(NewsletterCampaign $campaign): int
    {
        return DB::transaction(function () use ($campaign) {
            $locked = NewsletterCampaign::query()->whereKey($campaign->id)->lockForUpdate()->firstOrFail();
            if (! $locked->isDraft()) {
                return 0;
            }
            $now = now();
            $count = 0;
            NewsletterSubscriber::query()->subscribed()
                ->when($locked->locale, fn ($q, $locale) => $q->where('locale', $locale))
                ->select('id')->chunkById(500, function ($chunk) use ($locked, $now, &$count) {
                    NewsletterDelivery::query()->insertOrIgnore($chunk->map(fn ($s) => [
                        'campaign_id' => $locked->id, 'subscriber_id' => $s->id, 'status' => 'pending', 'created_at' => $now, 'updated_at' => $now,
                    ])->all());
                    $count += $chunk->count();
                });
            $locked->update(['status' => NewsletterCampaign::SENDING, 'recipients' => $count, 'started_at' => $now]);
            $campaign->setRawAttributes($locked->getAttributes(), true);

            return $count;
        });
    }

    /**
     * Envoie au plus $limit e-mails en attente (toutes campagnes en cours). Renvoie le nombre
     * traité ; une campagne sans envoi en attente passe à « envoyée ».
     */
    public function sendBatch(?int $limit = null): int
    {
        $limit ??= self::perMinute();
        $done = 0;
        // Envois réservés par un processus interrompu (coupure, délai dépassé) : remis en attente.
        NewsletterDelivery::query()->where('status', 'sending')->where('updated_at', '<', now()->subMinutes(15))->update(['status' => 'pending']);
        $deliveries = NewsletterDelivery::query()->with(['campaign', 'subscriber'])->where('status', 'pending')
            ->whereHas('campaign', fn ($q) => $q->where('status', NewsletterCampaign::SENDING))
            ->orderBy('id')->limit($limit)->get();

        foreach ($deliveries as $delivery) {
            // Réservation : un autre processus qui enverrait en même temps passe son tour.
            if (! NewsletterDelivery::query()->whereKey($delivery->id)->where('status', 'pending')->update(['status' => 'sending'])) {
                continue;
            }
            try {
                Mail::to($delivery->subscriber->email, $delivery->subscriber->name)
                    ->locale($delivery->subscriber->locale)
                    ->send(new NewsletterMail($delivery->campaign, $delivery->subscriber));
                $delivery->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
                $delivery->campaign->increment('sent_count');
            } catch (Throwable $e) {
                report($e);
                $delivery->update(['status' => 'failed', 'error' => Str::limit($e->getMessage(), 250, '')]);
                $delivery->campaign->increment('failed_count');
            }
            $done++;
        }

        NewsletterCampaign::query()->where('status', NewsletterCampaign::SENDING)
            ->whereDoesntHave('deliveries', fn ($q) => $q->whereIn('status', ['pending', 'sending']))
            ->update(['status' => NewsletterCampaign::SENT, 'sent_at' => now()]);

        return $done;
    }

    /** Envois restants (campagnes en cours). */
    public function pending(): int
    {
        return NewsletterDelivery::query()->where('status', 'pending')
            ->whereHas('campaign', fn ($q) => $q->where('status', NewsletterCampaign::SENDING))->count();
    }

    public static function perMinute(): int
    {
        return max(1, min(600, (int) Setting::read('newsletter_per_minute', self::DEFAULT_PER_MINUTE)));
    }

    private function announce(NewsletterSubscriber $subscriber): void
    {
        try {
            Notification::send(User::query()->where('role', User::ROLE_ADMIN)->get(), new NewSubscriber($subscriber));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
