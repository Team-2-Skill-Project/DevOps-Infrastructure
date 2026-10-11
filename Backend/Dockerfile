FROM composer:2.7 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./ 

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --ignore-platform-reqs \
    --optimize-autoloader \
    --no-scripts 

FROM php:8.3-cli-alpine 

RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpq-dev \
    libzip-dev \
    icu-dev \
    tini \
    linux-headers \
    $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        bcmath \
        pcntl \
        intl \
        zip \
        opcache \
    && apk del $PHPIZE_DEPS 

RUN addgroup -g 10001 appgroup && \
    adduser -u 10001 -G appgroup -s /bin/sh -D appuser 

WORKDIR /var/www/html 

COPY --chown=appuser:appgroup . .
COPY --from=vendor --chown=appuser:appgroup /app/vendor ./vendor

RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache && \
    chown -R appuser:appgroup storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache 
    
USER appuser 
EXPOSE 8000

ENTRYPOINT ["/sbin/tini", "--"] 
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]