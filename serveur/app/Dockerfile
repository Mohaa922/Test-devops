FROM php:8.3-apache

# --- Dépendances système ---
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      git unzip pkg-config \
      libicu-dev libzip-dev libsqlite3-dev \
      libjpeg62-turbo-dev libpng-dev libfreetype6-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" intl pdo_mysql pdo_sqlite zip gd opcache; \
    a2enmod rewrite headers; \
    rm -rf /var/lib/apt/lists/*

# --- Composer global ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# --- Configuration Apache ---
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
      /etc/apache2/sites-available/000-default.conf \
      /etc/apache2/apache2.conf

WORKDIR /var/www/html

# --- Installation des vendors d’après composer.json ---
# (copiés avant le reste pour utiliser le cache Docker)
COPY vitrine/composer.json vitrine/composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# --- Copie du code Symfony ---
COPY vitrine/ .

# --- Préparation finale du container ---
RUN set -eux; \
    composer dump-autoload --no-dev --optimize; \
    mkdir -p var; \
    chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
