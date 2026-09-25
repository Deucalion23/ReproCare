#!/bin/sh
set -e

# Configure Apache port based on PORT environment variable (Render provides $PORT, defaults to 80)
PORT="${PORT:-80}"
echo "Configuring Apache to listen on port ${PORT}..."
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure .env file exists
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Configure APP_URL (using Render's automatic external URL if present)
if [ -n "${RENDER_EXTERNAL_URL}" ]; then
    APP_URL="${RENDER_EXTERNAL_URL}"
fi
if [ -n "${APP_URL}" ]; then
    sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" /var/www/html/.env
fi

# Ensure APP_KEY is set (generate automatically if missing)
if [ -n "${APP_KEY}" ]; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" /var/www/html/.env
fi

CURRENT_KEY=$(grep -E "^APP_KEY=" /var/www/html/.env | cut -d '=' -f2-)
if [ -z "${CURRENT_KEY}" ] && [ -z "${APP_KEY}" ]; then
    echo "APP_KEY is missing. Generating application key..."
    php artisan key:generate --force
fi

# Ensure storage, cache, and database directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Handle SQLite database file (local dev only — production uses Postgres,
# whose data lives outside the ephemeral container filesystem)
if [ "${DB_CONNECTION}" = "sqlite" ] || [ -z "${DB_CONNECTION}" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "Creating database.sqlite file..."
        touch /var/www/html/database/database.sqlite
    fi
    chown www-data:www-data /var/www/html/database/database.sqlite
    chmod 666 /var/www/html/database/database.sqlite
fi

# Wait for external Postgres (Render / Supabase) before migrating.
# Uses PHP (no pg client installed) with a TCP check, up to ~60s.
if [ "${DB_CONNECTION}" = "pgsql" ]; then
    echo "Waiting for Postgres at ${DB_HOST}:${DB_PORT:-5432}..."
    for i in $(seq 1 30); do
        if php -r '$h = getenv("DB_HOST"); $p = getenv("DB_PORT") ?: "5432"; $c = @fsockopen($h, (int) $p, $e, $s, 2); if ($c) { fclose($c); exit(0); } exit(1);'; then
            echo "Postgres is reachable."
            break
        fi
        if [ "$i" = "30" ]; then
            echo "Warning: Postgres not reachable after 60s, continuing anyway (migrate may fail)."
        else
            sleep 2
        fi
    done
fi

# Ensure storage symlink exists
php artisan storage:link || true

# Run database migrations automatically so required tables (users, sessions, cache) exist
if [ "${SKIP_MIGRATIONS}" != "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Warning: Migration failed. Check DB connection settings."
fi

# Seed accounts and default data (CHO, Midwife, BHW, Patients)
if [ "${SKIP_SEED}" != "true" ]; then
    echo "Seeding database accounts and data..."
    php artisan db:seed --force || echo "Warning: Seeding encountered a warning or was already seeded."
fi

# Clear any cached config so runtime environment variables are active
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "Starting Apache web server..."
# Execute the main container command (apache2-foreground)
exec "$@"
