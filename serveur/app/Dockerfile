FROM php:8.3-apache

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

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Vendors d'abord (meilleur cache)
COPY vitrine/composer.json vitrine/composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# Code applicatif
COPY vitrine/ .

ENV APP_ENV=dev \
    APP_DEBUG=1 \
    APP_RUNTIME_DISABLE_DOTENV=1 \
    DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db

RUN set -eux; \
    composer dump-autoload --no-dev --optimize; \
    mkdir -p var; \
    chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
