#!/usr/bin/env bash
# Certificats HTTPS des sites d'essai, un par site (Let's Encrypt, défi HTTP-01).
# Pour les adresses sans certificat générique possible, comme {essai}.{ip}.sslip.io.
#
# À lancer en root toutes les minutes (crontab -e en root) :
#   * * * * * GP_CERT_EMAIL=admin@exemple.org /var/www/geniuspress/scripts/hosting/certificats-essais.sh
#
# Pour chaque adresse de storage/hosting/hosts/ : demande le certificat s'il manque,
# puis dépose le témoin storage/hosting/certs/{adresse} (la plateforme ne propose
# le site qu'à ce moment-là). Pour chaque site supprimé : retire témoin et certificat.
# Recharge Nginx si quelque chose a changé. Prérequis : certbot, et le bloc Nginx
# scripts/hosting/nginx-essais.conf (sert /.well-known/acme-challenge/ en http).
set -euo pipefail

APP="${GP_APP:-/var/www/geniuspress}"
HOSTING="${GP_HOSTING_PATH:-$APP/storage/hosting}"
EMAIL="${GP_CERT_EMAIL:?Indiquez GP_CERT_EMAIL (adresse de contact Let’s Encrypt)}"
WEB_USER="${GP_WEB_USER:-www-data}"
# Domaine des essais (GP_HOSTING_DOMAIN) : seules ses adresses reçoivent un certificat.
DOMAIN="${GP_HOSTING_DOMAIN:-78-24-248-103.sslip.io}"

# Une seule exécution à la fois.
exec 9>/run/geniuspress-certificats.lock
flock -n 9 || exit 0

mkdir -p "$HOSTING/certs"
chown "$WEB_USER": "$HOSTING/certs"
reload=0

shopt -s nullglob
for file in "$HOSTING"/hosts/*; do
    host="$(basename "$file")"
    # Adresses d'essai seulement, et rien d'inattendu dans le nom.
    [[ "$host" =~ ^[a-z0-9-]+\.${DOMAIN//./\\.}$ ]] || continue
    [[ -e "$HOSTING/certs/$host" ]] && continue

    if [[ ! -e "/etc/letsencrypt/live/$host/fullchain.pem" ]]; then
        if ! certbot certonly --webroot -w "$APP/public" -d "$host" --cert-name "$host" \
            --non-interactive --agree-tos -m "$EMAIL" --quiet; then
            echo "certificats-essais : échec pour $host (nouvel essai à la prochaine exécution)" >&2
            continue
        fi
        reload=1
    fi
    touch "$HOSTING/certs/$host"
    chown "$WEB_USER": "$HOSTING/certs/$host"
done

# Sites supprimés : témoin et certificat retirés.
for marker in "$HOSTING"/certs/*; do
    host="$(basename "$marker")"
    [[ -e "$HOSTING/hosts/$host" ]] && continue
    rm -f "$marker"
    if [[ "$host" =~ ^[a-z0-9-]+\.${DOMAIN//./\\.}$ ]]; then
        certbot delete --cert-name "$host" --non-interactive --quiet || true
        reload=1
    fi
done

if [[ $reload -eq 1 ]]; then
    nginx -t -q && systemctl reload nginx
fi
