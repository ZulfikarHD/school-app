#!/bin/sh
set -e

echo "=================================================="
echo "  Laravel School App - Docker Entrypoint"
echo "=================================================="

echo "[1/7] Menunggu PostgreSQL siap..."
MAX_RETRIES=30
RETRY_COUNT=0

while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    if php -r "
        \$conn = @pg_connect('host='.getenv('DB_HOST').' port='.getenv('DB_PORT').' dbname='.getenv('DB_DATABASE').' user='.getenv('DB_USERNAME').' password='.getenv('DB_PASSWORD'));
        exit(\$conn ? 0 : 1);
    " 2>/dev/null; then
        echo "    PostgreSQL sudah siap!"
        break
    fi
    RETRY_COUNT=$((RETRY_COUNT + 1))
    echo "    Mencoba koneksi ke PostgreSQL... ($RETRY_COUNT/$MAX_RETRIES)"
    sleep 2
done

if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
    echo "    GAGAL: Tidak dapat terhubung ke PostgreSQL setelah $MAX_RETRIES percobaan"
    exit 1
fi

echo "[2/7] Memeriksa APP_KEY..."
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "    APP_KEY belum di-set, generating..."
    php artisan key:generate --force --no-interaction
else
    echo "    APP_KEY sudah tersedia"
fi

echo "[3/7] Optimizing configuration..."
php artisan config:clear --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction

if [ "$APP_ENV" = "production" ]; then
    echo "    Production mode: caching config, routes, views..."
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
fi

echo "[4/7] Running database migrations..."
php artisan migrate --force --no-interaction
echo "    Migrations selesai!"

echo "[5/7] Memeriksa storage link..."
if [ ! -L "/var/www/html/public/storage" ]; then
    echo "    Creating storage link..."
    php artisan storage:link --no-interaction
else
    echo "    Storage link sudah ada"
fi

echo "[6/7] Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
echo "    Permissions sudah di-set"

echo "[7/7] Setup selesai!"
echo ""
echo "=================================================="
echo "  Aplikasi siap digunakan!"
echo "  URL: ${APP_URL:-http://localhost:8080}"
echo "=================================================="
echo ""

exec "$@"
