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

Les pages sont décrites avec `App\Website\PageKit` (`section`, `col`, `h`, `p`, `btn`, `links`…),
qui produit l'arbre simplifié `sections → columns → widgets` (format 1). À l'import, `ContentSanitizer`
le convertit en conteneurs (format 2) et le valide, comme n'importe quel contenu.
