<?php

namespace App\Platform\Suppliers\Lws;

use App\Platform\Models\Plan;
use App\Platform\Suppliers\SupplierPricing;

/**
 * Offres partenaires conseillées, sous le nom GeniusPress (LWS n'apparaît jamais chez le client),
 * d'après le catalogue public LWS (tarifs normaux hors promotion, euros HT, novembre 2026) :
 * hébergement web (Perso, Starter, Performance), mail pro (Mail S, M, L) et stockage en
 * ligne (Cloud Drive S, M, L, XL). Importées depuis la console (Fournisseurs).
 *
 * « cost » : coût mensuel en centimes d'euro ; une offre annuelle coûte douze mois.
 */
final class LwsOffers
{
    public const OFFERS = [
        // ─── Hébergement web ─────────────────────────────────────────────
        [
            'slug' => 'web-essentiel', 'kind' => 'web_hosting', 'provider_ref' => 'LWS Perso', 'cost' => 399, 'period' => 'year', 'needs_domain' => true, 'sort' => 10,
            'name' => ['fr' => 'Web Essentiel', 'en' => 'Web Essential'],
            'description' => ['fr' => 'Pour mettre en ligne le site d’une petite organisation, sur votre propre nom de domaine.', 'en' => 'To put a small organization’s website online, on your own domain name.'],
            'features' => [
                'fr' => ['3 sites hébergés', '100 Go d’espace disque SSD', '5 adresses e-mail professionnelles', 'Certificat HTTPS gratuit', 'Sauvegardes automatiques chaque jour', 'Trafic illimité', 'WordPress, Joomla et autres en un clic'],
                'en' => ['3 hosted websites', '100 GB SSD disk space', '5 professional email addresses', 'Free HTTPS certificate', 'Automatic daily backups', 'Unlimited traffic', 'WordPress, Joomla and more in one click'],
            ],
        ],
        [
            'slug' => 'web-pro', 'kind' => 'web_hosting', 'provider_ref' => 'LWS Starter', 'cost' => 599, 'period' => 'year', 'needs_domain' => true, 'sort' => 20,
            'name' => ['fr' => 'Web Pro', 'en' => 'Web Pro'],
            'description' => ['fr' => 'Pour les PME, les écoles et les blogs actifs : deux fois plus de puissance.', 'en' => 'For small businesses, schools and active blogs: twice the power.'],
            'features' => [
                'fr' => ['10 sites hébergés', '200 Go d’espace disque SSD', '25 adresses e-mail professionnelles', 'Performances doublées', 'Certificat HTTPS gratuit', 'Sauvegardes automatiques chaque jour', 'Trafic illimité', 'WordPress, PrestaShop et autres en un clic'],
                'en' => ['10 hosted websites', '200 GB SSD disk space', '25 professional email addresses', 'Twice the performance', 'Free HTTPS certificate', 'Automatic daily backups', 'Unlimited traffic', 'WordPress, PrestaShop and more in one click'],
            ],
        ],
        [
            'slug' => 'web-business', 'kind' => 'web_hosting', 'provider_ref' => 'LWS Performance', 'cost' => 1199, 'period' => 'year', 'needs_domain' => true, 'sort' => 30,
            'name' => ['fr' => 'Web Business', 'en' => 'Web Business'],
            'description' => ['fr' => 'Pour les entreprises, les ONG et les agences qui gèrent de nombreux sites.', 'en' => 'For businesses, NGOs and agencies running many websites.'],
            'features' => [
                'fr' => ['50 sites hébergés', '300 Go d’espace disque SSD', '100 adresses e-mail professionnelles', 'Performances triplées', 'Assistance prioritaire', 'Certificat HTTPS gratuit', 'Sauvegardes automatiques chaque jour', 'Trafic illimité'],
                'en' => ['50 hosted websites', '300 GB SSD disk space', '100 professional email addresses', 'Three times the performance', 'Priority support', 'Free HTTPS certificate', 'Automatic daily backups', 'Unlimited traffic'],
            ],
        ],
        // ─── Mail pro (prix par boîte) ───────────────────────────────────
        [
            'slug' => 'mail-10', 'kind' => 'mail', 'provider_ref' => 'Mail S', 'cost' => 99, 'period' => 'month', 'needs_domain' => true, 'sort' => 40,
            'name' => ['fr' => 'Mail pro 10 Go', 'en' => 'Business email 10 GB'],
            'description' => ['fr' => 'Une adresse à votre nom de domaine (contact@votre-organisation.org).', 'en' => 'An address on your domain name (contact@your-organization.org).'],
            'features' => self::MAIL_FEATURES_10,
        ],
        [
            'slug' => 'mail-20', 'kind' => 'mail', 'provider_ref' => 'Mail M', 'cost' => 199, 'period' => 'month', 'needs_domain' => true, 'sort' => 50,
            'name' => ['fr' => 'Mail pro 20 Go', 'en' => 'Business email 20 GB'],
            'description' => ['fr' => 'Deux fois plus d’espace pour les boîtes très utilisées.', 'en' => 'Twice the space for busy mailboxes.'],
            'features' => self::MAIL_FEATURES_20,
        ],
        [
            'slug' => 'mail-50', 'kind' => 'mail', 'provider_ref' => 'Mail L', 'cost' => 299, 'period' => 'month', 'needs_domain' => true, 'sort' => 60,
            'name' => ['fr' => 'Mail pro 50 Go', 'en' => 'Business email 50 GB'],
            'description' => ['fr' => 'Le choix des équipes qui gardent tous leurs échanges.', 'en' => 'For teams that keep all their correspondence.'],
            'features' => self::MAIL_FEATURES_50,
        ],
        // ─── Stockage en ligne ───────────────────────────────────────────
        [
            'slug' => 'cloud-250', 'kind' => 'storage', 'provider_ref' => 'Cloud Drive S', 'cost' => 799, 'period' => 'month', 'needs_domain' => false, 'sort' => 70,
            'name' => ['fr' => 'Cloud 250 Go', 'en' => 'Cloud 250 GB'],
            'description' => ['fr' => 'Vos documents, photos et sauvegardes, accessibles partout.', 'en' => 'Your documents, photos and backups, available everywhere.'],
            'features' => ['fr' => ['250 Go d’espace', '5 utilisateurs', ...self::CLOUD_FR], 'en' => ['250 GB of space', '5 users', ...self::CLOUD_EN]],
        ],
        [
            'slug' => 'cloud-500', 'kind' => 'storage', 'provider_ref' => 'Cloud Drive M', 'cost' => 1499, 'period' => 'month', 'needs_domain' => false, 'sort' => 80,
            'name' => ['fr' => 'Cloud 500 Go', 'en' => 'Cloud 500 GB'],
            'description' => ['fr' => 'Pour une équipe qui partage ses dossiers au quotidien.', 'en' => 'For a team sharing its folders every day.'],
            'features' => ['fr' => ['500 Go d’espace', '10 utilisateurs', ...self::CLOUD_FR], 'en' => ['500 GB of space', '10 users', ...self::CLOUD_EN]],
        ],
        [
            'slug' => 'cloud-1000', 'kind' => 'storage', 'provider_ref' => 'Cloud Drive L', 'cost' => 1999, 'period' => 'month', 'needs_domain' => false, 'sort' => 90,
            'name' => ['fr' => 'Cloud 1 To', 'en' => 'Cloud 1 TB'],
            'description' => ['fr' => 'Pour les organisations qui archivent photos, vidéos et documents.', 'en' => 'For organizations archiving photos, videos and documents.'],
            'features' => ['fr' => ['1 000 Go d’espace', '20 utilisateurs', ...self::CLOUD_FR], 'en' => ['1,000 GB of space', '20 users', ...self::CLOUD_EN]],
        ],
        [
            'slug' => 'cloud-2000', 'kind' => 'storage', 'provider_ref' => 'Cloud Drive XL', 'cost' => 2999, 'period' => 'month', 'needs_domain' => false, 'sort' => 100,
            'name' => ['fr' => 'Cloud 2 To', 'en' => 'Cloud 2 TB'],
            'description' => ['fr' => 'Le grand espace partagé des équipes nombreuses.', 'en' => 'Large shared space for big teams.'],
            'features' => ['fr' => ['2 000 Go d’espace', '30 utilisateurs', ...self::CLOUD_FR], 'en' => ['2,000 GB of space', '30 users', ...self::CLOUD_EN]],
        ],
    ];

