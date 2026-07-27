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

PUBLIC_HTML="../public_html"
if [ -d "$PUBLIC_HTML" ]; then
    echo "→ public_html (index, htaccess, assets)"
    cp index.php .htaccess "$PUBLIC_HTML/"
    mkdir -p "$PUBLIC_HTML/css" "$PUBLIC_HTML/js" "$PUBLIC_HTML/images"
    cp -r public/css/. "$PUBLIC_HTML/css/"
    cp -r public/js/. "$PUBLIC_HTML/js/"
    if [ -d public/images ]; then
        cp -r public/images/. "$PUBLIC_HTML/images/"
    fi
    ln -sfn ../laravel/public/storage "$PUBLIC_HTML/storage" 2>/dev/null || true
fi

echo "→ cache"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "→ smoke-test"
php artisan presufactura:smoke-test

echo "✓ Deploy completado"
