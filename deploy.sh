#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

echo "→ git pull"
git pull origin main

echo "→ composer install"
composer install --no-dev --optimize-autoloader

echo "→ migrate"
php artisan migrate --force

echo "→ storage:link"
php artisan storage:link 2>/dev/null || true

echo "→ cache"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "→ smoke-test"
php artisan presufactura:smoke-test

echo "✓ Deploy completado"
