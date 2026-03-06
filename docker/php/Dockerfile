# ─────────────────────────────────────────────────────────────────────────────
# Stage 1: base
# ─────────────────────────────────────────────────────────────────────────────
FROM php:8.3-fpm AS base

WORKDIR /var/www

# System dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        git \
        unzip \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
        default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node + Yarn
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g yarn \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ─────────────────────────────────────────────────────────────────────────────
# Stage 2: development
# ─────────────────────────────────────────────────────────────────────────────
FROM base AS development

# Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

USER www-data

# ─────────────────────────────────────────────────────────────────────────────
# Stage 3: production
# ─────────────────────────────────────────────────────────────────────────────
FROM base AS production

COPY . /var/www

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && yarn install --frozen-lockfile \
    && yarn build \
    && rm -rf node_modules

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

USER www-data
