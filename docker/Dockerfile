FROM php:8.5-cli

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis

WORKDIR /app

COPY composer.json .
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && composer install --no-interaction --no-dev --optimize-autoloader

COPY . .
