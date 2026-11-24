# Installer GeniusPress

GeniusPress s'installe en trois étapes depuis le navigateur, sans toucher à aucun fichier de configuration. Il fonctionne aussi bien chez un hébergeur mutualisé que sur un VPS ou sur un ordinateur, avec ou sans Internet.

## Ce qu'il faut

| Élément | Minimum |
|---|---|
| PHP | 8.3 ou plus récent |
| Extensions PHP | `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`, `filter`, et `pdo_mysql` **ou** `pdo_sqlite` |
| Extensions conseillées | `intl` (dates et nombres), `curl` (assistant IA), `gd` (images), `zip` (export, import, plugins) |
| Base de données | MySQL 8, MariaDB 10.6 ou plus récent, **ou** SQLite (un simple fichier, sans serveur) |
| Droits d'écriture | Le fichier `.env` (ou le dossier du site s'il n'existe pas), `storage/`, `bootstrap/cache/` |

L'assistant vérifie tout cela à la première étape et indique précisément ce qui manque.

## Installation avec l'assistant

1. **Envoyez les fichiers** sur votre serveur. Le dossier racine du site, aussi appelé « document root », doit être le dossier `public/` de GeniusPress.
2. **Ouvrez l'adresse du site** dans votre navigateur. Vous arrivez sur `/install`.
3. **Étape 1 — Serveur.** La liste des vérifications doit être au vert. « Conseillé » signifie que GeniusPress fonctionne, mais qu'une fonctionnalité sera limitée.
4. **Étape 2 — Base de données.**
   - *MySQL / MariaDB* : indiquez le serveur (souvent `127.0.0.1` ou `localhost`), le port (`3306`), le nom de la base, l'utilisateur et le mot de passe fournis par votre hébergeur. Cochez « Créer la base si elle n'existe pas » si votre utilisateur en a le droit.
   - *SQLite* : rien à remplir. Tout est enregistré dans `database/geniuspress.sqlite`. C'est idéal pour un usage local ou hors ligne.
   - L'assistant teste la connexion, explique les erreurs en clair (mot de passe refusé, base inexistante, serveur injoignable…), puis crée les tables.
5. **Étape 3 — Votre site.** Nom du site, langue du contenu, compte administrateur, et point de départ : une page d'accueil d'exemple, ou une page vide à remplir vous-même ou avec l'assistant IA.

À la fin, vous êtes connecté au tableau de bord. L'assistant d'installation est alors **définitivement fermé** : ses adresses renvoient une erreur 404.

### Ce que fait l'assistant pour vous

- Il crée le fichier `.env` et une clé de chiffrement unique.
- Il enregistre la base de données choisie. Le `.env` n'est écrit qu'une seule fois, à la toute fin.
- Il règle l'adresse du site (`APP_URL`) et le **mode** : sur une adresse publique, le mode production est activé et les messages d'erreur détaillés sont masqués aux visiteurs. Sur `localhost` ou en `.test`, c'est le mode développement.
- Il crée le lien vers les fichiers envoyés (`public/storage`).
- Il écrit un verrou, `storage/app/installed.lock`, qui ferme l'installateur.

## Installation en ligne de commande

Sur un VPS ou dans un script de déploiement :

```bash
php artisan geniuspress:install
```

La commande pose les mêmes questions que l'assistant. Tout peut aussi être donné en options, pour une installation automatique :

| Option | Rôle |
|---|---|
| `--db=mysql` ou `--db=sqlite` | Type de base |
| `--db-host`, `--db-port`, `--db-name`, `--db-user`, `--db-password` | Connexion MySQL (la base est créée si besoin) |
| `--site-name` | Nom du site |
| `--locale` | Langue du contenu : `fr`, `en`, `sw`, `ln`, `pt`, `es`, `ar`, `rw` |
| `--admin-name`, `--admin-email`, `--admin-password` | Compte administrateur |
| `--url` | Adresse publique (active le mode production si ce n'est pas une adresse locale) |
| `--blank` | Page d'accueil vide |

```bash
php artisan geniuspress:install --db=mysql --db-host=127.0.0.1 --db-name=mon_site --db-user=mon_user \
    --db-password="…" --site-name="ONG Espoir" --locale=fr --admin-name="Amani" \
    --admin-email=admin@ong-espoir.org --admin-password="…" --url=https://ong-espoir.org -n
```

## Questions fréquentes

**J'ai déjà un fichier `.env` configuré.** L'assistant reprend ses valeurs MySQL dans le formulaire. Si la base est déjà prête et contient les tables, il passe directement à l'étape 3.

**Je développe avec `php artisan serve`.** Ce serveur redémarre à chaque modification du `.env`. L'installateur en tient compte et n'écrit ce fichier qu'à la toute fin. Si la page finale s'interrompt, rechargez-la : l'installation est terminée.

**Je veux réinstaller depuis zéro.** Supprimez `storage/app/installed.lock`, videz la base de données (ou supprimez le fichier SQLite), puis ouvrez `/install`.

**Une installation d'avant l'existence du verrou.** Elle est reconnue automatiquement (la base contient déjà un compte) et le verrou est créé.

**Sécurité.** Tant que l'installation n'est pas terminée, toute personne qui connaît l'adresse du site peut ouvrir `/install`. Terminez l'installation juste après avoir envoyé les fichiers, ou installez en ligne de commande avant de rendre le site public.
