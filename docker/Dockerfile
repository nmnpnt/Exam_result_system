FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql zip bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . /var/www

EXPOSE 8000
