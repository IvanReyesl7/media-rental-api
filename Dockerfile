FROM php:8.4-cli

RUN apt-get update && apt-get install -y git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["sh", "-c", "composer install && php artisan serve --host=0.0.0.0 --port=8000"]