    private const MAIL_FR = ['Pièces jointes jusqu’à 150 Mo', 'Webmail, Outlook, Gmail et téléphone', 'Calendrier et contacts synchronisés', 'Anti-spam et antivirus', 'Sauvegardes chaque jour'];

    private const MAIL_EN = ['Attachments up to 150 MB', 'Webmail, Outlook, Gmail and phone', 'Synced calendar and contacts', 'Anti-spam and antivirus', 'Daily backups'];

    private const MAIL_FEATURES_10 = ['fr' => ['10 Go par boîte', ...self::MAIL_FR], 'en' => ['10 GB per mailbox', ...self::MAIL_EN]];

    private const MAIL_FEATURES_20 = ['fr' => ['20 Go par boîte', ...self::MAIL_FR], 'en' => ['20 GB per mailbox', ...self::MAIL_EN]];

    private const MAIL_FEATURES_50 = ['fr' => ['50 Go par boîte', ...self::MAIL_FR], 'en' => ['50 GB per mailbox', ...self::MAIL_EN]];

    private const CLOUD_FR = ['Synchronisation ordinateur et téléphone', 'Partage privé ou public sécurisé', 'Bureautique en ligne (textes, tableurs, présentations)', 'Données répliquées deux fois', 'Transferts illimités', 'Serveurs en France, conformes au RGPD'];

