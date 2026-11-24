# GeniusPress — image Docker (Apache + PHP 8.3)
#
#   docker compose up -d        puis ouvrez http://localhost:8080
#
# Étape 1 : dépendances PHP (sans les outils de développement)
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

# Étape 2 : builder React et styles (Vite)
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# Étape 3 : image finale
FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql intl zip gd opcache bcmath \
    && a2enmod rewrite headers \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/geniuspress.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/geniuspress-entrypoint

WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app /var/www/html
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Le .env est rangé dans le volume « storage » pour survivre aux mises à jour de l'image.
ENV GP_ENV_DIR=/var/www/html/storage

RUN chmod +x /usr/local/bin/geniuspress-entrypoint \
    && rm -rf tests .git node_modules \
    && chown -R www-data:www-data /var/www/html

VOLUME ["/var/www/html/storage", "/var/www/html/plugins"]
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s CMD curl -fsS http://localhost/up || exit 1

ENTRYPOINT ["geniuspress-entrypoint"]
CMD ["apache2-foreground"]
