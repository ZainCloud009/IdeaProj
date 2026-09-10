#!/bin/sh
set -e

echo "==> Starting Laravel container entrypoint..."

# 1. Ensure .env file exists and has all Docker environment variables
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.example ]; then
        echo "==> Creating .env from .env.example..."
        cp /var/www/html/.env.example /var/www/html/.env
    else
        touch /var/www/html/.env
    fi
fi

# Synchronize runtime environment variables into .env so web server and CLI have identical credentials
for KEY in APP_KEY DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD CACHE_STORE SESSION_DRIVER QUEUE_CONNECTION APP_NAME APP_ENV APP_DEBUG APP_URL; do
    VAL=$(eval echo "\$$KEY")
    if [ -n "$VAL" ]; then
        if grep -q "^${KEY}=" /var/www/html/.env; then
            sed -i "s|^${KEY}=.*|${KEY}=${VAL}|" /var/www/html/.env
        elif grep -q "^# *${KEY}=" /var/www/html/.env; then
            sed -i "s|^# *${KEY}=.*|${KEY}=${VAL}|" /var/www/html/.env
        else
            echo "${KEY}=${VAL}" >> /var/www/html/.env
        fi
    fi
done

# 2. Ensure application key exists and is valid
if ! grep -q "^APP_KEY=" /var/www/html/.env 2>/dev/null; then
    echo "APP_KEY=" >> /var/www/html/.env
fi

if ! grep -q "^APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
    if [ -n "$APP_KEY" ]; then
        echo "==> Setting application key from environment..."
        sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" /var/www/html/.env
    else
        echo "==> Generating application key via artisan..."
        php /var/www/html/artisan key:generate --force --no-interaction || true
    fi
fi

# Fallback: if key is still missing or not base64, generate cryptographically secure 32-byte key via PHP
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
    echo "==> Setting fallback 32-byte encryption key..."
    FALLBACK_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    sed -i "s|^APP_KEY=.*|APP_KEY=${FALLBACK_KEY}|" /var/www/html/.env
fi

# 3. Create storage symlink
php /var/www/html/artisan storage:link --quiet || true

# 4. Wait for database connection if MySQL or MariaDB is configured
DB_TYPE="${DB_CONNECTION:-sqlite}"
if [ "$DB_TYPE" = "mysql" ] || [ "$DB_TYPE" = "mariadb" ]; then
    HOST="${DB_HOST:-127.0.0.1}"
    PORT="${DB_PORT:-3306}"
    USER="${DB_USERNAME:-root}"
    PASS="${DB_PASSWORD:-}"
    DB_NAME="${DB_DATABASE:-idea}"

    echo "==> Waiting for database connection ($HOST:$PORT, database: $DB_NAME)..."
    MAX_TRIES=30
    COUNT=0
    until php -r "
        try {
            new PDO('mysql:host=$HOST;port=$PORT;dbname=$DB_NAME', '$USER', '$PASS', [PDO::ATTR_TIMEOUT => 3]);
            exit(0);
        } catch (\Exception \$e) {
            exit(1);
        }
    " 2>/dev/null; do
        COUNT=$((COUNT + 1))
        if [ "$COUNT" -ge "$MAX_TRIES" ]; then
            echo "==> WARNING: Could not connect to database after $MAX_TRIES attempts. Proceeding anyway..."
            break
        fi
        echo "    Database not ready yet... retrying ($COUNT/$MAX_TRIES)"
        sleep 2
    done

    if [ "$COUNT" -lt "$MAX_TRIES" ]; then
        echo "==> Database connected successfully!"
    fi
elif [ "$DB_TYPE" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
fi

# 5. Clear file-based caches safely before running migrations
php /var/www/html/artisan config:clear --quiet || true
php /var/www/html/artisan view:clear --quiet || true
php /var/www/html/artisan route:clear --quiet || true

# 6. Run database migrations
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "==> Running database migrations..."
    php /var/www/html/artisan migrate --force || echo "==> Migrations skipped or failed (will retry on demand)."
fi

# 7. Safe cache optimization
php /var/www/html/artisan cache:clear --quiet || true

if [ "$APP_ENV" = "production" ]; then
    echo "==> Caching routes, config, and views for production..."
    php /var/www/html/artisan config:cache --quiet || true
    php /var/www/html/artisan route:cache --quiet || true
    php /var/www/html/artisan view:cache --quiet || true
fi

# 8. Ensure proper permissions for web server
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "==> Application initialized successfully. Launching web server..."

exec "$@"