    private const CLOUD_EN = ['Computer and phone sync', 'Secure private or public sharing', 'Online office suite (documents, spreadsheets, slides)', 'Data replicated twice', 'Unlimited transfers', 'Servers in France, GDPR compliant'];

    /**
     * Crée les offres absentes (prix conseillé : coût + marge, dans la devise des offres si son taux
     * est réglé, sinon en euros) et met à jour coût, description et points forts des offres existantes.
     * Les prix déjà réglés ne changent que si $reprice.
     *
     * @return array{created: int, updated: int}
     */
    public static function import(SupplierPricing $pricing, bool $reprice = false): array
    {
        $done = ['created' => 0, 'updated' => 0];
        $currency = (string) config('geniuspress.platform.currency', 'USD');
        if ($pricing->rate($currency) === null) {
            $currency = 'EUR';
        }
        foreach (self::OFFERS as $offer) {
            $cost = $offer['cost'] * ($offer['period'] === 'year' ? 12 : 1);
            $plan = Plan::query()->where('slug', $offer['slug'])->first();
            $values = [
                'kind' => $offer['kind'], 'provider' => 'lws', 'provider_ref' => $offer['provider_ref'],
                'cost' => $cost, 'cost_currency' => 'EUR', 'description' => $offer['description'],
                'features' => $offer['features'], 'needs_domain' => $offer['needs_domain'],
            ];
            if (! $plan) {
                Plan::create($values + [
                    'slug' => $offer['slug'], 'name' => $offer['name'], 'period' => $offer['period'], 'sort' => $offer['sort'],
                    'price' => $pricing->suggested($cost, 'EUR', $currency), 'currency' => $currency, 'active' => true,
                ]);
                $done['created']++;

                continue;
            }
            if ($reprice && ($price = $pricing->suggested($cost, 'EUR', $plan->currency)) !== null) {
                $values['price'] = $price;
            }
            $plan->update($values);
            $done['updated']++;
        }

        return $done;
    }
}
