FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libjpeg-dev libwebp-dev libonig-dev libxml2-dev libzip-dev zip unzip libicu-dev \
    && docker-php-ext-install gd mbstring exif pcntl bcmath zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json ./
RUN composer install --no-interaction --no-dev --optimize-autoloader

COPY src/ ./src/

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "/app/src", "/app/src/router.php"]
