FROM php:8.5-cli-alpine
RUN apk add --no-cache libpq \
    && apk add --no-cache --virtual .build-deps postgresql-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql \
    && apk del .build-deps
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist
COPY config config
COPY src src
COPY public public
COPY bin bin
COPY migrations migrations
COPY tests tests
CMD ["sh", "bin/start.sh"]
