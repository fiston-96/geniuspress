<p align="center"><img src="public/brand/geniuspress-logo-light.svg" alt="GeniusPress" width="320"></p>

# GeniusPress

**Un CMS open source avec son propre page builder visuel et un assistant IA intégré**, pensé pour les entreprises, les ONG et les institutions, partout dans le monde.

> Statut : **MVP en développement**. Pas encore prêt pour la production. Voir la [feuille de route](docs/feuille-de-route.html).

## Fonctionnalités

- **Builder visuel** : glisser-déposer, conteneurs imbriqués (flexbox), 8 widgets (titre, texte, image, bouton, espace, séparateur, vidéo, liste de liens), réglages par appareil (ordinateur, tablette, mobile) et au survol, fond, dégradé, bordure, ombre, typographie, couleurs et polices globales, arborescence de la page, copier-coller du style, annuler et rétablir, historique des versions.
- **Brouillon séparé** : enregistrer ne modifie pas le site en ligne tant que la page n'est pas publiée.
- **Assistant IA (Claude)** : création d'une page à partir d'une description, rédaction et réécriture des textes, traduction des pages, audit SEO et accessibilité.
- **Site multilingue**, médiathèque, référencement par page, rôles administrateur et éditeur.
- **Vos données vous appartiennent** : export complet du site en un fichier zip, import dans une autre installation (voir [docs/guide/sauvegarde.md](docs/guide/sauvegarde.md)).
- **Sécurité** : tout contenu est nettoyé et validé côté serveur, la clé API est chiffrée et n'est jamais envoyée au navigateur.

## Prérequis

- PHP 8.3 ou plus récent, avec les extensions `pdo_mysql`, `mbstring`, `intl`, `gd`, `fileinfo`
- MySQL 8 ou MariaDB 10.6+
- Composer 2 et Node.js 20+ (uniquement pour le développement)

## Installation

Guide complet : [docs/installation.md](docs/installation.md).

### Avec l'assistant (navigateur)

1. Placez les fichiers sur votre serveur. Le dossier public du site doit être `public/`.
2. Ouvrez l'adresse du site : l'assistant `/install` vérifie le serveur, configure la base de données (MySQL ou SQLite) et crée votre compte administrateur.

Aucune modification manuelle du fichier `.env` n'est nécessaire : il est créé et rempli par l'assistant.

### Avec Docker

```bash
docker compose up -d
```

Puis ouvrez <http://localhost:8080>. GeniusPress et MariaDB démarrent ensemble.

### Hébergement mutualisé

Téléchargez le paquet `geniuspress-x.y.z.zip` (dépendances incluses), envoyez-le chez votre hébergeur et ouvrez l'adresse du site. Pour le fabriquer : `npm run build && php artisan geniuspress:package`.

### En ligne de commande

```bash
php artisan geniuspress:install
```

Ou sans aucune question, par exemple avec SQLite :

```bash
php artisan geniuspress:install --db=sqlite --site-name="Mon ONG" --admin-name="Amani" \
    --admin-email=admin@exemple.org --admin-password="motdepasse123" --url=https://exemple.org -n
```

### Pour le développement

```bash
git clone <adresse-du-depot> geniuspress
cd geniuspress
composer install
npm install && npm run build
php artisan serve
```

Ouvrez <http://localhost:8000> : l'assistant d'installation s'occupe du reste.

Pour un compte de test rapide, définissez `GP_DEV_ADMIN_EMAIL` et `GP_DEV_ADMIN_PASSWORD` dans `.env`, puis lancez `php artisan db:seed --class=DevAdminSeeder`.

### Assistant IA

Dans **Réglages → Assistant IA**, collez une clé API Anthropic (créée sur console.anthropic.com) et présentez votre organisation. La clé peut aussi être fournie par la variable `ANTHROPIC_API_KEY`.

## Tests

```bash
php artisan test
```

Les tests de l'assistant IA utilisent un faux modèle : ils ne font aucun appel payant.

## Architecture en bref

| Dossier | Rôle |
|---|---|
| `app/Builder/` | Schéma des widgets, nettoyage du contenu, rendu HTML des pages |
| `app/Ai/` | Assistant IA et accès à l'API Claude |
| `app/Http/Controllers/` | Installation, admin, site public |
| `resources/js/builder/` | Builder React (canevas dans une iframe, panneau, contrôles) |
| `resources/css/site.css` | Styles de base du site public |

Le contenu d'une page est un arbre JSON de conteneurs imbriqués et de widgets (format décrit dans `app/Builder/Schema.php`). Le même schéma sert au builder, au rendu serveur, à l'import et à l'assistant IA.

## Marque

Le nom et le logo GeniusPress, ainsi que leurs règles d'usage, sont décrits dans [docs/marque.md](docs/marque.md). Ils ne sont pas couverts par la licence du code.

## Licence

GeniusPress est distribué sous licence **GNU AGPL-3.0** (voir `LICENSE`), avec une **exception pour les plugins** qui peuvent choisir librement leur licence (voir `LICENSE-EXCEPTION.md`).
