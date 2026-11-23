# Citation

Plugin GeniusPress.

## Structure

- `plugin.json` : nom, version, compatibilité, fichiers chargés.
- `src/Plugin.php` : point d'entrée ; déclare les widgets et les hooks.
- `src/Widget.php` : réglages autorisés et rendu HTML du widget sur le site.
- `public/builder.js` : contrôles et aperçu du widget dans le builder.
- `public/site.css` : styles du widget sur le site public.

## Développement

1. Activez le plugin dans **Admin → Plugins**.
2. Modifiez les fichiers, puis rechargez le builder.
3. Si le plugin lève une erreur au chargement, il est désactivé automatiquement et l'erreur s'affiche dans **Admin → Plugins**.
