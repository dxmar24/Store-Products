FROM php:8.3-cli-alpine

RUN apk add --no-cache $PHPIZE_DEPS curl git openssl-dev unzip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

WORKDIR /var/www/html

COPY composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts

COPY src ./src
COPY public ./public

RUN composer dump-autoload --no-dev --optimize

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public public/index.php"]
