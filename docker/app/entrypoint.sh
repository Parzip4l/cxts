#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ] && [ -f .env.docker ]; then
  cp .env.docker .env
fi

if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

mkdir -p storage/app/private storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rw storage bootstrap/cache

if [ -f artisan ]; then
  php artisan optimize:clear >/dev/null 2>&1 || true
  php artisan key:generate --force >/dev/null 2>&1 || true
  php artisan storage:link >/dev/null 2>&1 || true
  php artisan migrate --force >/dev/null 2>&1 || true
fi

exec "$@"
