# ==============================================================================
# Stage 1: Build Frontend Assets (Vite 7 & Tailwind CSS 4)
# ==============================================================================
FROM node:22-alpine AS frontend-builder

WORKDIR /app

COPY package*.json ./
RUN npm ci --prefer-offline --no-audit && npm cache clean --force

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

RUN npm run build

# ==============================================================================
# Stage 2: Build Composer Dependencies
# ==============================================================================
FROM composer:2 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --no-autoloader \
    && composer clear-cache

COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY routes ./routes

RUN composer dump-autoload --optimize --no-dev --no-scripts

# ==============================================================================
# Stage 3: Lightweight Production Container (PHP 8.2 + Apache)
# ==============================================================================
FROM php:8.2-apache

WORKDIR /var/www/html

# Install runtime libraries, compile extensions with temporary build tools, and purge all dev packages
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng16-16 \
        libjpeg62-turbo \
        libfreetype6 \
        libzip4 \
        libonig5 \
        libicu72 \
        unzip \
        curl \
    && savedAptMark="$(apt-mark showmanual)" \
    && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        xml \
        zip \
    && a2enmod rewrite \
    && apt-mark auto '.*' > /dev/null \
    && apt-mark manual $savedAptMark > /dev/null \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* /usr/share/man/*

# Configure Apache VirtualHost
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Configure custom PHP settings
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy application source code directly with www-data ownership (prevents layer duplication)
COPY --chown=www-data:www-data . /var/www/html

# Copy optimized vendor directory from composer-builder stage
COPY --chown=www-data:www-data --from=composer-builder /app/vendor /var/www/html/vendor

# Copy compiled frontend assets from frontend-builder stage
COPY --chown=www-data:www-data --from=frontend-builder /app/public/build /var/www/html/public/build

# Setup entrypoint script and permissions in a single minimal layer
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN tr -d '\r' < /usr/local/bin/entrypoint.sh > /usr/local/bin/entrypoint_unix.sh \
    && mv /usr/local/bin/entrypoint_unix.sh /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
