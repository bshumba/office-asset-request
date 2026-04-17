#!/bin/sh
set -e

cd /var/www/html

SQLITE_DATABASE_PATH="/var/www/html/storage/app/database/database.sqlite"
DB_CONNECTION_VALUE="${DB_CONNECTION:-mysql}"

set_env_value() {
    key="$1"
    value="$2"

    if grep -q "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${value}|" .env
    else
        printf '\n%s=%s\n' "$key" "$value" >> .env
    fi
}

if [ ! -f .env ]; then
    cp .env.example .env
fi

mkdir -p \
    bootstrap/cache \
    storage/app/database \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs

if [ ! -f storage/app/database/database.sqlite ]; then
    touch storage/app/database/database.sqlite
fi

set_env_value "DB_CONNECTION" "$DB_CONNECTION_VALUE"

if [ "$DB_CONNECTION_VALUE" = "sqlite" ]; then
    set_env_value "DB_DATABASE" "$SQLITE_DATABASE_PATH"
    set_env_value "DB_FOREIGN_KEYS" "${DB_FOREIGN_KEYS:-true}"
else
    set_env_value "DB_HOST" "${DB_HOST:-host.docker.internal}"
    set_env_value "DB_PORT" "${DB_PORT:-3306}"
    set_env_value "DB_DATABASE" "${DB_DATABASE:-office_asset_request}"
    set_env_value "DB_USERNAME" "${DB_USERNAME:-root}"
    set_env_value "DB_PASSWORD" "${DB_PASSWORD:-}"
fi

rm -f bootstrap/cache/config.php

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

if [ "$DB_CONNECTION_VALUE" = "mysql" ]; then
    attempts=0

    until php -r '
        $host = getenv("DB_HOST") ?: "host.docker.internal";
        $port = getenv("DB_PORT") ?: "3306";
        $database = getenv("DB_DATABASE") ?: "office_asset_request";
        $username = getenv("DB_USERNAME") ?: "root";
        $password = getenv("DB_PASSWORD") ?: "";

        try {
            new PDO("mysql:host={$host};port={$port};dbname={$database}", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (Throwable $exception) {
            fwrite(STDERR, $exception->getMessage().PHP_EOL);
            exit(1);
        }
    '; do
        attempts=$((attempts + 1))

        if [ "$attempts" -ge 30 ]; then
            echo "Unable to connect to the configured MySQL database."
            exit 1
        fi

        echo "Waiting for MySQL connection..."
        sleep 2
    done
fi

php artisan migrate --force

if [ "${APP_RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec "$@"
