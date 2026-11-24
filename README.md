# GeniusPress

**Un CMS open source avec son propre page builder visuel et un assistant IA intégré**, pensé pour les entreprises et les ONG, en particulier en Afrique.

> Statut : **MVP en développement**. Pas encore prêt pour la production. Voir la [feuille de route](docs/feuille-de-route.html).

## Fonctionnalités

- **Builder visuel** : glisser-déposer, sections de 1 à 4 colonnes, 7 widgets (titre, texte, image, bouton, espace, séparateur, vidéo), aperçu ordinateur, tablette et mobile, annuler et rétablir, historique des versions.
- **Brouillon séparé** : enregistrer ne modifie pas le site en ligne tant que la page n'est pas publiée.
- **Assistant IA (Claude)** : création d'une page à partir d'une description, rédaction et réécriture des textes, traduction des pages, audit SEO et accessibilité.
- **Site multilingue**, médiathèque, référencement par page, rôles administrateur et éditeur.
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

Le contenu d'une page est un arbre JSON `sections → colonnes → widgets`. Le même schéma sert au builder, au rendu serveur et à l'assistant IA.

## Licence

GeniusPress est distribué sous licence **GNU AGPL-3.0** (voir `LICENSE`), avec une **exception pour les plugins** qui peuvent choisir librement leur licence (voir `LICENSE-EXCEPTION.md`).
