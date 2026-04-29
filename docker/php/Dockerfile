FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        git \
        curl \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
        bash \
    && docker-php-ext-install pdo pdo_pgsql bcmath zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

CMD ["php-fpm"]
