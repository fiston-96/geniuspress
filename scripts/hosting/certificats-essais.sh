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

# Avec un certificat choisi d'après l'adresse ($gp_cert dans Nginx), le fichier est lu à chaque
# connexion par les processus non privilégiés de Nginx, qui ne peuvent pas lire /etc/letsencrypt
# (réservé à root). GP_CERT_DIR : dossier où recopier fullchain.pem et privkey.pem de chaque site,
# lisible par le groupe de Nginx (GP_NGINX_GROUP) seulement. Vide : pas de copie (certificat générique).
CERT_DIR="${GP_CERT_DIR:-}"
NGINX_GROUP="${GP_NGINX_GROUP:-www-data}"

# Domaines des clients branchés sur leur site (espace client → Domaines → Brancher) : pour chacun,
# un fichier Nginx dans GP_DOMAINS_CONF (inclus par « include /etc/nginx/geniuspress-domains/*.conf; »
# dans le bloc http), écrit après vérification que le domaine pointe vers GP_SERVER_IP.
# Vide : les domaines des clients sont ignorés. Un échec (DNS pas encore propagé) est retenté 30 min
# plus tard, pour rester sous les limites de Let's Encrypt.
DOMAINS_CONF="${GP_DOMAINS_CONF:-}"
SERVER_IP="${GP_SERVER_IP:-}"
PHP_SOCK="${GP_PHP_SOCK:-/run/php/php8.3-fpm.sock}"

copy_cert() {
    local host="$1" src="/etc/letsencrypt/live/$1"
    [[ -r "$src/fullchain.pem" && -r "$src/privkey.pem" ]] || return 1
    install -d -m 0750 -o root -g "$NGINX_GROUP" "$CERT_DIR" "$CERT_DIR/$host"
    install -m 0644 -o root -g "$NGINX_GROUP" "$src/fullchain.pem" "$CERT_DIR/$host/fullchain.pem"
    install -m 0640 -o root -g "$NGINX_GROUP" "$src/privkey.pem" "$CERT_DIR/$host/privkey.pem"
}

# Fichier Nginx d'un domaine de client : « http » (défi Let's Encrypt seulement) puis « https ».
write_domain_conf() {
    local host="$1" slug="$2" mode="$3" file="$DOMAINS_CONF/$1.conf"
    local cert="/etc/letsencrypt/live/$host"
    [[ -n "$CERT_DIR" ]] && cert="$CERT_DIR/$host"
    install -d -m 0755 "$DOMAINS_CONF"
    {
        echo "# Domaine de client $host → site $slug (écrit par certificats-essais.sh, ne pas modifier)."
        echo "server {"
        echo "    listen 80; listen [::]:80; server_name $host;"
        echo "    location ^~ /.well-known/acme-challenge/ { root $APP/public; default_type text/plain; try_files \$uri =404; }"
        if [[ "$mode" == "https" ]]; then
            echo "    location / { return 301 https://\$host\$request_uri; }"
        else
            echo "    location / { return 503; }"
        fi
        echo "}"
        if [[ "$mode" == "https" ]]; then
            cat <<CONF
server {
    listen 443 ssl; listen [::]:443 ssl; server_name $host;
    ssl_certificate     $cert/fullchain.pem;
    ssl_certificate_key $cert/privkey.pem;
    root $APP/public;
    index index.php;
    client_max_body_size 20m;
    charset utf-8;
    add_header X-Content-Type-Options nosniff always;
    add_header Referrer-Policy strict-origin-when-cross-origin always;
    location ~ ^/storage/(?<gp_path>.+)\$ { alias $HOSTING/sites/$slug/storage/app/public/\$gp_path; expires 7d; }
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ ^/index\.php(/|\$) {
        include snippets/fastcgi-php.conf;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
        fastcgi_pass unix:$PHP_SOCK;
        fastcgi_read_timeout 120;
    }
    location ~ \.php\$ { return 404; }
    location ~ /\.(?!well-known) { deny all; }
}
CONF
        fi
    } > "$file.tmp"
    mv "$file.tmp" "$file"
}

