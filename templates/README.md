# Modèles de sites

Le catalogue compte **60 modèles** : 10 secteurs × 6 thèmes, 8 pages chacun, en français et en
anglais. Chaque modèle s'installe comme une sauvegarde de site et se prévisualise sans installation.

| Secteur | Pack | Pages |
|---|---|---|
| ONG et associations | `ong` | accueil, à propos, programmes, projets, s'impliquer, faire un don, actualités, contact |
| Églises et communautés | `eglise` | accueil, notre église, cultes, ministères, événements, enseignements, dons, contact |
| Écoles et universités | `ecole` | accueil, à propos, formations, inscriptions, vie scolaire, équipe, actualités, contact |
| Gouvernement et institutions | `gouvernement` | accueil, la commune, services, démarches, projets, organisation, actualités, contact |
| Hôpitaux et cliniques | `hopital` | accueil, à propos, services, médecins, patients, rendez-vous, santé et prévention, contact |
| Entreprises tech | `tech` | accueil, solutions, tarifs, réalisations, à propos, carrières, blog, contact |
| Commerces et PME | `commerce` | accueil, produits, services, offres, à propos, avis clients, FAQ, contact |
| Art, décoration et design | `art-deco` | accueil, atelier, collections, services, réalisations, démarche, tarifs, contact |
| Restaurants et hôtellerie | `restaurant` | accueil, la carte, chambres, événements, galerie, à propos, réservation, contact |
| Cabinets et services pro | `cabinet` | accueil, expertises, le cabinet, équipe, honoraires, références, ressources, contact |

## Comment c'est construit

- **`sectors/{pack}.php`** : les 8 pages d'un secteur et leurs textes, en français et en anglais
  côte à côte (`$T('français', 'English')`). Les pages sont composées de blocs de sections
  (`App\Templates\Blocks`) ; aucun style n'y est écrit en dur.
- **`catalog.php`** : les modèles. Un modèle = un pack + un thème (`App\Templates\Theme`) :
  6 couleurs, polices, variante du héros (`split`, `center`, `image`, `gradient`), style des
  cartes (`shadow`, `border`, `flat`, `tinted`), forme des boutons, arrondis.
- Les blocs n'utilisent que les **couleurs globales** du site : après installation, changer une
  couleur dans les réglages met tout le site à jour.
- Les photos sont des **emplacements** (`{pack}-accueil`, `{pack}-equipe-1`…). Chacun reçoit une
  photo libre de droits de [Pexels](https://www.pexels.com/license/) ; un emplacement sans photo garde
  une illustration aux couleurs du thème.

## Photos (Pexels)

Seuls les choix sont versionnés (`photos/choices.json`, `photos/credits.json`, `photos/candidates.json`) ;
les fichiers (`photos/files/`, non versionnés) se re-téléchargent à l'identique.

```bash
# Clé gratuite sur https://www.pexels.com/api/, à mettre dans .env : PEXELS_API_KEY=…
php artisan geniuspress:templates:photos --search      # 8 candidates par emplacement (reprend après la limite horaire)
php artisan geniuspress:templates:photos --review      # dist/photos-review.html : comparer les candidates
php artisan geniuspress:templates:photos --choose=ong-accueil:3   # retenir une autre candidate
php artisan geniuspress:templates:photos --download    # 1600 px, JPEG ; puis reconstruire le catalogue
```

L'accès gratuit à l'API permet 200 recherches par heure : les 219 emplacements demandent donc deux
passages. Les crédits (photographe, lien) figurent dans l'index du catalogue et sur la page Modèles.

## Commandes

```bash
php artisan geniuspress:templates:check eglise   # contrôle un pack (8 pages, réglages, icônes, liens)
php artisan geniuspress:templates                # construit dist/catalog/v1 (index.json + 60 zips)
php artisan geniuspress:templates --only=eglise-lumiere
```

Démonstration sans installation : `/catalogue/{modèle}` (par exemple `/catalogue/eglise-lumiere`,
`/catalogue/eglise-lumiere/en/services`), actif en local ou avec `GP_CATALOG_DEMOS=true`.

## Ajouter un secteur ou un modèle

1. Copier un pack existant dans `sectors/{nouveau}.php` et réécrire les pages (8, mêmes clés dans
   les deux langues, une seule page d'accueil, emplacements de photos préfixés par le nom du pack).
2. `php artisan geniuspress:templates:check {nouveau}` jusqu'à obtenir « ✓ ».
3. Ajouter les modèles dans `catalog.php`. Les tests vérifient le contraste de chaque palette
   (WCAG AA : texte blanc sur la couleur principale, texte foncé sur l'accent) et que chaque
   réglage de chaque page passe la validation.
