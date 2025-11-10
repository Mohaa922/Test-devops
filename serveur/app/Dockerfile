FROM php:8.3-apache

# Paquets & extensions PHP
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      git unzip pkg-config \
      libicu-dev libzip-dev \
      libjpeg62-turbo-dev libpng-dev libfreetype6-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" intl pdo_mysql zip gd opcache; \
    a2enmod rewrite headers; \
    rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Apache -> /public + AllowOverride 
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
      /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf \
 && sed -i "/DocumentRoot/a \\\t<Directory ${APACHE_DOCUMENT_ROOT}>\\n\\tAllowOverride All\\n\\tRequire all granted\\n\\t</Directory>" \
      /etc/apache2/sites-available/000-default.conf

# Forcer la plateforme du binaire Tailwind (évite macOS-arm64 dans un conteneur Linux)
ENV TAILWINDCSS_PLATFORM=linux-x64

WORKDIR /var/www/html

# Vendors d'abord (cache build)
COPY vitrine/composer.json vitrine/composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# Code applicatif
COPY vitrine/ .

# Variables par défaut
ENV APP_ENV=prod \
    APP_DEBUG=0 \
    APP_RUNTIME_DISABLE_DOTENV=1

# Optimisations & permissions
RUN set -eux; \
    composer dump-autoload --no-dev --optimize; \
    mkdir -p var public/uploads; \
    chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
