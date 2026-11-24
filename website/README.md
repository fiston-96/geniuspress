# Site officiel geniuspress.site

Le site officiel est construit **avec GeniusPress lui-même**. Ce dossier le décrit en PHP :
pages vitrines, menus, pied de page et réglages, dans `site.php`.

La documentation n'est pas recopiée : elle est lue dans `docs/` et convertie depuis le Markdown
à chaque construction. Pour corriger une page de documentation, modifiez le fichier Markdown.

## Construire

```bash
php artisan geniuspress:website            # crée dist/geniuspress-site.zip
php artisan geniuspress:website --import   # l'importe directement dans le site local
```

L'archive suit le format d'export habituel. Sur le serveur, installez GeniusPress, puis importez
`geniuspress-site.zip` depuis **Sauvegarde → Importer** (ou `php artisan geniuspress:import`).
L'import remplace les pages de même adresse et les réglages du site.

## Ajouter une page de documentation

1. Écrivez le fichier Markdown dans `docs/`.
2. Ajoutez-le dans `$docs`, `$docTitles` et `$descriptions` de `site.php`.
3. Relancez la commande.

Les liens relatifs entre fichiers Markdown deviennent des liens vers les pages du site ; les autres
pointent vers le fichier sur GitHub.

Les pages sont décrites avec `App\Website\PageKit` (`section`, `col`, `h`, `p`, `btn`, `links`…),
qui produit le même arbre `sections → columns → widgets` que le builder. Tout passe ensuite par
`ContentSanitizer` à l'import, comme n'importe quel contenu.
