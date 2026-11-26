# Marque GeniusPress

## Le symbole

**« La page en construction »** : trois pages qui se chevauchent et montent en diagonale. La page de devant montre une mise en page (un bandeau et deux blocs). Le symbole dit trois choses : on construit des pages, le site grandit, et c'est un outil de mise en page, pas un simple document.

Le symbole a été conçu pour ne pas rappeler les logos existants du secteur. Il évite notamment :
- la barre verticale suivie de traits horizontaux (Elementor) ;
- les barres arrondies montantes sur une même ligne de base (Google Analytics, Power BI) ;
- les colonnes dans un carré (Trello).

Une recherche d'antériorité dans les bases de marques (WIPO, EUIPO, OAPI et offices nationaux des pays visés) reste à faire avant tout dépôt.

## Fichiers

Tous les fichiers sont dans `public/brand/`.

| Fichier | Usage |
|---|---|
| `geniuspress-mark.svg` | Symbole en couleur, à partir de 25 px |
| `geniuspress-mark-small.svg` | Symbole simplifié pour 24 px et moins (favicon, petites icônes) |
| `geniuspress-mark-white.svg` | Symbole blanc, sur fond terracotta ou sur photo sombre |
| `geniuspress-mark-black.svg` | Symbole une couleur, pour l'impression noir et blanc ou la gravure |
| `geniuspress-logo-light.svg` | Logo horizontal sur fond clair |
| `geniuspress-logo-dark.svg` | Logo horizontal sur fond sombre |
| `geniuspress-logo-stacked-light.svg`, `-dark.svg` | Logo empilé (symbole au-dessus du nom) |
| `geniuspress-icon-192.png`, `-512.png` | Icônes d'application |
| `../favicon.svg`, `../favicon.ico`, `../apple-touch-icon.png` | Icônes d'onglet et d'écran d'accueil |

Pour régénérer les PNG et le favicon après une modification du symbole :

```bash
php scripts/brand/generate-icons.php
```

Dans les vues de GeniusPress, utilisez le composant Blade `x-logo` (paramètres `size`, `wordmark`, `dark`). Il choisit automatiquement la version simplifiée en petite taille.

**À faire** : les logos avec le nom utilisent la police Inter comme texte. Pour un usage hors du web (impression, signalétique), il faudra une version où le texte est converti en tracés.

## Couleurs

| Nom | Hex | Rôle |
|---|---|---|
| Terracotta | `#aa4d26` | Couleur principale, page de devant (ce qu'on est en train de construire), « Press » |
| Nuit indigo | `#1e1b4b` | Page du milieu, texte « Genius », fonds sombres |
| Or clair | `#e3c48f` | Page du fond ; « Press » sur fond sombre |
| Or | `#c9973a` | Accent du site (chiffres, détails) |
| Crème | `#fbf7f0` | Fond clair du site ; page du milieu sur fond sombre |
| Blanc | `#ffffff` | Contenu de la page de devant, fonds clairs |

## Typographie

**Inter**, en gras (700), avec un léger resserrement (−2 %). « Genius » est en Nuit indigo et « Press » en Terracotta. Sur fond sombre : Blanc et Or clair.

## Règles d'usage

- **Marge de protection** : laissez autour du logo un espace vide au moins égal à la largeur de la page de devant (environ 40 % de la hauteur du symbole).
- **Tailles minimales** : symbole couleur 25 px (en dessous, prenez la version simplifiée), logo horizontal 120 px de large.
- **Fonds** : le symbole couleur va sur blanc, sur crème ou sur Nuit indigo (version sombre, page du milieu en crème). Sur le terracotta ou sur une photo, utilisez la version blanche.

À ne pas faire :
- changer les couleurs des pages ou leur ordre ;
- déformer, incliner ou faire pivoter le symbole ;
- ajouter des ombres, des dégradés ou des contours ;
- écrire le nom autrement que « GeniusPress » (pas « Genius Press », ni « geniuspress », ni « GENIUSPRESS ») ;
- utiliser le logo d'une façon qui laisse croire qu'un projet dérivé est le projet officiel.

## Licence

Le code de GeniusPress est sous licence AGPL-3.0, mais **le nom et le logo ne sont pas couverts par cette licence**. Vous pouvez les utiliser pour parler de GeniusPress, pour indiquer qu'un site est « propulsé par GeniusPress » ou pour un plugin compatible. Un projet dérivé (fork) doit en revanche porter un autre nom et un autre logo.
