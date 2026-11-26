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
- **En attendant (choix retenu pour tester)** : on ne peut pas créer de sous-domaines de `serveur-vps.net` (domaine de LWS). Les essais utilisent `sslip.io`, un DNS public gratuit : `cooperative.78-24-248-103.sslip.io` arrive sur le VPS (78.24.248.103) sans rien configurer. Pas de certificat générique possible (le DNS n'est pas à nous) : un certificat Let's Encrypt par essai (défi HTTP-01), obtenu dans la minute par `scripts/hosting/certificats-essais.sh` ; la plateforme ne propose le site qu'une fois son certificat prêt (`GP_HOSTING_CERTIFICATES=per-site`). Voir « Mise en place de test sur le VPS » (§ 8).
  - Limites (vérifiées en novembre 2026) : `sslip.io` n'est pas dans la Public Suffix List, donc Let's Encrypt compte ensemble tous les certificats `*.sslip.io` de tous ses utilisateurs. Leur plafond hebdomadaire a été relevé (3 000 en 2021, puis 200 000 en mars 2026 après avoir été épuisé en février 2026 — ticket cunnie/sslip.io#108). Si le plafond est atteint, la création du certificat échoue et le site reste « en sécurisation » : acceptable pour tester, pas pour la production. On dépend aussi de la disponibilité du DNS de sslip.io.
  - Pour la production : acheter le domaine des essais (nous comptons de toute façon vendre des noms de domaine) et passer à un certificat générique.

Recommandation : les essais sur un domaine **distinct** de celui de la plateforme où se trouvent les comptes et les paiements (comme `github.io` pour `github.com`), ou au minimum sur un sous-domaine dédié (`*.essai.{domaine}`), pour qu'un site d'essai ne puisse jamais poser de cookie sur la plateforme.

Nginx : [`scripts/hosting/nginx-essais.conf`](../../scripts/hosting/nginx-essais.conf) (à installer par l'exploitant) — adresses d'essai, certificat choisi d'après l'adresse, médias de chaque essai servis sans PHP depuis son dossier. Avec un vrai domaine et un certificat générique, seuls le domaine et le chemin du certificat changent. Sans la règle `/storage/`, Laravel sert lui-même les médias d'un essai (plus lent).

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

Variables (`.env` de la plateforme) : `GP_HOSTING_ENABLED`, `GP_HOSTING_DOMAIN`, `GP_HOSTING_SCHEME`, `GP_HOSTING_PORT`, `GP_HOSTING_CERTIFICATES` (`wildcard` ou `per-site`), `GP_HOSTING_DB` (`mysql` ou `sqlite`), `GP_HOSTING_DB_HOST`, `GP_HOSTING_DB_PORT`, `GP_HOSTING_DB_USERNAME`, `GP_HOSTING_DB_PASSWORD`, `GP_HOSTING_AI_KEY`, `GP_HOSTING_MAX_SITES`. Le dossier des essais (`storage/hosting` par défaut) se change avec la variable d'environnement du serveur `GP_HOSTING_PATH` (lue avant le `.env`, donc à déclarer dans PHP-FPM et la crontab, pas dans `.env`). Ne jamais définir `DB_*` dans l'environnement du serveur : ces valeurs l'emporteraient sur le `.env` de chaque essai.

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

## 8. Mise en place de test sur le VPS (sslip.io)

À faire par l'exploitant, en root, dans `/var/www/geniuspress` après déploiement de cette branche. Rien de tout cela n'a été fait sur le serveur.

1. **Utilisateur MySQL de provisionnement**, limité aux bases `gpe_…` :
   ```sql
   CREATE USER 'gp_provision'@'localhost' IDENTIFIED BY 'mot-de-passe-long-et-aléatoire';
   GRANT ALL PRIVILEGES ON `gpe\_%`.* TO 'gp_provision'@'localhost' WITH GRANT OPTION;
   GRANT CREATE USER ON *.* TO 'gp_provision'@'localhost';
   ```
2. **`.env` de la plateforme** :
   ```dotenv
   GP_HOSTING_ENABLED=true
   GP_HOSTING_DOMAIN=78-24-248-103.sslip.io
   GP_HOSTING_SCHEME=https
   GP_HOSTING_CERTIFICATES=per-site
   GP_HOSTING_DB=mysql
   GP_HOSTING_DB_HOST=localhost        # socket : correspond aux utilisateurs 'gpe_…'@'localhost'
   GP_HOSTING_DB_USERNAME=gp_provision
   GP_HOSTING_DB_PASSWORD=…
   GP_HOSTING_AI_KEY=…                 # clé Anthropic de la plateforme pour l'IA des essais
   GP_HOSTING_MAX_SITES=20             # prudent pour la phase de test
   ```
   puis `php artisan migrate --force` et `php artisan config:clear`.
3. **Nginx** : installer `scripts/hosting/nginx-essais.conf` (instructions en tête du fichier), `nginx -t`, recharger.
4. **Certificats** : `apt install certbot`, `chmod +x scripts/hosting/certificats-essais.sh`, puis dans la crontab de root :
   `* * * * * GP_CERT_EMAIL=… /var/www/geniuspress/scripts/hosting/certificats-essais.sh`
5. **Planificateur** (expiration des essais), dans la crontab de `www-data` :
   `* * * * * cd /var/www/geniuspress && php artisan schedule:run >> /dev/null 2>&1`
6. **Essai** : se connecter à la plateforme, ouvrir `/essayer`, créer un site ; la page attend le certificat (environ une minute) puis propose « Ouvrir mon site ». En ligne de commande : `sudo -u www-data php artisan geniuspress:trial list`.

Les commandes `geniuspress:trial` doivent être lancées avec l'utilisateur du site (`www-data`), pour que les dossiers créés lui appartiennent.

## 9. Questions ouvertes (production)

1. Achat du domaine des essais, distinct de celui de la plateforme, et certificat générique (défi DNS-01 : il faut un accès à l'API DNS du registraire).
2. Taille du disque du VPS, pour fixer le nombre d'essais simultanés et le quota de médias.
3. Un `php artisan queue:work` en service (`GP_HOSTING_QUEUE=database`) pour créer les essais hors de la requête web.

## Sites gratuits sans limite et MySQL 8 (geniuspress.site, novembre 2026)

- `GP_HOSTING_UNLIMITED=true` : chaque client crée autant de sites qu'il veut (`GP_HOSTING_SITES_PER_ACCOUNT`, 0 = sans limite), type « free », sans date de fin ni quota de pages, d'articles ou de médias ; seuls les quotas IA de l'essai restent. Page « Mes sites » dans l'espace client.
- Adresses : `{site}.geniuspress.site` (enregistrement DNS `A *`), un certificat Let's Encrypt par site (`GP_HOSTING_CERTIFICATES=per-site`, script `certificats-essais.sh` lancé par root chaque minute avec `GP_HOSTING_DOMAIN=geniuspress.site GP_WEB_USER=geniuspress`).
- **MySQL 8** refuse le `GRANT` au compte de provisionnement quand ses droits viennent d'un motif (`gpe\_%`). Plutôt que de lui donner des droits globaux, root crée une procédure qui vérifie les noms puis donne les droits, et le compte n'a que le droit de l'appeler (`GP_HOSTING_DB_GRANT_PROCEDURE=gp_hosting.grant_site`) :

```sql
CREATE DATABASE IF NOT EXISTS gp_hosting;
DELIMITER //
CREATE PROCEDURE gp_hosting.grant_site(IN db VARCHAR(64), IN usr VARCHAR(32), IN hst VARCHAR(64))
SQL SECURITY DEFINER
BEGIN
    IF db NOT REGEXP '^gpe_[0-9]+_[a-f0-9]{4}$' OR usr <> db OR hst NOT IN ('localhost', '127.0.0.1') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nom de base ou d''utilisateur refusé';
    END IF;
    SET @gp_sql = CONCAT('GRANT ALL PRIVILEGES ON `', db, '`.* TO ''', usr, '''@''', hst, '''');
    PREPARE gp_st FROM @gp_sql; EXECUTE gp_st; DEALLOCATE PREPARE gp_st;
END//
DELIMITER ;
GRANT EXECUTE ON PROCEDURE gp_hosting.grant_site TO 'geniuspress_hosting'@'localhost';
```

**Certificats par site et Nginx** : avec `ssl_certificate /…/$gp_cert/…`, Nginx lit le certificat à chaque connexion avec ses processus non privilégiés, qui ne peuvent pas lire `/etc/letsencrypt` (root seulement). Le script recopie donc chaque certificat dans `GP_CERT_DIR` (ex. `/etc/nginx/geniuspress-certs/{site}/`, groupe `GP_NGINX_GROUP`, clé privée en 0640) et Nginx lit ce dossier. Renouvellements : crochet `/etc/letsencrypt/renewal-hooks/deploy/geniuspress-sites.sh` qui appelle `certificats-essais.sh --deploy-hook` avec les mêmes variables.

**Garde-fou** : `HostedBootstrap::apply()` refuse de servir un site hébergé si `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` ou `DB_HOST` sont définis dans l'environnement du serveur (`env[…]` d'un pool PHP-FPM, crontab) : sinon le site lirait la base de la plateforme (incident corrigé sur geniuspress.site le 26 novembre 2026, variables retirées du pool `geniuspress`).