# Domaine de client : DNS vérifié, puis certificat, puis fichier Nginx HTTPS. Code 0 si prêt.
setup_domain() {
    local host="$1" slug retry="$HOSTING/certs/.retry-$1"
    slug="$(tr -d '[:space:]' < "$HOSTING/hosts/$host")"
    [[ "$slug" =~ ^[a-z0-9][a-z0-9-]{1,38}[a-z0-9]$ ]] || return 1
    # Échec récent : nouvel essai dans 30 minutes.
    [[ -e "$retry" && -n "$(find "$retry" -mmin -30 2>/dev/null)" ]] && return 1
    if ! getent ahostsv4 "$host" | awk '{print $1}' | grep -qx "$SERVER_IP"; then
        touch "$retry"
        return 1
    fi
    if [[ ! -e "/etc/letsencrypt/live/$host/fullchain.pem" ]]; then
        # Défi HTTP-01 servi par un fichier Nginx provisoire.
        if [[ ! -e "$DOMAINS_CONF/$host.conf" ]]; then
            write_domain_conf "$host" "$slug" http
            nginx -t -q && systemctl reload nginx
        fi
        if ! certbot certonly --webroot -w "$APP/public" -d "$host" --cert-name "$host" \
            --non-interactive --agree-tos -m "$EMAIL" --quiet; then
            echo "certificats-essais : échec pour $host (nouvel essai dans 30 minutes)" >&2
            touch "$retry"
            return 1
        fi
    fi
    if [[ -n "$CERT_DIR" ]]; then
        copy_cert "$host" || return 1
    fi
    write_domain_conf "$host" "$slug" https
    rm -f "$retry"
    return 0
}

# Appelé par certbot après chaque renouvellement (crochet « deploy », voir plus bas) : recopie.
if [[ "${1:-}" == "--deploy-hook" ]]; then
    host="$(basename "${RENEWED_LINEAGE:-}")"
    if [[ -n "$CERT_DIR" ]] && [[ "$host" =~ ^[a-z0-9-]+\.${DOMAIN//./\\.}$ || ( -n "$DOMAINS_CONF" && -e "$DOMAINS_CONF/$host.conf" ) ]]; then
        copy_cert "$host" && nginx -t -q && systemctl reload nginx
    fi
    exit 0
fi

# Une seule exécution à la fois.
exec 9>/run/geniuspress-certificats.lock
flock -n 9 || exit 0

mkdir -p "$HOSTING/certs"
chown "$WEB_USER": "$HOSTING/certs"
reload=0

shopt -s nullglob
for file in "$HOSTING"/hosts/*; do
    host="$(basename "$file")"
    # Domaine d'un client (hors adresses d'essai) : seulement si GP_DOMAINS_CONF et GP_SERVER_IP sont réglés.
    if [[ ! "$host" =~ ^[a-z0-9-]+\.${DOMAIN//./\\.}$ ]]; then
        [[ -n "$DOMAINS_CONF" && -n "$SERVER_IP" ]] || continue
        [[ "$host" =~ ^([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,63}$ ]] || continue
        [[ -e "$HOSTING/certs/$host" && -e "$DOMAINS_CONF/$host.conf" ]] && continue
        if setup_domain "$host"; then
            touch "$HOSTING/certs/$host"
            chown "$WEB_USER": "$HOSTING/certs/$host"
            reload=1
        fi
        continue
    fi
    # Déjà prêt (témoin posé et, s il le faut, copie pour Nginx présente) : rien à faire.
    [[ -e "$HOSTING/certs/$host" && ( -z "$CERT_DIR" || -e "$CERT_DIR/$host/fullchain.pem" ) ]] && continue

    if [[ ! -e "/etc/letsencrypt/live/$host/fullchain.pem" ]]; then
        if ! certbot certonly --webroot -w "$APP/public" -d "$host" --cert-name "$host" \
            --non-interactive --agree-tos -m "$EMAIL" --quiet; then
            echo "certificats-essais : échec pour $host (nouvel essai à la prochaine exécution)" >&2
            continue
        fi
        reload=1
    fi
    # Copie lisible par Nginx (voir copy_cert) : sans elle, la poignée de main TLS échoue.
    if [[ -n "$CERT_DIR" ]]; then
        copy_cert "$host" || { echo "certificats-essais : copie impossible pour $host" >&2; continue; }
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
    if [[ "$host" =~ ^[a-z0-9-]+\.${DOMAIN//./\\.}$ ]] || [[ -n "$DOMAINS_CONF" && -e "$DOMAINS_CONF/$host.conf" ]]; then
        certbot delete --cert-name "$host" --non-interactive --quiet || true
        [[ -n "$CERT_DIR" ]] && rm -rf "${CERT_DIR:?}/$host"
        [[ -n "$DOMAINS_CONF" ]] && rm -f "${DOMAINS_CONF:?}/$host.conf"
        reload=1
    fi
done
# Témoins « nouvel essai » des domaines débranchés.
for retry in "$HOSTING"/certs/.retry-*; do
    [[ -e "$HOSTING/hosts/${retry##*/.retry-}" ]] || rm -f "$retry"
done

if [[ $reload -eq 1 ]]; then
    nginx -t -q && systemctl reload nginx
fi
