# Modèles de sites

Le catalogue compte **72 modèles** : 10 secteurs × 6 thèmes, plus le secteur Médias et presse
(6 packs × 2 thèmes), 8 pages chacun, en français et en anglais. Chaque modèle s'installe comme une
sauvegarde de site et se prévisualise sans installation.

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
| Médias : journal en ligne | `medias-journal` | accueil (la une), actualités, rubriques, enquêtes, la rédaction, qui sommes-nous, publicité, contact |
| Médias : magazine | `medias-magazine` | accueil, articles, rubriques, le magazine, abonnement, l'équipe, publicité, contact |
| Médias : radio | `medias-radio` | accueil, direct, émissions, grille des programmes, animateurs, actualités, annonceurs, contact |
| Médias : télévision | `medias-tv` | accueil (la une), direct, programmes, émissions et replays, l'info, la chaîne, annonceurs, contact |
| Médias : journaliste indépendant | `medias-journaliste` | accueil, à propos, reportages, articles, prestations, formations, publications et prix, contact |
| Médias : podcast | `medias-podcast` | accueil, épisodes, saisons et séries, le podcast, être invité, nous soutenir, partenaires, contact |

Un secteur peut réunir plusieurs packs : les modèles du secteur `medias` déclarent leur pack
(`'pack' => 'medias-radio'`, dernier argument de `$tpl` dans `catalog.php`). Les emplacements de
photos sont alors préfixés par le nom du pack (`medias-radio-accueil`).

## Comment c'est construit

- **`sectors/{pack}.php`** : les 8 pages d'un secteur et leurs textes, en français et en anglais
  côte à côte (`$T('français', 'English')`). Les pages sont composées de blocs de sections
  (`App\Templates\Blocks`) ; aucun style n'y est écrit en dur.
- **`catalog.php`** : les modèles. Un modèle = un pack + un thème (`App\Templates\Theme`) :
  6 couleurs, polices, variante du héros (`split`, `center`, `image`, `gradient`), style des
  cartes (`shadow`, `border`, `flat`, `tinted`), forme des boutons, arrondis.
- Les blocs n'utilisent que les **couleurs globales** du site : après installation, changer une
  couleur dans les réglages met tout le site à jour.
- **Blog** : chaque pack renvoie aussi `blog` (adresse et titre du blog, catégories, articles
  d'exemple illustrés par les photos du pack). Le modèle installe ces articles et deux gabarits du
  thème, « Article » et « Archive » (`App\Templates\BlogKit`), dans le style du thème. La page qui
  porte l'adresse du blog (Actualités, Enseignements…) affiche les articles avec `Blocks::posts`.
  Options du blog : `layout` (`share` : barre de lecture et boutons de partage sur l'article ;
  `terms` : rubriques et sujets populaires) et `interval` (jours entre deux articles d'exemple).
- **Blocs des médias** : `ticker` (flash info), `headlines` (la une : diaporama et dernières infos),
  `live` (direct radio ou télévision, lien externe vers le flux), `videos` (replays), `table` (grille
  des programmes), `chart` (audience), `terms` (rubriques), `timeline` (parcours), `platforms`
  (plateformes d'écoute) ; `posts` accepte `layout => carousel`. Les vidéos d'exemple sont des films
  libres de la Blender Foundation (CC BY), à remplacer par celles du média.
- Les photos sont des **emplacements** (`{pack}-accueil`, `{pack}-equipe-1`…). Chacun reçoit une
  photo libre de droits de [Pixabay](https://pixabay.com/service/license-summary/) (ou
  [Pexels](https://www.pexels.com/license/)) ; un emplacement sans photo garde
  une illustration aux couleurs du thème.

## Photos (Pixabay ou Pexels)

Seuls les choix sont versionnés (`photos/choices.json`, `photos/credits.json`, `photos/candidates.json`) ;
les fichiers (`photos/files/`, non versionnés) se re-téléchargent à l'identique.

```bash
# Clé gratuite sur https://pixabay.com/api/docs/, à mettre dans .env : PIXABAY_API_KEY=…
# (ou PEXELS_API_KEY=… et --source=pexels)
php artisan geniuspress:templates:photos --search      # 8 candidates par emplacement
php artisan geniuspress:templates:photos --review      # dist/photos-review.html : comparer les candidates
php artisan geniuspress:templates:photos --choose=ong-accueil:3   # retenir une autre candidate
php artisan geniuspress:templates:photos --download    # 1600 px au plus, JPEG ; puis reconstruire le catalogue
```

La recherche essaie d'abord la description complète de l'emplacement, puis ses mots principaux, puis
le secteur seul, jusqu'à trouver assez de candidates. Pixabay permet 100 recherches par minute (l'outil
attend la minute suivante) ; Pexels, 200 par heure (l'outil s'arrête et reprend au passage suivant).
Les crédits (photographe, lien, source) figurent dans l'index du catalogue et sur la page Modèles.

## Commandes

```bash
php artisan geniuspress:templates:check eglise   # contrôle un pack (8 pages, réglages, icônes, liens)
php artisan geniuspress:templates                # construit dist/catalog/v1 (index.json + 72 zips)
php artisan geniuspress:templates --only=eglise-lumiere
```

Démonstration sans installation : `/catalogue/{modèle}` (par exemple `/catalogue/eglise-lumiere`,
`/catalogue/eglise-lumiere/en/services`), actif en local ou avec `GP_CATALOG_DEMOS=true`. Le blog y
fonctionne aussi (`/catalogue/ong-horizon/actualites/rapport-annuel`), lu dans l'archive du modèle.

## Ajouter un secteur ou un modèle

1. Copier un pack existant dans `sectors/{nouveau}.php` et réécrire les pages (8, mêmes clés dans
   les deux langues, une seule page d'accueil, emplacements de photos préfixés par le nom du pack)
   et le blog (3 articles au moins, chacun dans une catégorie du pack).
2. `php artisan geniuspress:templates:check {nouveau}` jusqu'à obtenir « ✓ ».
3. Ajouter les modèles dans `catalog.php`. Les tests vérifient le contraste de chaque palette
   (WCAG AA : texte blanc sur la couleur principale, texte foncé sur l'accent) et que chaque
   réglage de chaque page passe la validation.
