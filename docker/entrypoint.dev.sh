#!/bin/sh
set -e

echo "=================================================="
echo "  Laravel School App - Development Mode"
echo "=================================================="

echo "[1/5] Menunggu PostgreSQL siap..."
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
    echo "    Mencoba koneksi... ($RETRY_COUNT/$MAX_RETRIES)"
    sleep 2
done

if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
    echo "    GAGAL: Tidak dapat terhubung ke PostgreSQL"
    exit 1
fi

echo "[2/5] Memeriksa APP_KEY..."
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "    Generating APP_KEY..."
    php artisan key:generate --force --no-interaction
else
    echo "    APP_KEY sudah tersedia"
fi

echo "[3/5] Clearing caches..."
php artisan config:clear --no-interaction 2>/dev/null || true
php artisan route:clear --no-interaction 2>/dev/null || true
php artisan view:clear --no-interaction 2>/dev/null || true

echo "[4/5] Running migrations..."
php artisan migrate --force --no-interaction

echo "[5/5] Setting up storage..."
if [ ! -L "public/storage" ]; then
    php artisan storage:link --no-interaction 2>/dev/null || true
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""
echo "=================================================="
echo "  Development server ready!"
echo "  App: http://localhost:8080"
echo "  Vite HMR: http://localhost:5173"
echo "=================================================="
echo ""

exec "$@"
