FROM php:8.5-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libicu-dev libonig-dev libsqlite3-dev \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql pdo_sqlite zip gd intl mbstring bcmath \
    && rm -rf /var/lib/apt/lists/*

RUN printf 'upload_max_filesize=2G\npost_max_size=2G\nmemory_limit=256M\n' \
    > /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html
EXPOSE 9000
CMD ["php-fpm"]
