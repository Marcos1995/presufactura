#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")"

php_bin() {
    if command -v php8.3 >/dev/null 2>&1; then
        command -v php8.3
    elif [ -x /opt/alt/php83/usr/bin/php ]; then
        echo /opt/alt/php83/usr/bin/php
    elif command -v php >/dev/null 2>&1; then
        command -v php
    else
        echo "No se encontró PHP. En hPanel → Selector PHP elige 8.3 y vuelve a entrar por SSH." >&2
        exit 1
    fi
}

run_composer() {
    if command -v composer >/dev/null 2>&1; then
        composer "$@"
    elif [ -f composer.phar ]; then
        "$PHP" composer.phar "$@"
    else
        echo "Composer no está en el PATH. Si vendor/ ya existe, continúa con migrate a mano:" >&2
        echo "  $PHP artisan migrate --force" >&2
        exit 1
    fi
}

PHP="$(php_bin)"
echo "→ PHP: $($PHP -v | head -n 1)"

echo "→ git pull origin main"
if ! git pull origin main; then
    echo "git pull falló. Si pide usuario/contraseña, GitHub ya no acepta password." >&2
    echo "En el servidor: git remote -v" >&2
    echo "Usa SSH (git@github.com:Marcos1995/presufactura.git) o un token de GitHub." >&2
    exit 1
fi

echo "→ composer install"
run_composer install --no-dev --optimize-autoloader --no-interaction

echo "→ migrate"
"$PHP" artisan migrate --force

echo "→ storage:link"
"$PHP" artisan storage:link 2>/dev/null || true

echo "→ cache"
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache

echo "→ smoke-test"
"$PHP" artisan presufactura:smoke-test

echo "✓ Deploy completado"
