# Essai hébergé — conception

Une personne qui a un compte sur la plateforme GeniusPress clique sur « Essayer GeniusPress » et obtient, en quelques secondes, un site GeniusPress complet hébergé chez nous : builder et ses 64 widgets, modèles, blog, thème, IA (dans la limite de crédits d'essai). Rien à installer. À la fin de l'essai, elle passe à une offre payante (même site, mêmes données) ou ses données sont supprimées.

Code : `app/Hosting/`. Réglages : `config/hosting.php`. Comptes, offres et paiement : session « Plateforme commerciale » (`app/Platform/`), branchée par l'interface `App\Hosting\HostingAccounts`.

## 1. Isoler chaque site d'essai

| | Un code partagé, une base + un dossier par site | Une installation complète par site | Une base commune (colonne `site_id`) |
|---|---|---|---|
| Isolation des données | Forte : base MySQL et utilisateur MySQL dédiés, dossier de fichiers dédié | Forte | Faible : un oubli de filtre dans une requête montre les données d'un autre site |
| Disque par site | Base (~2 Mo) + médias | + ~150 Mo de code (`vendor`, `public/build`) | Médias seulement |
| Mise à jour de GeniusPress | Un déploiement, puis `geniuspress:trial migrate` | Chaque copie à mettre à jour | Un déploiement |
| Mémoire PHP (OPcache) | Un seul jeu de fichiers en cache | Un jeu par site : sature vite 8 Go | Un seul |
| Modifications du cœur | Aucune (le site se croit une installation normale) | Aucune | Tous les modèles, toutes les requêtes |
| Création d'un site | 2 à 5 s (base + migrations) | Copie de fichiers, plus lente | Instantanée |

**Choix : un code partagé, une base MySQL et un dossier par site.** C'est le seul qui isole vraiment les données sans multiplier le code ni toucher au cœur.

Fonctionnement (`App\Hosting\HostedBootstrap`, appelé dans `bootstrap/app.php` avant le chargement du `.env`) :

1. Le nom d'hôte de la requête (`cooperative.essai.exemple.org`) est cherché dans l'index `storage/hosting/hosts/` ; en ligne de commande, c'est la variable `GP_SITE=cooperative`.
2. Si un site correspond, Laravel utilise son dossier : son `.env` (clé de chiffrement, base, adresse), son `storage/` (médias, sessions, journaux, verrou d'installation) et son propre cache de configuration.
3. Sinon, c'est la plateforme elle-même.

```
storage/hosting/
  hosts/cooperative.essai.exemple.org      → « cooperative »
  sites/cooperative/
    .env            clé, base et utilisateur MySQL gpe_12_a3f9, adresse, clé IA de la plateforme
    hosting.json    état de l'essai, date de fin, quotas, propriétaire, secret de connexion
    storage/        app/public (médias), framework (sessions, vues), logs
```

Chaque site a sa base `gpe_{n}_{aléa}` et son utilisateur MySQL du même nom (mot de passe aléatoire, droits sur cette seule base). La base ne doit pas déjà exister : un site ne reprend jamais la base d'un autre. L'utilisateur MySQL de la plateforme qui provisionne (`GP_HOSTING_DB_USERNAME`) doit pouvoir créer des bases et des utilisateurs.

Ce que l'essai ne peut pas faire : il n'a pas accès aux fichiers du serveur ; les plugins sont ceux installés par l'exploitant (activables, pas téléversables) ; l'import de site ne lit que les fichiers attendus (voir `SiteImporter`).

## 2. Adresse de chaque essai

Chaque essai a **son propre nom d'hôte** : `{essai}.{domaine des essais}`. Une adresse en sous-dossier (`/essai/cooperative/…`) a été écartée : les contenus des modèles utilisent des liens à la racine (`/contact`, `/storage/…`), et tous les essais partageraient la même origine (cookies, scripts) entre eux et avec la plateforme.

- **En local** : `GP_HOSTING_DOMAIN=localhost`, `GP_HOSTING_SCHEME=http`, `GP_HOSTING_PORT=8000` → `http://cooperative.localhost:8000` (les navigateurs résolvent `*.localhost` sans configuration).
- **Avec un nom de domaine** (cible) : un enregistrement DNS générique `*.essai.{domaine}` vers le VPS, un certificat générique `*.essai.{domaine}` (Let's Encrypt, défi DNS-01), un bloc Nginx `server_name *.essai.{domaine}`. Les essais devenus payants pourront rattacher leur propre domaine (index `hosts/`).
- **En attendant, avec `vps127199.serveur-vps.net`** : on ne peut pas créer de sous-domaines de `serveur-vps.net` (domaine de LWS). Deux solutions :
  1. un DNS générique public gratuit comme `sslip.io` : `cooperative.{ip-du-vps}.sslip.io` arrive sur le VPS sans rien configurer ; `GP_HOSTING_DOMAIN={ip-avec-tirets}.sslip.io`. Certificat Let's Encrypt par essai (défi HTTP-01, demandé à la création). Pratique pour tester, mais on dépend d'un service tiers et des limites de Let's Encrypt : à réserver aux essais internes ;
  2. acheter dès maintenant le domaine de la plateforme (nous comptons de toute façon vendre des noms de domaine).

Recommandation : les essais sur un domaine **distinct** de celui de la plateforme où se trouvent les comptes et les paiements (comme `github.io` pour `github.com`), ou au minimum sur un sous-domaine dédié (`*.essai.{domaine}`), pour qu'un site d'essai ne puisse jamais poser de cookie sur la plateforme.

Nginx, pour un domaine (à faire par l'exploitant, pas par cette branche) :

```nginx
map $host $gp_site { default ""; ~^(?<s>[a-z0-9-]+)\.essai\.exemple\.org$ $s; }
server {
    listen 443 ssl; server_name *.essai.exemple.org;
    ssl_certificate /etc/letsencrypt/live/essai.exemple.org/fullchain.pem;  # certificat générique
    root /var/www/geniuspress/public;
    # Médias de chaque essai, servis sans PHP.
    location /storage/ { alias /var/www/geniuspress/storage/hosting/sites/$gp_site/storage/app/public/; }
    location / { try_files $uri /index.php?$query_string; }
    location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name; fastcgi_pass unix:/run/php/php8.3-fpm.sock; }
}
```

Sans cette règle `/storage/`, Laravel sert lui-même les médias d'un essai (route de secours, plus lente).

## 3. Durée et limites

Valeurs par défaut (`config/hosting.php`, modifiables par l'offre d'essai que renvoie `HostingAccounts::trialPlan()`) :

| Limite | Valeur | Contrôle |
|---|---|---|
| Durée | 14 jours, prolongeable par l'exploitant | `hosting.json` → `expires_at` |
| Essais par compte | 1, même après suppression (l'exploitant peut en créer d'autres en ligne de commande) | `Trials::refusal()` |
| Pages | 40 | création d'une page refusée au-delà (modèles et import compris) |
| Articles | 40 | idem |
| Médias | 150 Mo au total (10 Mo par fichier, comme partout) | envoi refusé au-delà |
| IA | 40 demandes et 400 000 jetons (entrée + sortie) | `MeteredLlmGateway`, avant chaque appel |
| Essais actifs sur le serveur | 100 | « revenez plus tard » au-delà |

Les crédits IA sont comptés en jetons réels (table `ai_requests` de l'essai) ; leur conversion en « crédits » facturés (deux fois le coût réel) appartient à la plateforme commerciale. La clé API de l'IA est celle de la plateforme (`GP_HOSTING_AI_KEY`), copiée dans le `.env` de l'essai, jamais envoyée au navigateur.

Charge du VPS (4 cœurs, 8 Go) : un essai inactif ne coûte que du disque ; la mémoire et le processeur ne servent qu'aux requêtes, partagées par le même pool PHP-FPM. Le vrai plafond est le disque : 100 essais × 150 Mo = 15 Go au pire.

## 4. Cycle de vie

```
préparation ──▶ actif ──(date de fin)──▶ terminé ──(30 jours)──▶ supprimé
                  │  ▲                      │
          suspendre│  │reprendre            └──(paiement)──▶ actif (offre payante, sans date de fin)
                  ▼  │
               suspendu
```

- **Préparation** : la plateforme enregistre l'essai, puis une tâche crée le dossier, la base, le `.env`, lance `geniuspress:hosted-setup` dans le contexte du site (migrations, compte administrateur, page d'accueil, verrou d'installation). Sans file d'attente active, la tâche s'exécute juste après la réponse (`dispatchAfterResponse`). En cas d'échec : état « échec », tout ce qui a été créé est retiré.
- **Actif** : bandeau « Essai : N jours restants » dans l'administration de l'essai.
- **Terminé** : le site public affiche « Ce site d'essai est terminé » ; l'administration reste ouverte **en lecture seule** pendant 30 jours (consultation, export du site dans Sauvegarde), le temps de passer à une offre payante.
- **Suspendu** (abus, décision de l'exploitant) : tout est fermé.
- **Supprimé** : base et utilisateur MySQL supprimés, dossier effacé, adresse libérée. Il ne reste qu'une ligne d'historique (compte, dates) sans contenu.

Connexion en un clic : le bouton « Ouvrir mon site » de la plateforme envoie vers un lien signé (HMAC avec le secret du site, valable 10 minutes, utilisable une fois). Le compte administrateur de l'essai reprend le nom, l'e-mail et le mot de passe (déjà chiffré) du compte plateforme : on peut aussi se connecter directement sur `/admin/login` de l'essai.

## 5. Passer à une offre payante

La plateforme commerciale, une fois le paiement reçu, appelle `SiteProvisioner::convert($site, $offre)` : même site, même base, mêmes médias ; l'essai devient un site payant (`kind = paid`), sans date de fin (ou jusqu'à la fin de l'abonnement), avec les quotas de l'offre. Le nom de domaine acheté s'ajoute ensuite à l'index des hôtes. Rien n'est copié ni réinstallé.

## 6. Exploitation

```bash
php artisan geniuspress:trial list
php artisan geniuspress:trial create --email=amani@exemple.org --name="Coopérative" --site=cooperative
php artisan geniuspress:trial extend cooperative --days=7
php artisan geniuspress:trial suspend cooperative --reason="Contenu frauduleux"
php artisan geniuspress:trial resume cooperative
php artisan geniuspress:trial delete cooperative --force
php artisan geniuspress:trial expire     # planifié toutes les heures
php artisan geniuspress:trial migrate    # après chaque mise à jour du code
GP_SITE=cooperative php artisan …        # n'importe quelle commande dans le contexte d'un essai
```

Le planificateur Laravel doit tourner (`* * * * * php artisan schedule:run` dans la crontab de l'utilisateur du site).

Variables (`.env` de la plateforme) : `GP_HOSTING_ENABLED`, `GP_HOSTING_DOMAIN`, `GP_HOSTING_SCHEME`, `GP_HOSTING_PORT`, `GP_HOSTING_DB` (`mysql` ou `sqlite`), `GP_HOSTING_DB_HOST`, `GP_HOSTING_DB_PORT`, `GP_HOSTING_DB_USERNAME`, `GP_HOSTING_DB_PASSWORD`, `GP_HOSTING_AI_KEY`, `GP_HOSTING_MAX_SITES`. Le dossier des essais (`storage/hosting` par défaut) se change avec la variable d'environnement du serveur `GP_HOSTING_PATH` (lue avant le `.env`, donc à déclarer dans PHP-FPM et la crontab, pas dans `.env`). Ne jamais définir `DB_*` dans l'environnement du serveur : ces valeurs l'emporteraient sur le `.env` de chaque essai.

Événements pour la plateforme : `SiteProvisioned`, `TrialExpired`, `SiteDeleted` (`App\Hosting\Events`) — par exemple pour envoyer un e-mail.

## 7. Interface avec la plateforme commerciale

```php
interface HostingAccounts
{
    public function accountId(Authenticatable $user): string;          // compte plateforme
    public function trialRefusal(Authenticatable $user): ?string;      // null = droit à un essai
    public function trialPlan(Authenticatable $user): Plan;            // durée et quotas
    public function owner(string $accountId): ?SiteOwner;              // nom, e-mail, mot de passe chiffré
    public function upgradeUrl(Site $site): ?string;                   // page « choisir une offre »
}
```

Implémentation provisoire : `LocalHostingAccounts` (tout compte connecté de ce site a droit à un essai, offre d'essai de `config/hosting.php`, pas de page d'offres). La plateforme la remplace dans son fournisseur de services : `$this->app->bind(HostingAccounts::class, PlatformHostingAccounts::class)`. Les routes de l'essai utilisent le middleware `config('hosting.middleware')` (`web` + `auth` par défaut), à adapter au garde des comptes plateforme.

## 8. Questions ouvertes (hébergement réel)

1. Nom de domaine des essais : `sslip.io` en attendant, ou achat immédiat d'un domaine ? Domaine distinct de celui de la plateforme ?
2. Certificat générique (défi DNS-01 : il faut un accès à l'API DNS du registraire) ou un certificat par essai (défi HTTP-01, limité à 50 par semaine et par domaine chez Let's Encrypt) ?
3. Taille du disque du VPS, pour fixer le nombre d'essais simultanés et le quota de médias.
4. Un utilisateur MySQL dédié au provisionnement (droits `CREATE`, `DROP`, `CREATE USER`, `GRANT OPTION`) sur le VPS.
5. Crontab du planificateur et, si on le souhaite, un `php artisan queue:work` en service pour créer les essais hors de la requête.
