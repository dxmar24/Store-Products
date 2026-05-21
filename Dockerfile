FROM php:8.3-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends $PHPIZE_DEPS ca-certificates curl git libssl-dev libzstd-dev pkg-config unzip \
    && update-ca-certificates \
    && curl -fsSL https://pecl.php.net/get/mongodb-2.3.2.tgz -o /tmp/mongodb.tgz \
    && pecl install /tmp/mongodb.tgz \
    && docker-php-ext-enable mongodb \
    && curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/* /tmp/pear /tmp/mongodb.tgz

WORKDIR /var/www/html

COPY composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts

COPY src ./src
COPY public ./public

RUN composer dump-autoload --no-dev --optimize

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public public/index.php"]
