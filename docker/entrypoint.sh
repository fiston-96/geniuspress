#!/bin/sh
# Démarrage du conteneur GeniusPress.
set -e
cd /var/www/html

# Le volume « storage » peut être vide au premier démarrage : on recrée l'arborescence.
mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
         storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache plugins

# Lien public vers les fichiers envoyés.
[ -L public/storage ] || ln -s ../storage/app/public public/storage

# Exécute une commande en tant que www-data, en gardant les variables d'environnement (-p).
as_www() { su -p -s /bin/sh www-data -c "$*"; }

if [ -f storage/app/installed.lock ]; then
    # Mise à jour de l'image : on applique les nouvelles migrations.
    as_www "php artisan migrate --force --no-interaction" || echo "GeniusPress : migrations impossibles pour l'instant."
elif [ -n "$GP_ADMIN_EMAIL" ] && [ -n "$GP_ADMIN_PASSWORD" ]; then
    # Installation automatique (sinon : assistant /install dans le navigateur).
    # La commande lit DB_*, GP_SITE_*, GP_ADMIN_* et APP_URL dans l'environnement :
    # aucun mot de passe n'apparaît dans la ligne de commande.
    echo "GeniusPress : installation automatique..."
    i=0
    until as_www "php artisan geniuspress:install --no-interaction"; do
        i=$((i + 1))
        if [ "$i" -ge 10 ]; then
            echo "GeniusPress : installation automatique impossible, utilisez l'assistant /install."
            break
        fi
        echo "Base de données pas encore prête, nouvel essai dans 3 s..."
        sleep 3
    done
fi

exec "$@"
