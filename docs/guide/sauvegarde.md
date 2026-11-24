# Sauvegarder, exporter et importer un site

Vos données vous appartiennent. GeniusPress peut à tout moment rassembler tout votre site dans **un seul fichier zip**, pour le sauvegarder ou pour le déplacer vers une autre installation : un autre hébergeur, un VPS, un ordinateur hors ligne…

## Ce que contient l'export

| Inclus | Non inclus |
|---|---|
| Toutes les pages, dans toutes les langues, avec leurs liens de traduction | Les comptes utilisateurs et les mots de passe |
| Les brouillons non publiés | La clé API de l'assistant IA |
| Les réglages de référencement (titre et description) | L'historique des versions |
| Les médias (images, PDF) et leurs textes alternatifs | Les plugins (à réinstaller sur le nouveau site) |
| Les réglages du site : nom, slogan, couleurs, police, langues, présentation de l'organisation | |

## Depuis l'administration

**Menu Sauvegarde** (administrateurs uniquement) :

- **Exporter le site** : un fichier `nom-du-site-date.zip` est téléchargé.
- **Importer un site**, avec deux modes :
  - *Ajouter au site actuel* : les pages importées s'ajoutent. Une adresse déjà utilisée reçoit un suffixe (`accueil-2`), et votre page d'accueil reste la même.
  - *Remplacer les pages de même adresse* : pour **restaurer une sauvegarde**. Les pages existantes sont écrasées et la page d'accueil importée est reprise.
  - Case *Importer aussi les réglages* : nom, couleurs, police et langues du site exporté.

La taille maximale d'un fichier envoyé dépend de votre hébergeur (elle est indiquée sur la page). Pour un gros site, utilisez la ligne de commande.

## Déménager un site vers une nouvelle installation

À la dernière étape de l'installation, choisissez le point de départ **« Importer un site »** et donnez le fichier zip. Le nouveau site reprend toutes les pages, les médias et les réglages, sauf le nom que vous venez de saisir.

En ligne de commande :

```bash
php artisan geniuspress:install --import=mon-site.zip …
```

## En ligne de commande

```bash
php artisan geniuspress:export                      # dans storage/app/private/exports/
php artisan geniuspress:export /sauvegardes/site.zip
php artisan geniuspress:import site.zip             # ajouter au site actuel
php artisan geniuspress:import site.zip --replace   # restaurer une sauvegarde
php artisan geniuspress:import site.zip --no-settings
```

Pour une sauvegarde automatique chaque nuit, ajoutez la commande d'export à la planification de votre serveur (cron).

## Sécurité

L'import ne fait jamais confiance au contenu de l'archive :
- seuls les fichiers attendus sont lus, sans extraction brute ni chemin du type `../` ;
- les médias sont contrôlés comme un envoi normal : types autorisés, taille, SVG contenant du code refusés ;
- le contenu des pages est nettoyé comme dans le builder : pas de scripts, pas de liens `javascript:` ;
- les réglages sont validés un par un, et une clé API présente dans l'archive est ignorée ;
- les liens vers les images sont réécrits vers les nouveaux fichiers.

Un problème non bloquant (média refusé, langue inconnue…) est signalé dans **Points à vérifier** après l'import.
