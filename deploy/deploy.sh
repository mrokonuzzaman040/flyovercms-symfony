#!/bin/bash
# FlyoverCMS Symfony — cPanel deploy script
# Run on server after pushing code via Git or FTP

set -e

echo "==> Installing PHP dependencies (production)"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Clearing Symfony cache"
php bin/console cache:clear --env=prod --no-debug

echo "==> Warming up cache"
php bin/console cache:warmup --env=prod --no-debug

echo "==> Running database migrations"
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo "==> Building frontend assets"
npm ci
npm run build

echo "==> Setting permissions"
chmod -R 775 var/
chmod -R 775 public/build/

echo "==> Done. FlyoverCMS deployed successfully."
