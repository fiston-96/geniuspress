# Site officiel geniuspress.site

Le site officiel est construit **avec GeniusPress lui-même**, en français (`/`) et en anglais
(`/en`). Ce dossier le décrit en PHP : pages vitrines, menus, pied de page et réglages, dans `site.php`.
Chaque texte y est écrit dans les deux langues côte à côte : `$t('français', 'English')`.

La documentation n'est pas recopiée : elle est lue dans `docs/` (français) et `docs/en/` (anglais)
et convertie depuis le Markdown à chaque construction. Pour corriger une page de documentation,
modifiez le fichier Markdown, dans les deux langues.

## Construire

```bash
php artisan geniuspress:website            # crée dist/geniuspress-site.zip
php artisan geniuspress:website --import   # l'importe directement dans le site local
```

L'archive suit le format d'export habituel. Sur le serveur, installez GeniusPress, puis importez
`geniuspress-site.zip` depuis **Sauvegarde → Importer** (ou `php artisan geniuspress:import`).
L'import remplace les pages de même adresse et les réglages du site.

## Ajouter une page de documentation

1. Écrivez le fichier Markdown dans `docs/` et sa traduction dans `docs/en/`.
2. Ajoutez-les dans `$slugs`, `$docFiles`, `$docTitles` et `$descriptions` de `site.php`.
3. Relancez la commande.

Les liens relatifs entre fichiers Markdown deviennent des liens vers les pages du site ; les autres
pointent vers le fichier sur GitHub.

## Pages vitrines et modèles

L'accueil, les fonctionnalités et la page **Modèles** (`/modeles`, `/en/templates`) sont dans
`showcase.php`, au format 2 (conteneurs et widgets). Elles utilisent les blocs des modèles de sites
(`App\Templates\Blocks`) avec un thème aux couleurs de la marque, et `App\Website\Showcase` pour
les aperçus : chaque fiche reproduit le haut de la page d'accueil du modèle (variante, couleurs,
polices, photo) et renvoie vers sa démonstration `/catalogue/{modèle}`.

Les chiffres (modèles, pages, widgets, secteurs) sont calculés depuis `templates/catalog.php` et le
registre des widgets : ajouter un modèle met la vitrine à jour à la prochaine construction.

Les photos des aperçus viennent de `templates/photos/files/` ;
sans elles, une illustration aux couleurs de la marque les remplace.

**En production**, les démonstrations demandent le catalogue construit sur le serveur
(`php artisan geniuspress:templates`) et `GP_CATALOG_DEMOS=true` dans le `.env`.

## Pages de documentation

Les autres pages sont décrites avec `App\Website\PageKit` (`section`, `col`, `h`, `p`, `btn`, `links`…),
qui produit l'arbre simplifié `sections → columns → widgets` (format 1). À l'import, `ContentSanitizer`
le convertit en conteneurs (format 2) et le valide, comme n'importe quel contenu.
