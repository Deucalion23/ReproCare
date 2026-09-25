# ==============================================================================
# Multi-stage Dockerfile for Laravel 12 Application (Render / Production ready)
# ==============================================================================

# ------------------------------------------------------------------------------
# Stage 1: Build Frontend Assets with Node.js & Vite
# ------------------------------------------------------------------------------
FROM node:20-alpine AS frontend
WORKDIR /app

# Copy dependency definition files
COPY package*.json ./
RUN npm ci || npm install

# Copy application source code for Vite asset compilation
COPY . .
RUN npm run build

# ------------------------------------------------------------------------------
# Stage 2: Production PHP Application Server (Apache)
# ------------------------------------------------------------------------------
FROM php:8.2-apache

# Set Apache and container environment variables
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV PORT=80
ENV LOG_CHANNEL=stderr

# Install system utilities
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install official PHP extension installer helper
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install PHP extensions required by Laravel
RUN install-php-extensions \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Configure Apache DocumentRoot to point to /public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure Apache to allow .htaccess overrides
RUN echo '<Directory /var/www/html/public>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy compiled frontend assets from Vite build stage
COPY --from=frontend /app/public/build ./public/build

# Install PHP dependencies without dev dependencies
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Create and configure Laravel storage & cache directories
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Install and prepare container startup entrypoint
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose ports (Render automatically binds via $PORT)
EXPOSE 80 10000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
