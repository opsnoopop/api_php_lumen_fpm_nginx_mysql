# --- Stage 1: composer dependencies ---
FROM composer:2 AS vendor
WORKDIR /app
ARG APP_ENV=prod

# context = .  => โค้ดอยู่ที่ app/
COPY ./api/composer.json ./api/composer.lock /app/

RUN if [ "$APP_ENV" = "prod" ]; then \
        composer install --no-dev --prefer-dist --no-progress --no-interaction; \
    else \
        composer install --prefer-dist --no-progress --no-interaction; \
    fi

# --- Stage 2: php-fpm runtime ---
FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        git curl zip unzip icu-dev oniguruma-dev libpng-dev \
    && docker-php-ext-install \
        intl mbstring pdo pdo_mysql opcache

RUN addgroup -g 1000 app && adduser -G app -g app -s /bin/sh -D app

WORKDIR /var/www

COPY ./php/php.ini /usr/local/etc/php/php.ini
COPY ./php/www.conf /usr/local/etc/php-fpm.d/www.conf

ARG APP_ENV=prod

# คัดลอกโค้ดเข้า image (PROD ใช้อันนี้, DEV ถ้า bind-mount ก็ได้)
COPY --chown=app:app ./api/ /var/www/

# คัดลอก vendor จาก stage composer (ต้องใช้ absolute path ของสเตจ vendor)
COPY --from=vendor /app/vendor /var/www/vendor

RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R app:app /var/www /var/www/storage /var/www/bootstrap/cache

USER app

EXPOSE 9000
CMD ["php-fpm"]   