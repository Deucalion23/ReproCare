#!/bin/sh
set -e

# Configure Apache port based on PORT environment variable (Render provides $PORT, defaults to 80)
PORT="${PORT:-80}"
echo "Configuring Apache to listen on port ${PORT}..."
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure storage, cache, and database directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Handle SQLite database file if SQLite is configured
if [ "${DB_CONNECTION}" = "sqlite" ] || [ -z "${DB_CONNECTION}" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "Creating database.sqlite file..."
        touch /var/www/html/database/database.sqlite
    fi
    chown www-data:www-data /var/www/html/database/database.sqlite
    chmod 664 /var/www/html/database/database.sqlite
fi

# Run migrations automatically if RUN_MIGRATIONS is set to true
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Optimize Laravel cache if APP_KEY is present
if [ -n "${APP_KEY}" ]; then
    echo "Caching routes, views, and configurations..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Execute the main container command (apache2-foreground)
exec "$@"
