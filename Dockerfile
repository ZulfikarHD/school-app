# ==============================================================================
# Dockerfile untuk Laravel School App
# Multi-stage build yang mencakup PHP 8.4, Node.js, dan Nginx
# Memastikan environment konsisten di semua device ("works everywhere")
# ==============================================================================

# ------------------------------------------------------------------------------
# Stage 1: Node.js untuk build frontend assets (Vite + Vue + Tailwind)
# ------------------------------------------------------------------------------
FROM node:22-alpine AS node-builder

WORKDIR /app

# Copy package files terlebih dahulu untuk cache layer optimization
COPY package.json yarn.lock ./

# Install dependencies menggunakan yarn (frozen lockfile untuk konsistensi)
RUN yarn install --frozen-lockfile

# Copy source files yang diperlukan untuk build
COPY resources ./resources
COPY vite.config.ts tsconfig.json ./
COPY public ./public

# Build production assets
RUN yarn build

# ------------------------------------------------------------------------------
# Stage 2: Composer dependencies
# ------------------------------------------------------------------------------
FROM composer:2.8 AS composer-builder

WORKDIR /app

# Copy composer files untuk install dependencies
COPY composer.json composer.lock ./

# Install dependencies tanpa dev dependencies untuk production
# --no-scripts mencegah execution scripts yang membutuhkan artisan
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# Copy seluruh source code
COPY . .

# Generate optimized autoloader setelah semua files tersedia
RUN composer dump-autoload --optimize --no-dev

# ------------------------------------------------------------------------------
# Stage 3: Final PHP-FPM image dengan semua komponen
# ------------------------------------------------------------------------------
FROM php:8.4-fpm-alpine AS production

# Labels untuk metadata image
LABEL maintainer="Zulfikar Hidayatullah"
LABEL description="Laravel School App - Production Ready"

# Install system dependencies yang diperlukan
# - postgresql-dev: PostgreSQL client libraries
# - libzip-dev: ZIP extension untuk PHP
# - libpng-dev: GD image processing
# - freetype-dev: Font rendering untuk PDF
# - libjpeg-turbo-dev: JPEG support
# - icu-dev: Internationalization
# - oniguruma-dev: Regex untuk mbstring
# - supervisor: Process manager
# - nginx: Web server
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    icu-dev \
    oniguruma-dev \
    supervisor \
    nginx \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        intl \
        mbstring \
        bcmath \
        opcache \
        pcntl \
    && rm -rf /var/cache/apk/*

# Install Redis extension untuk caching (optional, uncomment jika diperlukan)
# RUN pecl install redis && docker-php-ext-enable redis

# Copy PHP configuration yang sudah dioptimasi
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy Nginx configuration
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copy Supervisor configuration untuk manage multiple processes
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files dari composer stage
COPY --from=composer-builder /app .

# Copy built assets dari node stage
COPY --from=node-builder /app/public/build ./public/build

# Create necessary directories dengan proper permissions
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy entrypoint script yang handle auto-setup
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port untuk Nginx
EXPOSE 80

# Health check untuk memastikan aplikasi berjalan
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Entrypoint untuk auto-setup dan start services
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Default command: jalankan supervisor yang manage nginx + php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
